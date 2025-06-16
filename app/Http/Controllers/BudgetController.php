<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\CategoryGroup;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\CategoryBudget;
use App\Traits\CreatesBudgetCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BudgetController extends Controller
{
    use CreatesBudgetCategories;
    
    /**
     * Display a listing of the user's budget plans.
     */
    public function index()
    {
        $budgets = Budget::where('user_id', Auth::id())->get();

        return Inertia::render('budgets/Budgets', [
            'budgets' => $budgets
        ]);
    }

    /**
     * Store a newly created budget plan in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'currency_code' => 'nullable|string|in:IDR,USD,JPY,GBP',
        ]);

        try {
            DB::beginTransaction();

            $budget = new Budget();
            $budget->user_id = Auth::id();
            $budget->name = $validated['name'];
            $budget->description = $validated['description'] ?? '';
            $budget->currency_code = $validated['currency_code'] ?? 'IDR';
            $budget->save();
            
            // Create default category groups and categories for the new budget
            $this->createDefaultCategoriesForBudget($budget);

            DB::commit();

            return redirect()->route('budget', $budget->id)
                ->with('success', 'Budget berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create budget: ' . $e->getMessage());

            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat budget: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Display the specified budget plan.
     */
    public function show(Budget $budget)
    {
        // Ensure the user can only view their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        // Load the budget with all necessary relationships
        $budget->load([
            'monthlyBudgets' => function ($query) {
                $query->orderBy('month', 'desc');
            },
            'categoryGroups.categories.categoryBudgets.monthlyBudget',
            'accounts'
        ]);

        // Get accounts grouped by type
        $accounts = $budget->accounts;
        $accountTypes = $this->formatAccountTypes($accounts, $budget->id);

        return Inertia::render('app/Budget', [
            'budget' => $budget,
            'account_types' => $accountTypes
        ]);
    }
    
    /**
     * Show the form for editing the specified budget plan.
     */
    public function edit(Budget $budget)
    {
        // Ensure the user can only edit their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('users/EditBudget', [
            'budget' => $budget
        ]);
    }

    /**
     * Update the specified budget plan in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure the user can only update their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses budget ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'currency_code' => 'required|string|in:IDR,USD,JPY,GBP',
        ]);

        try {
            DB::beginTransaction();

            $budget->name = $validated['name'];
            $budget->description = $validated['description'] ?? $budget->description;
            $budget->save();

            DB::commit();

            return redirect()->route('budget', $budget)->with('success', 'Budget berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal memperbarui budget: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified budget plan from storage.
     */
    public function destroy(Budget $budget)
    {
        // Ensure the user can only delete their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses budget ini.');
        }

        try {
            DB::beginTransaction();

            // Check if budget has any related data
            $hasAccounts = $budget->accounts()->exists();
            $hasCategories = $budget->categoryGroups()->exists();
            $hasMonthlyBudgets = $budget->monthlyBudgets()->exists();

            if ($hasAccounts || $hasCategories || $hasMonthlyBudgets) {
                throw new \Exception('Tidak dapat menghapus budget yang memiliki data terkait (akun, kategori, atau budget bulanan).');
            }

            $budget->delete();

            DB::commit();

            return redirect()->route('budgets')->with('success', 'budget berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
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

    public function recent()
    {
        $latestBudget = Budget::where('user_id', Auth::id())
            ->latest()
            ->first();

        if ($latestBudget) {
            return redirect()->intended(route('budget', $latestBudget, absolute: false));
        }
        return redirect()->intended(route('budgets', absolute: false));
    }
}
