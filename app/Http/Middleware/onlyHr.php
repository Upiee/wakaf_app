<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class onlyHr
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized - HR Access Required');
        }

        $role = $request->user()->role;
        // strpos
        if (!str_contains($role, 'hr') && !str_contains($role, 'admin')) {
            abort(403, 'Unauthorized - HR Access Required');
        }

        return $next($request);
    }
}
