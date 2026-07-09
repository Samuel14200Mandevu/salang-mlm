<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // ✅ Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        $user = Auth::user();

        // ✅ Vérifier si l'utilisateur a le rôle admin
        // Utiliser hasRole() de Spatie Permission
        if (!$user->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur.'
                ], 403);
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé. Vous devez être administrateur.');
        }

        return $next($request);
    }
}