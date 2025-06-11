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

        // Load the budget with its monthly budgets
        $budget->monthlyBudgets();

        $budget->categoryGroups()->with('categories')->get();

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
        ]);

        $budget->name = $validated['name'];
        $budget->description = $validated['description'] ?? $budget->description;
        $budget->save();

        return redirect()->route('budgets.show', $budget);
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

        return redirect()->route('budgets.index');
    }
}
