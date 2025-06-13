<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\MonthlyBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryBudgetController extends Controller
{
    /**
     * Update the specified category budget in storage.
     */
    public function update(Request $request, CategoryBudget $categoryBudget)
    {
        // Ensure the user can only update category budgets for their own categories
        if ($categoryBudget->category->categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned' => 'nullable|numeric',
            'available' => 'nullable|numeric',
        ]);

        // Store old values before update
        $oldAssigned = $categoryBudget->assigned;

        // Update the category budget
        $categoryBudget->update([
            'assigned' => $validated['assigned'] ?? $categoryBudget->assigned,
            'available' => $validated['available'] ?? $categoryBudget->available,
        ]);

        // Calculate the difference to adjust total_income
        $assignedDifference = $categoryBudget->assigned - $oldAssigned;

        $this->updateMonthlyBudgetTotals($categoryBudget->monthlyBudget);
        $this->updateTotalIncome($categoryBudget->monthlyBudget, $assignedDifference);

        return redirect()->back()->with('success', 'Anggaran kategori berhasil diperbarui.');
    }

    /**
     * Update the monthly budget totals based on all category budgets.
     */
    private function updateMonthlyBudgetTotals(MonthlyBudget $monthlyBudget)
    {
        $categoryBudgets = $monthlyBudget->categoryBudgets;

        $totalAssigned = $categoryBudgets->sum('assigned');
        $totalActivity = $categoryBudgets->sum('activity');
        $totalAvailable = $categoryBudgets->sum('available');

        $monthlyBudget->update([
            'total_assigned' => $totalAssigned,
            'total_activity' => $totalActivity,
            'total_available' => $totalAvailable,
        ]);
    }

    private function updateTotalIncome(MonthlyBudget $monthlyBudget, $assignedDifference = 0)
    {
        // Adjust total_income by subtracting the difference in assigned and available amounts
        // When assigned/available increases, total_income decreases
        // When assigned/available decreases, total_income increases
        $totalDifference = $assignedDifference;

        $monthlyBudget->update([
            'total_income' => $monthlyBudget->total_income - $totalDifference,
        ]);
    }
}
