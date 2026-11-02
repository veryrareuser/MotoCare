<?php

use App\Http\Controllers\Staff\BookingController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\InvoiceController;
use App\Http\Controllers\Staff\MaintenanceReminderController;
use App\Http\Controllers\Staff\ServiceOrderController;
use App\Http\Controllers\Staff\ServiceOrderMechanicController;
use App\Http\Controllers\Staff\ServiceOrderPartController;
use App\Http\Controllers\Staff\ServiceOrderServiceController;
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

    Route::middleware('role:admin,mechanic,cashier,owner')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [BookingController::class, 'show'])
            ->whereNumber('booking')
            ->name('bookings.show');
    });

    Route::middleware('role:admin,mechanic,cashier')->group(function () {
        Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])
            ->whereNumber('booking')
            ->name('bookings.check-in');

        Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->whereNumber('booking')
            ->name('bookings.cancel');

        Route::patch('/bookings/{booking}/no-show', [BookingController::class, 'noShow'])
            ->whereNumber('booking')
            ->name('bookings.no-show');
    });

    Route::middleware('role:admin,mechanic,cashier,owner')->group(function () {
        Route::get('/service-orders', [ServiceOrderController::class, 'index'])
            ->name('service-orders.index');

        Route::get('/service-orders/{serviceOrder}', [ServiceOrderController::class, 'show'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.show');
    });

    Route::middleware('role:admin,mechanic,cashier')->group(function () {
        Route::get('/service-orders/create', [ServiceOrderController::class, 'create'])
            ->name('service-orders.create');

        Route::post('/service-orders', [ServiceOrderController::class, 'store'])
            ->name('service-orders.store');

        Route::post('/service-orders/{serviceOrder}/mechanics', [ServiceOrderMechanicController::class, 'store'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.mechanics.store');

        Route::delete('/service-orders/{serviceOrder}/mechanics/{assignment}', [ServiceOrderMechanicController::class, 'destroy'])
            ->whereNumber(['serviceOrder', 'assignment'])
            ->name('service-orders.mechanics.destroy');
    });

    Route::middleware('role:admin,mechanic')->group(function () {
        Route::patch('/service-orders/{serviceOrder}/status', [ServiceOrderController::class, 'updateStatus'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.status.update');

        Route::post('/service-orders/{serviceOrder}/services', [ServiceOrderServiceController::class, 'store'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.services.store');

        Route::delete('/service-orders/{serviceOrder}/services/{line}', [ServiceOrderServiceController::class, 'destroy'])
            ->whereNumber(['serviceOrder', 'line'])
            ->name('service-orders.services.destroy');

        Route::post('/service-orders/{serviceOrder}/parts', [ServiceOrderPartController::class, 'store'])
            ->whereNumber('serviceOrder')
            ->name('service-orders.parts.store');

        Route::delete('/service-orders/{serviceOrder}/parts/{line}', [ServiceOrderPartController::class, 'destroy'])
            ->whereNumber(['serviceOrder', 'line'])
            ->name('service-orders.parts.destroy');
    });

    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/reminders', [MaintenanceReminderController::class, 'index'])
            ->name('reminders.index');

        Route::get('/reminders/{reminder}', [MaintenanceReminderController::class, 'show'])
            ->whereNumber('reminder')
            ->name('reminders.show');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/reminders/create', [MaintenanceReminderController::class, 'create'])
            ->name('reminders.create');

        Route::post('/reminders', [MaintenanceReminderController::class, 'store'])
            ->name('reminders.store');

        Route::get('/reminders/{reminder}/edit', [MaintenanceReminderController::class, 'edit'])
            ->whereNumber('reminder')
            ->name('reminders.edit');

        Route::patch('/reminders/{reminder}', [MaintenanceReminderController::class, 'update'])
            ->whereNumber('reminder')
            ->name('reminders.update');

        Route::patch('/reminders/{reminder}/cancel', [MaintenanceReminderController::class, 'cancel'])
            ->whereNumber('reminder')
            ->name('reminders.cancel');

        Route::patch('/reminders/{reminder}/complete', [MaintenanceReminderController::class, 'complete'])
            ->whereNumber('reminder')
            ->name('reminders.complete');
    });

    Route::middleware('role:admin,cashier,owner')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::get('/invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])
            ->whereNumber('invoice')
            ->name('invoices.receipt');

        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
            ->whereNumber('invoice')
            ->name('invoices.show');
    });

    Route::middleware('role:cashier')->group(function () {
        Route::patch('/invoices/{invoice}/deposit', [InvoiceController::class, 'updateDeposit'])
            ->whereNumber('invoice')
            ->name('invoices.deposit.update');

        Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])
            ->whereNumber('invoice')
            ->name('invoices.payments.store');
    });

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
