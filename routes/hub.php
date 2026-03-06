<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('hub.dashboard');
})->name('hub.home');

Route::get('/dashboard', function () {
    return view('hub.dashboard');
})->name('hub.dashboard');

Route::get('/products', function () {
    return view('hub.products');
})->name('hub.products');

Route::get('/account', function () {
    return view('hub.account');
})->name('hub.account');

Route::get('/help', function () {
    return view('hub.help');
})->name('hub.help');

Route::get('/login', function () {
    return view('hub.auth.login');
})->name('hub.login');

Route::get('/forgot-password', function () {
    return view('hub.auth.forgot-password');
})->name('hub.forgot-password');

Route::get('/reset-password', function () {
    return view('hub.auth.reset-password');
})->name('hub.reset-password');

if (! app()->environment('local')) {
    Route::get('health', function () {
        return response()->json(['status' => 'ok']);
    })->name('hub.health');
}
