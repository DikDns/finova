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
        ]);

        return Inertia::render('app/Budget', [
            'budget' => $budget
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
            'currency_code' => 'required|string|in:IDR,USD,JPY,GBP',
        ]);

        $budget->name = $validated['name'];
        $budget->description = $validated['description'] ?? $budget->description;
        $budget->currency_code = $validated['currency_code'];
        $budget->save();

        return redirect()->back();
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

        return redirect()->back();
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
