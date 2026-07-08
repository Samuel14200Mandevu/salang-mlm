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

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Cet email est déjà utilisé' : 'Email disponible',
            'available' => !$exists
        ]);
    }
}