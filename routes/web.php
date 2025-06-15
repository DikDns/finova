<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryBudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategoryGroupController;
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
    Route::get('/budgets/{budget}/accounts', [AccountsController::class, 'index'])->name('budget.accounts');

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
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
