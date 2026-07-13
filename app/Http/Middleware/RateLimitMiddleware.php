<?php
// app/Http/Middleware/RateLimitMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;

        if ($this->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            Log::warning('Too many requests', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'route' => $request->route()?->getName(),
                'url' => $request->fullUrl(),
            ]);

            $retryAfter = $this->getRetryAfter($key);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many requests. Please try again in {$retryAfter} seconds.",
                    'code' => 'TOO_MANY_REQUESTS',
                    'retry_after' => $retryAfter,
                ], 429);
            }

            return back()
                ->with('error', "Too many requests. Please try again in {$retryAfter} seconds.");
        }

        $this->incrementAttempts($key, $decayMinutes);

        $response = $next($request);

        $remaining = $this->getRemainingAttempts($key, $maxAttempts);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset', $this->getResetTime($key));

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        $userId = auth()->id() ?? 'guest';
        $routeName = $request->route()?->getName() ?? $request->path();

        return $userId . '|' . $request->ip() . '|' . $routeName;
    }

    protected function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $attempts = (int) Cache::get($key, 0);
        return $attempts >= $maxAttempts;
    }

    protected function incrementAttempts(string $key, int $decayMinutes): void
    {
        $attempts = (int) Cache::get($key, 0);
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
    }

    protected function getRemainingAttempts(string $key, int $maxAttempts): int
    {
        $attempts = (int) Cache::get($key, 0);
        return max(0, $maxAttempts - $attempts);
    }

    protected function getRetryAfter(string $key): int
    {
        $expiresAt = Cache::get($key . ':expires');
        if ($expiresAt) {
            return max(0, $expiresAt - now()->timestamp);
        }
        return 60;
    }

    protected function getResetTime(string $key): int
    {
        $expiresAt = Cache::get($key . ':expires');
        return $expiresAt ?? now()->addMinutes(1)->timestamp;
    }
}