<?php

use App\Http\Controllers\BudgetController;
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
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::get('/budgets/{budget}', [BudgetController::class, 'show'])->name('budget');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
