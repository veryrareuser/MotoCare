<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\StaffAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('role:admin,mechanic,inventory_staff,cashier,owner')
        ->name('dashboard');

    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/accounts', [StaffAccountController::class, 'index'])
            ->name('accounts.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/accounts/create', [StaffAccountController::class, 'create'])
            ->name('accounts.create');

        Route::post('/accounts', [StaffAccountController::class, 'store'])
            ->name('accounts.store');

        Route::patch('/accounts/{account}/activate', [StaffAccountController::class, 'activate'])
            ->name('accounts.activate');

        Route::patch('/accounts/{account}/deactivate', [StaffAccountController::class, 'deactivate'])
            ->name('accounts.deactivate');
    });
});
