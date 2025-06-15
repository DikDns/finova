<?php

namespace App\Http\Controllers;

use App\Models\CategoryBudget;
use App\Models\MonthlyBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryBudgetController extends Controller
{
    /**
     * Update the specified category budget in storage.
     */
    public function update(Request $request, CategoryBudget $categoryBudget)
    {
        try {
            // Ensure the user can only update category budgets for their own categories
            if ($categoryBudget->category->categoryGroup->budget->user_id !== Auth::id()) {
                abort(403);
            }

            $validated = $request->validate([
                'assigned' => 'nullable|numeric',
                'available' => 'nullable|numeric',
            ]);

            return DB::transaction(function () use ($validated, $categoryBudget) {
                // Store old values before update
                $oldAssigned = $categoryBudget->assigned;
                // Calculate the difference to adjust total_balance
                $assignedDifference = ($validated['assigned'] ?? $categoryBudget->assigned) - $oldAssigned;

                $this->updateTotalIncome($categoryBudget->monthlyBudget, $assignedDifference);

                // Update the category budget
                $categoryBudget->update([
                    'assigned' => $validated['assigned'] ?? $categoryBudget->assigned,
                    'available' => $validated['available'] ?? $categoryBudget->available,
                ]);

                $this->updateMonthlyBudgetTotals($categoryBudget->monthlyBudget);

                return redirect()->back()->with('success', 'Budget kategori berhasil diperbarui.');
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan saat memperbarui budget kategori: ' . $e->getMessage(),
            ]);
        }
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
        $totalDifference = $assignedDifference;
        $new_total_balance = $monthlyBudget->total_balance - $totalDifference;

        if ($new_total_balance < 0) {
            throw ValidationException::withMessages([
                'error' => ['Total budget tidak mencukupi.'],
            ]);
        }

        $monthlyBudget->update([
            'total_balance' => $new_total_balance,
        ]);
    }
}
