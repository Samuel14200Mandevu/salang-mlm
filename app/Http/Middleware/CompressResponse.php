<?php
// app/Http/Middleware/CompressResponse.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CompressResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (in_array('gzip', $request->getEncodings())) {
            $content = $response->getContent();
            $compressed = gzencode($content, 9);
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
        }

        return $response;
    }
}