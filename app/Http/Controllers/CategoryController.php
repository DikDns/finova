<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'category_group_id' => 'required|string|exists:category_groups,id'
        ]);

        // Ensure the user can only create categories for their own category groups
        $categoryGroup = CategoryGroup::with('budget')->findOrFail($validated['category_group_id']);
        if ($categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $category = Category::create([
            'name' => $validated['name'],
            'category_group_id' => $validated['category_group_id']
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil dibuat.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Ensure the user can only update categories for their own category groups
        if ($category->categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update([
            'name' => $validated['name']
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Ensure the user can only delete categories for their own category groups
        if ($category->categoryGroup->budget->user_id !== Auth::id()) {
            abort(403);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
    }
}