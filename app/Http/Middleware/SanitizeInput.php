<?php
// app/Http/Middleware/SanitizeInput.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        if (!empty($input)) {
            array_walk_recursive($input, function (&$value) {
                if (is_string($value)) {
                    // Remove extra spaces
                    $value = trim($value);
                    
                    // Remove HTML tags (keep for rich text fields if needed)
                    // $value = strip_tags($value);
                    
                    // Prevent XSS
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }
}