<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\VigilanteLeadController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/servicos', 'pages.servicos')->name('servicos');
Route::view('/portfolio', 'pages.portfolio')->name('portfolio');
Route::view('/sistemas', 'pages.sistemas')->name('sistemas');
Route::view('/contato', 'pages.contato')->name('contato');

Route::post('/contato', [ContactController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('contato.store');

Route::get('/portal', function () {
    $portalUrl = trim((string) env('PORTAL_REDIRECT_URL', ''));

    if ($portalUrl !== '') {
        return redirect()->away($portalUrl, 302);
    }

    return view('pages.portal');
})->name('portal');

Route::view('/vigilante', 'pages.vigilante')->name('vigilante');
Route::view('/sistemas/vigilante', 'pages.vigilante')->name('sistemas.vigilante');
Route::post('/vigilante', [VigilanteLeadController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('vigilante.store');
