<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // ✅ Vérifier l'état du compte après connexion
        $user = Auth::user();

        // ✅ Supprimer les anciens messages d'erreur
        $request->session()->forget('error');

        if (!$user->is_active) {
            // ✅ Rediriger vers la page d'activation avec un message d'information
            return redirect()->route('activate.index')
                ->with('warning', 'Votre compte est inactif. Veuillez l\'activer pour recevoir des commissions.');
        }

        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // ✅ Supprimer les messages d'erreur lors de la déconnexion
        $request->session()->forget('error');
        $request->session()->forget('warning');

        return redirect('/');
    }
}