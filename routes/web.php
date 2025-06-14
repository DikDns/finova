<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryBudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryGroupController;
use App\Http\Controllers\MonthlyBudgetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/features', function () {
    return Inertia::render('Features');
})->name('features');

Route::get('/pricing', function () {
    return Inertia::render('Pricing');
})->name('pricing');

// Main routes
Route::middleware('auth')->group(function () {
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::get('/budgets/recent', [BudgetController::class, 'recent'])->name('budget.recent');
    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budget');

    Route::get('/budgets/{budget}/analysis', [AnalysisController::class, 'index'])->name('budget.analysis');
    Route::get('/budgets/{budget}/accounts', [AccountController::class, 'index'])->name('budget.accounts');
    
    // Account Routes
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');

    // Category Group Routes
    Route::post('/category-groups', [CategoryGroupController::class, 'store'])->name('category-groups.store');
    Route::put('/category-groups/{categoryGroup}', [CategoryGroupController::class, 'update'])->name('category-groups.update');
    Route::delete('/category-groups/{categoryGroup}', [CategoryGroupController::class, 'destroy'])->name('category-groups.destroy');

    // Category Routes
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Category Budget Routes
    Route::put('/category-budgets/{categoryBudget}', [CategoryBudgetController::class, 'update'])->name('category-budgets.update');

    Route::post('/monthly-budgets/store', [MonthlyBudgetController::class, 'store'])->name('monthly-budgets.store');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
