<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'code' => 'UNAUTHENTICATED'
                ], 401);
            }

            return redirect()->route('login')
                ->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            Log::warning('Unauthorized admin access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Administrator role required.',
                    'code' => 'FORBIDDEN'
                ], 403);
            }

            abort(403, 'Unauthorized access. Administrator role required.');
        }

        if (!$user->is_active) {
            Auth::logout();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated',
                    'code' => 'ACCOUNT_INACTIVE'
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        return $next($request);
    }
}