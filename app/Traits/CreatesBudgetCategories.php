<?php

namespace App\Traits;

use App\Models\Budget;
use App\Models\CategoryGroup;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\CategoryBudget;
use Illuminate\Support\Facades\Log;

trait CreatesBudgetCategories
{
    /**
     * Create default category groups and categories for a budget.
     */
    protected function createDefaultCategoriesForBudget(Budget $budget): void
    {
        try {
            // Create current month's budget
            $monthlyBudget = MonthlyBudget::create([
                'budget_id' => $budget->id,
                'month' => now()->startOfMonth(),
                'total_balance' => 0,
                'total_assigned' => 0,
                'total_activity' => 0,
                'total_available' => 0,
            ]);

            // Create category groups and their categories
            $categoryGroups = [
                'Tagihan' => [
                    '🎓 Uang Kuliah (UKT)',
                    '🏠 Sewa Kost',
                    '📱 Telepon & Internet',
                    '💡 Listrik & Air',
                    '🚗 Transportasi',
                ],
                'Kebutuhan' => [
                    '🍽️ Makan & Minum',
                    '📚 Perlengkapan Kuliah',
                    '🧴 Kebutuhan Mandi & Kebersihan Diri',
                    '🏥 Kesehatan',
                    '🚨 Dana Darurat',
                ],
                'Keinginan' => [
                    '🍪 Jajan',
                    '🎮 Hiburan',
                    '📺 Langganan',
                    '🛍️ Belanja',
                    '✈️ Liburan',
                ]
            ];

            foreach ($categoryGroups as $groupName => $categories) {
                // Create category group
                $categoryGroup = CategoryGroup::create([
                    'budget_id' => $budget->id,
                    'name' => $groupName,
                ]);

                // Create categories for this group
                foreach ($categories as $categoryName) {
                    $category = Category::create([
                        'name' => $categoryName,
                        'category_group_id' => $categoryGroup->id,
                    ]);

                    // Create category budget for current month
                    CategoryBudget::create([
                        'monthly_budget_id' => $monthlyBudget->id,
                        'category_id' => $category->id,
                        'assigned' => 0,
                        'activity' => 0,
                        'available' => 0,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log error and rethrow to be caught by the caller
            Log::error('Failed to create default categories for budget: ' . $e->getMessage());
            throw $e;
        }
    }
}
