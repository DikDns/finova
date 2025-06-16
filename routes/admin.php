<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountsController;
use App\Http\Controllers\Admin\SubscriptionLogController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('admin', '/admin/dashboard', 301);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts');
    Route::get('/account', [AccountsController::class, 'index'])->name('account');
    Route::get('/accounts/{id}', [AccountsController::class, 'show'])->name('accounts.show');
    Route::delete('/accounts/{id}', [AccountsController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts-stats', [AccountsController::class, 'getAccountStats'])->name('accounts.stats');

    Route::get('/subscription', [SubscriptionLogController::class, 'index'])->name('subscription');
});
