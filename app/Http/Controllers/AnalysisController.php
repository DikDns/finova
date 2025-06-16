<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalysisController extends Controller
{
    public function index(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        // Get current month and year for the stored functions
        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');

        // Call stored functions to get expense statistics
        $totalExpense = DB::selectOne("SELECT GetTotalMonthlyExpenseForMonth(?, ?, ?) as value", [$budget->id, $currentYear, $currentMonth]);
        $averageMonthlyExpense = DB::selectOne("SELECT GetAverageExpenseForMonth(?, ?, ?) as value", [$budget->id, $currentYear, $currentMonth]);
        $averageDailyExpense = DB::selectOne("SELECT GetAverageExpenseForDay(?, ?, ?, ?) as value", [$budget->id, $currentYear, $currentMonth, $currentDay]);
        $highestMonthlyExpense = DB::selectOne("SELECT GetHighestExpenseForMonth(?, ?, ?) as value", [$budget->id, $currentYear, $currentMonth]);
        $mostActiveCategory = DB::selectOne("SELECT GetMostActiveExpenseCategoryForMonth(?, ?, ?) as value", [$budget->id, $currentYear, $currentMonth]);

        // Load accounts for the sidebar
        $budget->load('accounts');
        $accountTypes = $this->formatAccountTypes($budget->accounts, $budget->id);

        // Get expense data by category for the current month
        $expenseData = $this->getExpenseDataByCategory($budget->id, $currentYear, $currentMonth);

        return Inertia::render('app/Analysis', [
            'budget' => $budget,
            'account_types' => $accountTypes,
            'expenseStats' => [
                'totalExpense' => round(floatval($totalExpense->value ?? 0)),
                'averageMonthlyExpense' => round(floatval($averageMonthlyExpense->value ?? 0)),
                'averageDailyExpense' => round(floatval($averageDailyExpense->value ?? 0)),
                'highestMonthlyExpense' => round(floatval($highestMonthlyExpense->value ?? 0)),
                'mostActiveCategory' => $mostActiveCategory->value ?? 'No Category'
            ],
            'expenseData' => $expenseData
        ]);
    }

    /**
     * Get expense data by category for the specified month and year
     */
    private function getExpenseDataByCategory($budgetId, $year, $month)
    {
        $expensesByCategory = DB::select(
            "SELECT c.name as category, ABS(SUM(t.amount)) as amount 
            FROM transactions t 
            JOIN categories c ON t.category_id = c.id 
            WHERE t.budget_id = ? AND t.amount < 0 
            AND YEAR(t.date) = ? AND MONTH(t.date) = ? 
            GROUP BY c.name",
            [$budgetId, $year, $month]
        );

        // Calculate total expense for percentage calculation
        $totalExpense = 0;
        foreach ($expensesByCategory as $expense) {
            $totalExpense += $expense->amount;
        }

        // Define colors and icons for categories
        $colors = ['#2563eb', '#16a34a', '#dc2626', '#ea580c', '#8b5cf6', '#0891b2', '#db2777', '#65a30d'];
        $icons = ['🏠', '⚡', '🛒', '⛽', '🍔', '💊', '🚗', '📱', '🎮', '📚', '🎭', '✈️'];

        // Format data for the frontend
        $formattedData = [];
        $colorIndex = 0;
        $iconIndex = 0;

        foreach ($expensesByCategory as $expense) {
            $formattedData[] = [
                'category' => $expense->category,
                'amount' => round(floatval($expense->amount)),
                'percentage' => $totalExpense > 0 ? round(($expense->amount / $totalExpense) * 100) : 0,
                'color' => $colors[$colorIndex % count($colors)],
                'icon' => $icons[$iconIndex % count($icons)],
            ];
            $colorIndex++;
            $iconIndex++;
        }

        return $formattedData;
    }

    /**
     * Format accounts data for the sidebar component
     */
    private function formatAccountTypes($accounts, $budgetId)
    {
        $groupedAccounts = $accounts->groupBy('type');
        $accountTypes = [];

        foreach ($groupedAccounts as $type => $typeAccounts) {
            $formattedAccounts = $typeAccounts->map(function ($account) use ($budgetId) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'url' => "/budgets/{$budgetId}/accounts/{$account->id}",
                    'balance' => (float) $account->balance
                ];
            })->toArray();

            $accountTypes[] = [
                'id' => $type,
                'type' => $type,
                'isActive' => false,
                'accounts' => $formattedAccounts
            ];
        }

        return $accountTypes;
    }
}
