<?php

use App\Modules\Solar\Controllers\CustomerController;
use App\Modules\Solar\Controllers\ProjectController;
use App\Modules\Solar\Controllers\QuoteController;
use App\Modules\Solar\Controllers\SimulationController;
use App\Modules\Solar\Controllers\SolarCompanySettingController;
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
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::get('/projects/automation-preview', [ProjectController::class, 'automationPreview'])->name('projects.automation-preview');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('/projects/{project}/simulations', [SimulationController::class, 'storeFromProject'])->name('projects.simulations.store');
        Route::get('/settings', [SolarCompanySettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SolarCompanySettingController::class, 'update'])->name('settings.update');
        Route::get('/simulations', [SimulationController::class, 'index'])->name('simulations.index');
        Route::get('/simulations/{simulation}', [SimulationController::class, 'show'])->name('simulations.show');
        Route::post('/simulations/{simulation}/quotes', [QuoteController::class, 'storeFromSimulation'])->name('simulations.quotes.store');
        Route::get('/quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
        Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    });
