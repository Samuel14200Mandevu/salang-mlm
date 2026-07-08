<?php
// app/Http/Controllers/Auth/PasswordResetLinkController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => '📧 L\'adresse email est obligatoire.',
            'email.email' => '📧 Veuillez saisir une adresse email valide.',
        ]);

        // ✅ Vérifier si l'utilisateur existe
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => '❌ Aucun compte trouvé avec cette adresse email.',
                ]);
        }

        // ✅ Vérifier si le compte est actif
        if (!$user->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => '⛔ Votre compte est désactivé. Veuillez contacter l\'administration.',
                ]);
        }

        // ✅ Envoyer le lien de réinitialisation
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', '📧 Un lien de réinitialisation a été envoyé à votre adresse email.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => '⚠️ Une erreur est survenue. Veuillez réessayer.',
            ]);
    }
}