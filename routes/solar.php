<?php

use App\Modules\Solar\Controllers\CustomerController;
use App\Modules\Solar\Controllers\QuoteController;
use App\Modules\Solar\Controllers\SimulationController;
use App\Modules\Solar\Controllers\SolarDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'company.active', 'product:solar'])
    ->prefix('solar')
    ->name('solar.')
    ->group(function (): void {
        Route::get('/', [SolarDashboardController::class, 'index'])->name('dashboard');
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/simulations', [SimulationController::class, 'index'])->name('simulations.index');
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    });
