<?php

namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CategoryGroupController extends Controller
{
    /**
     * Store a newly created category group in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'budget_id' => 'required|string|exists:budgets,id'
        ]);

        // Ensure the user can only create category groups for their own budgets
        $budget = Budget::findOrFail($validated['budget_id']);
        if ($budget->user_id !== Auth::id()) {
            abort(403);
        }

        $categoryGroup = CategoryGroup::create([
            'name' => $validated['name'],
            'budget_id' => $validated['budget_id']
        ]);

        return redirect()->back()->with('success', 'Grup kategori berhasil dibuat.');
    }

    /**
     * Update the specified category group in storage.
     */
    public function update(Request $request, CategoryGroup $categoryGroup)
    {
        // Ensure the user can only update category groups for their own budgets
        if ($categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $categoryGroup->update([
            'name' => $validated['name']
        ]);

        return redirect()->back()->with('success', 'Grup kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category group from storage.
     */
    public function destroy(CategoryGroup $categoryGroup)
    {
        // Ensure the user can only delete category groups for their own budgets
        if ($categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $categoryGroup->delete();

        return redirect()->back()->with('success', 'Grup kategori berhasil dihapus.');
    }
}