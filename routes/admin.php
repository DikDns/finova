<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('admin', '/admin/dashboard', 301);

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/account', function () {
        return Inertia::render('admin/account');
    })->name('admin.account');

    Route::get('/subscription', function () {
        return Inertia::render('admin/subscription');
    })->name('admin.subscription');
});
