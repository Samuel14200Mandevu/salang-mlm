<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($request->only(['name', 'phone', 'country', 'city', 'address']));

        return redirect()->route('profile.index')->with('success', 'Profil mis à jour avec succès !');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = Auth::user();

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
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Mot de passe mis à jour avec succès !');
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
            return back()->withErrors(['password' => 'Le mot de passe est incorrect.']);
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
