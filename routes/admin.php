<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('admin', '/admin/admindashboard', 301);

Route::prefix('admin')->group(function () {
    Route::get('/admindashboard', [DashboardController::class, 'index'])->name('admin.admindashboard');

    Route::get('/adminsubscription', function () {
        return Inertia::render('admin/adminsubscription');
    })->name('admin.adminsubscription');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts');
    Route::get('/adminaccount', [AccountsController::class, 'index'])->name('adminaccount');
    Route::get('/accounts/{id}', [AccountsController::class, 'show'])->name('accounts.show');
    Route::delete('/accounts/{id}', [AccountsController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts-stats', [AccountsController::class, 'getAccountStats'])->name('accounts.stats');
});