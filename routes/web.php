<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryBudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryGroupController;
use App\Http\Controllers\MonthlyBudgetController;
use App\Http\Controllers\TransactionController;
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
    Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

    Route::get('/budgets/{budget}/analysis', [AnalysisController::class, 'index'])->name('budget.analysis');
    Route::get('/budgets/{budget}/accounts', [AccountController::class, 'index'])->name('budget.accounts');

    Route::get('/budgets/{budget}/accounts/{account}', [AccountController::class, 'show'])->name('budget.accounts.show');


    // Account Routes
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Transaction Routes
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

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

    Route::post('/ai/chat', [AIController::class, 'chat'])->name('ai.chat');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
