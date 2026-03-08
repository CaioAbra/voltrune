<?php

use App\Http\Controllers\Hub\AuthController;
use App\Http\Controllers\Hub\Admin\AdminDashboardController;
use App\Http\Controllers\Hub\Admin\AdminAccountController;
use App\Http\Controllers\Hub\Admin\CompanyAdminController;
use App\Http\Controllers\Hub\HubController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('hub.dashboard');
})->name('hub.home');

Route::get('/dashboard', [HubController::class, 'dashboard'])->name('hub.dashboard');
Route::get('/products', [HubController::class, 'products'])->name('hub.products');
Route::get('/account', [HubController::class, 'account'])->name('hub.account');
Route::post('/account/password', [AuthController::class, 'updatePassword'])->name('hub.account.password.update');
Route::get('/billing', [HubController::class, 'billing'])->name('hub.billing');
Route::get('/help', [HubController::class, 'help'])->name('hub.help');
Route::get('/activation-pending', [HubController::class, 'activationPending'])->name('hub.activation-pending');

Route::get('/login', [AuthController::class, 'showLogin'])->name('hub.login');
Route::post('/login', [AuthController::class, 'login'])->name('hub.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('hub.logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('hub.register');
Route::post('/register', [AuthController::class, 'register'])->name('hub.register.submit');

Route::get('/forgot-password', function () {
    return view('hub.auth.forgot-password');
})->name('hub.forgot-password');

Route::get('/reset-password', function () {
    return view('hub.auth.reset-password');
})->name('hub.reset-password');

Route::middleware('hub.admin')
    ->prefix('/admin')
    ->name('hub.admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('home');
        Route::get('/account', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::post('/account/password', [AdminAccountController::class, 'updatePassword'])->name('account.password.update');
        Route::get('/companies', [CompanyAdminController::class, 'index'])->name('companies.index');
        Route::get('/contracts', [CompanyAdminController::class, 'contracts'])->name('contracts.index');
        Route::get('/billing', [CompanyAdminController::class, 'billing'])->name('billing.index');
        Route::get('/access', [CompanyAdminController::class, 'access'])->name('access.index');
        Route::get('/companies/{company}', [CompanyAdminController::class, 'show'])->name('companies.show');
        Route::patch('/companies/{company}/status', [CompanyAdminController::class, 'updateStatus'])->name('companies.status.update');
        Route::patch('/companies/{company}/contracts/{productKey}', [CompanyAdminController::class, 'upsertContract'])->name('companies.contracts.upsert');
        Route::patch('/companies/{company}/access/{productKey}', [CompanyAdminController::class, 'upsertAccess'])->name('companies.access.upsert');
        Route::post('/companies/{company}/billing', [CompanyAdminController::class, 'storeBilling'])->name('companies.billing.store');
    });

if (! app()->environment('local')) {
    Route::get('health', function () {
        return response()->json(['status' => 'ok']);
    })->name('hub.health');
}
