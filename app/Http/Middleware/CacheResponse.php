<?php
// app/Http/Middleware/CacheResponse.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResponse
{
    public function handle(Request $request, Closure $next, $minutes = 60)
    {
        // Skip caching for authenticated users or POST/PUT/DELETE requests
        if (auth()->check() || !in_array($request->method(), ['GET', 'HEAD'])) {
            return $next($request);
        }

        $key = $this->generateCacheKey($request);

        if (Cache::has($key)) {
            return response(Cache::get($key))->header('X-Cache', 'HIT');
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($key, $response->getContent(), now()->addMinutes($minutes));
            $response->header('X-Cache', 'MISS');
        }

        return $response;
    }

    private function generateCacheKey(Request $request): string
    {
        $userId = auth()->id() ?? 'guest';
        $path = str_replace('/', '_', $request->path());
        $query = http_build_query($request->query());

        return 'response_cache:' . $userId . ':' . $path . ':' . $query;
    }
}