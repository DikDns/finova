<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Budget;
use App\Models\CategoryGroup;
use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\CategoryBudget;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:' . User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        
        // Create default budget for new user
        $this->createDefaultBudget($user);

        return to_route('budget.recent');
    }
    
    /**
     * Create a default budget with predefined categories for a new user.
     */
    private function createDefaultBudget(User $user): void
    {
        try {
            DB::transaction(function () use ($user) {
                // Create the default budget
                $budget = Budget::create([
                    'user_id' => $user->id,
                    'name' => 'Budget Mahasiswa',
                    'description' => 'Budget default untuk pengelolaan keuangan mahasiswa',
                    'currency_code' => 'IDR',
                ]);
                
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
                        'Uang Kuliah (UKT)',
                        'Sewa Kost',
                        'Telepon & Internet',
                        'Listrik & Air',
                        'Transportasi',
                    ],
                    'Kebutuhan' => [
                        'Makan & Minum',
                        'Perlengkapan Kuliah',
                        'Kebutuhan Mandi & Kebersihan Diri',
                        'Kesehatan',
                        'Dana Darurat',
                    ],
                    'Keinginan' => [
                        'Jajan',
                        'Hiburan',
                        'Langganan',
                        'Belanja',
                        'Liburan',
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
            });
        } catch (\Exception $e) {
            // Log error but don't interrupt registration flow
            Log::error('Failed to create default budget: ' . $e->getMessage());
        }
    }
}
