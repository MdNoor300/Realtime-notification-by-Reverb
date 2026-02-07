<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {

             // Admin panel (web)
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/custom_routes/admin.php'));

            // API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/custom_routes/common.php'));

            // Auth routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/custom_routes/auth.php'));

                // User routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/custom_routes/user.php'));

                 // ENABLE BROADCASTING ROUTES (THIS IS THE FIX)
            Broadcast::routes(['middleware' => ['auth:sanctum']]);
        }
    )
     ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })


    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
