<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BudgetController extends Controller
{
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
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $budget->name = $validated['name'];
        $budget->description = $validated['description'] ?? $budget->description;
        $budget->save();

        return redirect()->route('budget', $budget);
    }

    /**
     * Remove the specified budget plan from storage.
     */
    public function destroy(Budget $budget)
    {
        // Ensure the user can only delete their own budgets
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $budget->delete();

        return redirect()->route('budgets');
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

        // @rafi_zamzami handle to create new budget with paramater of user_plan: true
        return redirect()->intended(route('budgets', absolute: false));
    }
}
