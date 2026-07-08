<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Tentative d'authentification avec messages personnalisés
        try {
            $credentials = $request->only('email', 'password');
            
            // Vérifier si l'utilisateur existe
            $user = \App\Models\User::where('email', $credentials['email'])->first();
            
            if (!$user) {
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Aucun compte trouvé avec cette adresse email.',
                    ]);
            }

            // Vérifier si le compte est actif
            if (!$user->is_active) {
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Votre compte a été désactivé. Veuillez contacter l\'administration.',
                    ]);
            }

            // Tentative de connexion
            if (!Auth::attempt($credentials, $request->boolean('remember'))) {
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'password' => 'Le mot de passe saisi est incorrect.',
                    ]);
            }

            // Vérification supplémentaire : email vérifié
            if (!Auth::user()->hasVerifiedEmail()) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors([
                        'email' => 'Veuillez vérifier votre adresse email avant de vous connecter.',
                    ]);
            }

            // Régénérer la session
            $request->session()->regenerate();

            // Redirection selon le rôle
            if (Auth::user()->hasRole('admin')) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Bonjour ' . Auth::user()->name . ' ! Bienvenue dans l\'administration.');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Bonjour ' . Auth::user()->name . ' ! Content de vous revoir.');

        } catch (\Exception $e) {
            Log::error('Erreur de connexion', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.',
                ]);
        }
    }

    /**
     * Déconnexion
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Déconnexion', ['user_id' => $user?->id, 'user_email' => $user?->email]);

        return redirect('/')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }
}