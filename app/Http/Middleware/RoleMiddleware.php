<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {

        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, "Access denied. Requires {$role} role.");
        }
        return $next($request);
    }
}
