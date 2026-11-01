<?php

use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRouteName())
        : view('welcome');
})->name('home');

Route::get('/dashboard', CustomerDashboardController::class)
    ->middleware(['auth', 'role:customer'])
    ->name('dashboard');

Route::get('/staff/dashboard', StaffDashboardController::class)
    ->middleware(['auth', 'role:admin,mechanic,inventory_staff,cashier,owner'])
    ->name('staff.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
