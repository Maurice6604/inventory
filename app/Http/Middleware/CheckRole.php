<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Ensures the user has the required role (admin, staff).
     * Usage in route: middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || ! $request->user()->isActive()) {
            abort(403, 'Your account is inactive or unauthorized.');
        }

        if ($request->user()->role !== $role && $request->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
