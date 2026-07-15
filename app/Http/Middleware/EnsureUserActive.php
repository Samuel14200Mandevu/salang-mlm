<?php
// app/Http/Middleware/EnsureUserActive.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserActive
{
    public function handle(Request $request, Closure $next)
    {
        // Routes publiques d'authentification
        $routeName = $request->route()?->getName();
        
        $publicRoutes = ['login', 'register', 'password.request', 'password.email', 'password.reset', 'password.store', 'logout'];
        
        if (in_array($routeName, $publicRoutes)) {
            return $next($request);
        }

        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ✅ Supprimer les anciens messages d'erreur
        $request->session()->forget('error');

        // ✅ Si le compte est actif, tout est autorisé
        if ($user->is_active) {
            return $next($request);
        }

        // ✅ COMPTE INACTIF : ON NE BLOQUE PAS L'ACCÈS
        // ✅ Ajouter une variable pour la vue (bannière)
        view()->share('account_inactive', true);
        
        // ✅ Ajouter un message flash (non bloquant) si pas déjà présent
        if (!$request->session()->has('warning')) {
            session()->flash('warning', 'Votre compte est inactif. Activez-le pour recevoir des commissions.');
        }

        // ✅ PERMETTRE L'ACCÈS À TOUTES LES ROUTES
        return $next($request);
    }
}