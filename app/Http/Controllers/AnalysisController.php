<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnalysisController extends Controller
{
    public function index(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        // Load accounts for the sidebar
        $budget->load('accounts');
        $accountTypes = $this->formatAccountTypes($budget->accounts, $budget->id);

        return Inertia::render('app/Analysis', [
            'budget' => $budget,
            'account_types' => $accountTypes
        ]);
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
