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
        then: function () {
            Route::prefix('api/v1')
                ->name('api.v1.')
                ->group(base_path('routes/Api/api_v1.php'));

            Route::prefix('api/v2')
                ->name('api.v2.')
                ->group(base_path('routes/Api/api_v2.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule) {
        // Альтернативный способ через замыкание
        $schedule->job(new \App\Domains\Shopify\Jobs\SyncProductsJob())
            ->dailyAt('02:00');
    })
    ->create();
