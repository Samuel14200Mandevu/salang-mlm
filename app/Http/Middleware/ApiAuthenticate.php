<?php
// app/Http/Middleware/ApiAuthenticate.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            Log::warning('Unauthenticated API access attempt', [
                'ip' => $request->ip(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in.',
                'code' => 'UNAUTHENTICATED',
                'status' => 401
            ], 401);
        }

        $user = auth()->user();

        if (!$user->is_active) {
            Log::warning('API access attempt with inactive account', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
                'code' => 'ACCOUNT_INACTIVE',
                'status' => 403
            ], 403);
        }

        return $next($request);
    }
}