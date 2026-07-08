<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //  Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            Log::warning('Tentative d\'accès admin sans authentification', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié'
                ], 401);
            }
            
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        //  Vérifier si l'utilisateur est admin (avec support pour super_admin)
        if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            Log::warning('Tentative d\'accès admin non autorisé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur.'
                ], 403);
            }
            
            abort(403, 'Accès non autorisé. Vous devez être administrateur.');
        }

        //  Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte est désactivé.'
                ], 403);
            }
            
            return redirect()->route('login')->with('error', 'Votre compte est désactivé.');
        }

        //  Vérifier si le compte est vérifié (si KYC requis pour admin)
        if (config('app.require_kyc_for_admin', false) && !$user->isKycVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'KYC requis pour accéder à l\'administration.'
                ], 403);
            }
            
            return redirect()->route('kyc.index')->with('error', 'Veuillez compléter votre KYC pour accéder à l\'administration.');
        }

        //  Log de l'accès admin
        Log::info('Accès admin', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $request->ip(),
            'url' => $request->fullUrl()
        ]);

        return $next($request);
    }
}