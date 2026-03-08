<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            if (env('APP_ENV') === 'local') {
                Route::middleware('web')
                    ->prefix('hub')
                    ->group(base_path('routes/hub.php'));
            } else {
                Route::middleware('web')
                    ->domain(env('HUB_DOMAIN', 'hub.voltrune.com'))
                    ->group(base_path('routes/hub.php'));
            }
        },
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'hub.admin' => \App\Http\Middleware\EnsureHubAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
