<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('hub.index');
});

if (! app()->environment('local')) {
    Route::get('health', function () {
        return response()->json(['status' => 'ok']);
    });
}
