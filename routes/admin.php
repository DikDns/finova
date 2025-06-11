<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\UserLogController;
use App\Http\Controllers\Admin\SubscriptionLogController;

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');

    // Ganti route userlog dari closure ke controller
    Route::get('/subscription-log', [SubscriptionLogController::class, 'index'])->name('admin.subscription-log');

    Route::get('/account', function () {
        return Inertia::render('admin/Account');
    })->name('admin.account');

    Route::get('/subscription', function () {
        return Inertia::render('admin/Subscription');
    })->name('admin.subscription');

});