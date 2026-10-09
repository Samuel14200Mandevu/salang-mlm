<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->hasSupervisionAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé à la supervision (admin ou IT manager).',
                ], 403);
            }

            abort(403, 'Accès réservé à la supervision (admin ou IT manager).');
        }

        return $next($request);
    }
}
