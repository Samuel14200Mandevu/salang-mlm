<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Afficher le profil
     */
    public function index()
    {
        $user = Auth::user();
        
        // ✅ Récupérer le sponsor
        $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();
        
        return view('profile.index', compact('user', 'sponsor'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'zip' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($request->only(['name', 'phone', 'country', 'city', 'address', 'zip']));

        return redirect()->route('profile.index')
            ->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Mettre à jour l'avatar
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        // Supprimer l'ancien avatar
        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

        // Sauvegarder le nouvel avatar
        $image = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('storage/avatars'), $filename);

        $user->avatar = $filename;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar mis à jour avec succès !',
            'avatar_url' => asset('storage/avatars/' . $filename)
        ]);
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Mot de passe mis à jour avec succès !');
    }

    /**
     * Supprimer l'avatar
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

        $user->avatar = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar supprimé avec succès !'
        ]);
    }

    /**
     * Supprimer le compte
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Le mot de passe est incorrect.'
            ], 'userDeletion');
        }

        // Supprimer l'avatar
        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

        // Supprimer le wallet
        if ($user->wallet) {
            $user->wallet->delete();
        }

        // Supprimer l'utilisateur
        $user->delete();
        Auth::logout();

        return redirect('/')->with('success', 'Votre compte a été supprimé.');
    }
}