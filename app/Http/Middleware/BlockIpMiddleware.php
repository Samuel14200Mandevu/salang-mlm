<?php
// app/Http/Middleware/BlockIpMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockIpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        
        // Vérifier si l'IP est bloquée
        if (Cache::get("blocked_ip_{$ip}", false)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'code' => 'IP_BLOCKED'
            ], 403);
        }

        return $next($request);
    }
}