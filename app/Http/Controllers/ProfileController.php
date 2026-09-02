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
    public function index()
    {
        $user = Auth::user();
        $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();

        return view('profile.index', compact('user', 'sponsor'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            // Informations personnelles
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            
            // ✅ Nouveaux champs
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'profession' => ['nullable', 'string', 'max:255'],
            'identity_number' => ['nullable', 'string', 'max:100'],
            
            // Coordonnées bancaires
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'mobile_money' => ['nullable', 'string', 'max:50'],
            
            // Signature
            'signature_name' => ['nullable', 'string', 'max:255'],
            'signature_date' => ['nullable', 'date'],
            'signature_location' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($request->only([
            'name', 'phone', 'country', 'city', 'address',
            'birth_date', 'gender', 'profession', 'identity_number',
            'bank_name', 'account_number', 'account_holder', 'mobile_money',
            'signature_name', 'signature_date', 'signature_location'
        ]));

        return redirect()->route('profile.index')
            ->with('success', 'Profil mis à jour avec succès !');
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

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

        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

        if ($user->wallet) {
            $user->wallet->delete();
        }

        $user->delete();
        Auth::logout();

        return redirect('/')->with('success', 'Votre compte a été supprimé.');
    }
}