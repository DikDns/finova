<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('admin', '/admin/dashboard', 301);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/account', function () {
        return Inertia::render('admin/account');
    })->name('admin.account');


    Route::get('/subscription', function () {
        return Inertia::render('admin/subscription');
    })->name('admin.subscription');

    Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts');
    Route::get('/account', [AccountsController::class, 'index'])->name('account');
    Route::get('/accounts/{id}', [AccountsController::class, 'show'])->name('accounts.show');
    Route::delete('/accounts/{id}', [AccountsController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts-stats', [AccountsController::class, 'getAccountStats'])->name('accounts.stats');
});
