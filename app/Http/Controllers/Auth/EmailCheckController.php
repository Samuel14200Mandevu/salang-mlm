<?php
// app/Http/Controllers/Auth/EmailCheckController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailCheckController extends Controller
{
    /**
     * Vérifier si un email existe déjà
     */
    public function check(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $exists = User::where('email', $email)->exists();

        if ($exists) {
            return response()->json([
                'exists' => true,
                'available' => false,
                'type' => 'error',
                'title' => 'Email indisponible',
                'message' => 'Cette adresse email est déjà associée à un compte existant.',
                'detail' => 'Veuillez utiliser une autre adresse email ou vous connecter.',
                'field_status' => 'error'
            ]);
        }

        // Vérification du format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'exists' => false,
                'available' => false,
                'type' => 'warning',
                'title' => 'Format invalide',
                'message' => 'L\'adresse email saisie n\'est pas valide.',
                'detail' => 'Format attendu : nom@domaine.com',
                'field_status' => 'warning'
            ]);
        }

        return response()->json([
            'exists' => false,
            'available' => true,
            'type' => 'success',
            'title' => 'Email disponible',
            'message' => 'Cette adresse email est disponible pour votre inscription.',
            'detail' => 'Vous pouvez continuer le processus d\'inscription.',
            'field_status' => 'success'
        ]);
    }
}