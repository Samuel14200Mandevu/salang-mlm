<?php
// app/Http/Middleware/LogRequest.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    public function handle(Request $request, Closure $next)
    {
        // Log only in debug mode or for specific routes
        if (config('app.debug')) {
            Log::channel('api')->debug('API Request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'headers' => $request->headers->all(),
                'input' => $request->except(['password', 'password_confirmation']),
            ]);
        }

        return $next($request);
    }
}