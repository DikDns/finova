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

        return Inertia::render('budget/Budgets', [
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
        $budget->load(['monthlyBudgets' => function ($query) {
            $query->orderBy('month', 'desc');
        }]);

        // For each monthly budget, load the category groups with their categories and category budgets
        foreach ($budget->monthlyBudgets as $monthlyBudget) {
            // Get the original date object from the database
            // We need to access the raw attribute to get the date object
            $monthDate = $monthlyBudget->getRawOriginal('month') ?
                date_create($monthlyBudget->getRawOriginal('month')) :
                date_create($monthlyBudget->month);

            // Format the month for frontend in YYYY-MM format
            $monthlyBudget->month = date_format($monthDate, 'Y-m');

            // Load category groups for this budget
            $categoryGroups = $budget->categoryGroups()->with(['categories' => function ($query) {
                $query->orderBy('name');
            }])->orderBy('name')->get();

            // Initialize arrays to store processed data
            $processedGroups = [];

            foreach ($categoryGroups as $group) {
                $categories = [];
                $totalAllocated = 0;
                $totalSpent = 0;
                $totalTarget = 0;

                foreach ($group->categories as $category) {
                    // Get category budget for this month
                    $categoryBudget = $category->categoryBudgets()
                        ->where('monthly_budget_id', $monthlyBudget->id)
                        ->first();

                    // Get total spent for this category in this month
                    $spent = $category->transactions()
                        ->whereMonth('date', date_format($monthDate, 'm'))
                        ->whereYear('date', date_format($monthDate, 'Y'))
                        ->sum('amount');

                    // Default values if no budget exists
                    $allocated = 0;
                    $target = 0;

                    if ($categoryBudget) {
                        $allocated = (float) $categoryBudget->assigned;
                        // For now, target is the same as allocated
                        $target = $allocated;
                    }

                    $totalAllocated += $allocated;
                    $totalSpent += $spent;
                    $totalTarget += $target;

                    $categories[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'icon' => $category->icon ?? null,
                        'allocated' => $allocated,
                        'spent' => $spent,
                        'target' => $target
                    ];
                }

                $processedGroups[] = [
                    'id' => $group->id,
                    'name' => $group->name,
                    'categories' => $categories,
                    'totalAllocated' => $totalAllocated,
                    'totalSpent' => $totalSpent,
                    'totalTarget' => $totalTarget
                ];
            }

            // Add the processed category groups to the monthly budget
            $monthlyBudget->category_groups = $processedGroups;
        }


        return Inertia::render('users/BudgetDetail', [
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
