<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // For API requests, return JSON instead of redirect
        if (! $request->expectsJson()) {
            return route('login'); // or null if no login route
        }

        return null;
    }
}
