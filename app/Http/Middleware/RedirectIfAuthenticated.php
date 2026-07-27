<?php
// app/Http/Middleware/RedirectIfAuthenticated.php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // ✅ Redirection selon le rôle
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                
                // ✅ Redirection pour le caissier
                if ($user->hasRole('cashier')) {
                    return redirect()->route('cashier.dashboard');
                }
                
                // ✅ Redirection par défaut pour les utilisateurs normaux
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}