<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ Vérifier l'authentification
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // ✅ Vérifier le rôle admin (méthode simple)
        if (Auth::user()->email !== 'samuelmandevu10@gmail.com') {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}