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

            // custom_routes Admin routes
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/custom_routes/admin.php'));

            // custom_routes  Common routes
            Route::middleware('web')
                ->group(base_path('routes/custom_routes/common.php'));

            // custom_routes Auth routes
            Route::middleware('web')
                ->group(base_path('routes/custom_routes/auth.php'));

            // custom_routes User routes
            Route::middleware('web')
                ->group(base_path('routes/custom_routes/user.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
