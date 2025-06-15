<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\MonthlyBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_group_id' => 'required|string|exists:category_groups,id',
            'monthly_budget_ids' => 'required|array',
            'monthly_budget_ids.*' => 'required|string|exists:monthly_budgets,id'
        ]);

        try {
            DB::beginTransaction();

            // Ensure the user can only create categories for their own category groups
            $categoryGroup = CategoryGroup::with('budget')->findOrFail($validated['category_group_id']);
            if ($categoryGroup->budget->user_id !== Auth::id()) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses grup kategori ini.');
            }

            $category = Category::create([
                'name' => $validated['name'],
                'category_group_id' => $validated['category_group_id'],
            ]);

            foreach ($validated['monthly_budget_ids'] as $monthlyBudgetId) {
                $category->categoryBudgets()->create([
                    'monthly_budget_id' => $monthlyBudgetId,
                    'category_id' => $category->id,
                    'assigned' => 0,
                    'activity' => 0,
                    'available' => 0
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Kategori berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal membuat kategori: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Ensure the user can only update categories for their own category groups
        if ($category->categoryGroup->budget->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses kategori ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $category->update([
                'name' => $validated['name']
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal memperbarui kategori: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Ensure the user can only delete categories for their own category groups
        if ($category->categoryGroup->budget->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses kategori ini.');
        }

        try {
            DB::beginTransaction();

            // Before deleting, restore the assigned amounts back to monthly budgets
            $categoryBudgets = $category->categoryBudgets;
            
            foreach ($categoryBudgets as $categoryBudget) {
                $monthlyBudget = $categoryBudget->monthlyBudget;
                
                // Restore the assigned amount back to total_balance
                $monthlyBudget->update([
                    'total_balance' => $monthlyBudget->total_balance + $categoryBudget->assigned
                ]);
            }

            $category->delete();

            // Update monthly budget totals after deletion
            foreach ($categoryBudgets as $categoryBudget) {
                $this->updateMonthlyBudgetTotals($categoryBudget->monthlyBudget);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal menghapus kategori: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the monthly budget totals based on all category budgets.
     */
    private function updateMonthlyBudgetTotals($monthlyBudget)
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
