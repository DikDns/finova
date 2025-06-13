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

        // Update the category budget
        $categoryBudget->update([
            'assigned' => $validated['assigned'] ?? $categoryBudget->assigned,
            'available' => $validated['available'] ?? $categoryBudget->available,
        ]);

        // Update the monthly budget totals
        $this->updateMonthlyBudgetTotals($categoryBudget->monthlyBudget);

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
}
