<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('admin', '/admin/admindashboard', 301);

Route::prefix('admin')->group(function () {
    Route::get('/admindashboard', [DashboardController::class, 'index'])->name('admin.admindashboard');

    Route::get('/adminaccount', function () {
        return Inertia::render('admin/adminaccount');
    })->name('admin.adminaccount');

    Route::get('/adminsubscription', function () {
        return Inertia::render('admin/adminsubscription');
    })->name('admin.adminsubscription');
});
