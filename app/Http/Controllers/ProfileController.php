<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = Auth::user();

        // Supprimer l'ancien avatar s'il existe
        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }

        $image = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Redimensionner et sauvegarder l'image
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($image);
            $img->cover(300, 300);
            $img->save(public_path('storage/avatars/' . $filename));
        } catch (\Exception $e) {
            // Fallback: sauvegarder sans redimensionnement si Intervention échoue
            $image->move(public_path('storage/avatars'), $filename);
        }

        $user->avatar = $filename;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully!',
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
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully!');
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
            'message' => 'Avatar removed successfully!'
        ]);
    }
}
