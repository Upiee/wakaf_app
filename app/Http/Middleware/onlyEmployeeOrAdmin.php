<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class onlyEmployeeOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized - Employee Access Required');
        }

        $role = $request->user()->role;
        // strpos
        if (!str_contains($role, 'employee') && !str_contains($role, 'admin')) {
            abort(403, 'Unauthorized - Employee Access Required');
        }
        return $next($request);
    }
}
