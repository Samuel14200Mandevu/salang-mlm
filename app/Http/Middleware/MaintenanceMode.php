<?php
// app/Http/Middleware/MaintenanceMode.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        if (Cache::get('maintenance_mode', false)) {
            if (auth()->check() && auth()->user()->hasRole('admin')) {
                return $next($request);
            }

            $token = $request->query('maintenance_token');
            if ($token && $token === config('app.maintenance_token')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The site is currently under maintenance. Please try again later.',
                    'code' => 'MAINTENANCE_MODE',
                    'estimated_time' => Cache::get('maintenance_end_time'),
                ], 503);
            }

            return response()->view('errors.maintenance', [
                'estimated_time' => Cache::get('maintenance_end_time'),
            ], 503);
        }

        return $next($request);
    }
}