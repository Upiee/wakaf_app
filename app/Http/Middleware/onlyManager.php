<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class onlyManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('filament.manager.auth.login');
        }

        // Pastikan user memiliki role manager
        if (auth()->user()->role !== 'manager') {
            abort(403, 'Access denied. Manager role required.');
        }

        return $next($request);
    }
}
