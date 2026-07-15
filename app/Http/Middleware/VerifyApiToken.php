<?php
// app/Http/Middleware/VerifyApiToken.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token missing',
                'code' => 'TOKEN_MISSING'
            ], 401);
        }

        $invalidTokens = Cache::get('invalid_tokens', []);
        if (in_array($token, $invalidTokens)) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid or expired',
                'code' => 'TOKEN_INVALID'
            ], 401);
        }

        return $next($request);
    }
}