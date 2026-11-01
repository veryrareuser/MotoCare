<?php

use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\MotorcycleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('motorcycles', MotorcycleController::class)->except('show');
});
