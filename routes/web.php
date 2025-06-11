<?php

use App\Http\Controllers\BudgetController;
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

// Budget routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('budgets', BudgetController::class);
    Route::resource('transactions', TransactionController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
