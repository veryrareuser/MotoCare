<?php

use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\ServiceTypeController;
use App\Http\Controllers\Staff\SparepartCompatibilityController;
use App\Http\Controllers\Staff\SparepartController;
use App\Http\Controllers\Staff\StaffAccountController;
use App\Http\Controllers\Staff\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('role:admin,mechanic,inventory_staff,cashier,owner')
        ->name('dashboard');

    Route::middleware('role:admin,mechanic,inventory_staff,cashier,owner')->group(function () {
        Route::get('/services', [ServiceTypeController::class, 'index'])
            ->name('services.index');

        Route::get('/spareparts', [SparepartController::class, 'index'])
            ->name('spareparts.index');

        Route::get('/spareparts/{sparepart}', [SparepartController::class, 'show'])
            ->whereNumber('sparepart')
            ->name('spareparts.show');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/services/create', [ServiceTypeController::class, 'create'])
            ->name('services.create');

        Route::post('/services', [ServiceTypeController::class, 'store'])
            ->name('services.store');

        Route::get('/services/{serviceType}/edit', [ServiceTypeController::class, 'edit'])
            ->whereNumber('serviceType')
            ->name('services.edit');

        Route::patch('/services/{serviceType}', [ServiceTypeController::class, 'update'])
            ->whereNumber('serviceType')
            ->name('services.update');

        Route::delete('/services/{serviceType}', [ServiceTypeController::class, 'destroy'])
            ->whereNumber('serviceType')
            ->name('services.destroy');
    });

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

    Route::middleware('role:inventory_staff')->group(function () {
        Route::get('/spareparts/create', [SparepartController::class, 'create'])
            ->name('spareparts.create');

        Route::post('/spareparts', [SparepartController::class, 'store'])
            ->name('spareparts.store');

        Route::get('/spareparts/{sparepart}/edit', [SparepartController::class, 'edit'])
            ->whereNumber('sparepart')
            ->name('spareparts.edit');

        Route::patch('/spareparts/{sparepart}', [SparepartController::class, 'update'])
            ->whereNumber('sparepart')
            ->name('spareparts.update');

        Route::delete('/spareparts/{sparepart}', [SparepartController::class, 'destroy'])
            ->whereNumber('sparepart')
            ->name('spareparts.destroy');

        Route::post('/spareparts/{sparepart}/movements', [StockMovementController::class, 'store'])
            ->whereNumber('sparepart')
            ->name('spareparts.movements.store');

        Route::post('/spareparts/{sparepart}/compatibilities', [SparepartCompatibilityController::class, 'store'])
            ->whereNumber('sparepart')
            ->name('spareparts.compatibilities.store');

        Route::delete('/spareparts/{sparepart}/compatibilities/{compatibility}', [SparepartCompatibilityController::class, 'destroy'])
            ->whereNumber(['sparepart', 'compatibility'])
            ->name('spareparts.compatibilities.destroy');
    });
});
