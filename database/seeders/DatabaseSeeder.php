<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Budget;
use App\Models\Account;
use App\Models\AiChat;
use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\CategoryGroup;
use App\Models\ExportReport;
use App\Models\MonthlyBudget;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\UserLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@finova.com',
            'password' => Hash::make('password'),
            'name' => 'Administrator',
            'role' => 'admin',
        ]);

        // Create regular users
        $users = User::factory(5)->create();
        $allUsers = $users->prepend($admin);

        // Create data for each user
        foreach ($allUsers as $user) {
            // Create budgets
            $budgets = Budget::factory(2)->for($user)->create();

            foreach ($budgets as $budget) {
                // Create category groups and categories
                $categoryGroups = CategoryGroup::factory(rand(3, 5))
                    ->create(['budget_id' => $budget->id]);

                // Create category groups and their categories first
                $allCategories = collect();
                foreach ($categoryGroups as $categoryGroup) {
                    $categories = Category::factory(rand(3, 6))
                        ->create(['category_group_id' => $categoryGroup->id]);
                    $allCategories = $allCategories->concat($categories);
                }

                // Create monthly budgets for the last 3 months with consistent dates
                $monthlyBudgets = collect();
                for ($i = 0; $i < 3; $i++) {
                    $monthlyBudgets->push(
                        MonthlyBudget::factory()->create([
                            'budget_id' => $budget->id,
                            'month' => now()->subMonths($i)->startOfMonth()
                        ])
                    );
                }

                // Create category budgets for each category in each monthly budget
                foreach ($monthlyBudgets as $monthlyBudget) {
                    foreach ($allCategories as $category) {
                        CategoryBudget::factory()->create([
                            'monthly_budget_id' => $monthlyBudget->id,
                            'category_id' => $category->id
                        ]);
                    }
                }

                // Create accounts
                $accounts = Account::factory(rand(2, 4))
                    ->create(['budget_id' => $budget->id]);

                // Get all categories for this budget
                $allCategories = Category::whereIn('category_group_id', $categoryGroups->pluck('id'))->get();

                // Create transactions for each account
                foreach ($accounts as $account) {
                    Transaction::factory(rand(10, 20))->create([
                        'account_id' => $account->id,
                        'category_id' => fn() => $allCategories->random()->id,
                        'budget_id' => $budget->id
                    ]);
                }
            }

            // Create export reports
            ExportReport::factory(rand(1, 3))->create([
                'user_id' => $user->id,
                'budget_id' => fn() => $budgets->random()->id
            ]);

            // Create subscription (70% chance)
            if (fake()->boolean(70)) {
                Subscription::factory()->create(['user_id' => $user->id]);
            }

            // Create AI chats
            AiChat::factory(rand(0, 5))->create([
                'user_id' => $user->id,
                'category_ids' => fn() => json_encode([$allCategories->random()->id]),
                'transaction_ids' => json_encode([1]),
                'account_ids' => json_encode([1])
            ]);

            // Create user logs
            for ($i = 0; $i < rand(5, 10); $i++) {
                UserLog::create([
                    'user_id' => $user->id,
                    'action' => fake()->randomElement(['login', 'logout', 'create', 'update', 'delete']),
                    'description' => fake()->sentence(),
                    'ip_address' => fake()->ipv4(),
                    'user_agent' => fake()->userAgent(),
                    'old_values' => json_encode([]),
                    'new_values' => json_encode([]),
                    'created_at' => fake()->dateTimeBetween('-3 months', 'now')
                ]);
            }
        }
    }
}
