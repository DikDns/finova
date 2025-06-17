<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\MonthlyBudget;
use App\Models\CategoryBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyBudgetController extends Controller
{
    /**
     * Store a newly created monthly budget in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'budget_id' => 'required|exists:budgets,id',
                'month' => 'required|date',
                'reference_month' => 'required|date',
            ]);

            $budget = Budget::findOrFail($validated['budget_id']);

            // Format the month to match the model's expected format (Y-m-01)
            $monthFormatted = date('Y-m-01', strtotime($validated['month']));
            $referenceMonthFormatted = date('Y-m-01', strtotime($validated['reference_month']));

            // Check if monthly budget already exists
            if ($budget->monthlyBudgets()->where('month', $monthFormatted)->exists()) {
                throw ValidationException::withMessages([
                    'error' => ['Budget untuk bulan ini sudah ada.'],
                ]);
            }

            return DB::transaction(function () use ($budget, $monthFormatted, $referenceMonthFormatted) {

                // Get reference monthly budget
                $referenceMonthlyBudget = $budget->monthlyBudgets()
                    ->where('month', $referenceMonthFormatted)
                    ->first();

                // Create new monthly budget
                $monthlyBudget = $budget->monthlyBudgets()->create([
                    'month' => $monthFormatted,
                    'total_balance' => $referenceMonthlyBudget->total_balance,
                    'total_assigned' => 0,
                    'total_activity' => 0,
                    'total_available' => 0,
                ]);


                if ($referenceMonthlyBudget) {
                    // Copy category budgets from reference month
                    foreach ($referenceMonthlyBudget->categoryBudgets as $referenceBudget) {
                        CategoryBudget::create([
                            'monthly_budget_id' => $monthlyBudget->id,
                            'category_id' => $referenceBudget->category_id,
                            'assigned' => 0,
                            'available' => $referenceBudget->available,
                            'activity' => 0,
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'Budget bulanan berhasil dibuat.');
            });
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Terjadi kesalahan saat membuat budget bulanan: ' . $e->getMessage(),
            ]);
        }
    }
}
