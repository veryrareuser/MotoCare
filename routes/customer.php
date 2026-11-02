<?php

use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\MotorcycleController;
use App\Http\Controllers\Customer\SparepartCatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('motorcycles', MotorcycleController::class)->except('show');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])
        ->whereNumber('booking')
        ->name('bookings.show');
    Route::patch('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule'])
        ->whereNumber('booking')
        ->name('bookings.reschedule');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->whereNumber('booking')
        ->name('bookings.cancel');

    Route::get('/spareparts', SparepartCatalogController::class)->name('spareparts.index');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])
        ->whereNumber('invoice')
        ->name('invoices.receipt');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->whereNumber('invoice')
        ->name('invoices.show');
});
