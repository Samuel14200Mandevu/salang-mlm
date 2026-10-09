<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\Genealogy;
use App\Models\RankHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileController extends Controller
{
    protected function accountMeta(User $user): array
    {
        return [
            'referralUrl' => url('/register?ref=' . ($user->sponsor_id ?? '')),
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ];
    }

    public function index()
    {
        $user = Auth::user();
        $sponsor = $user->parrain_id ? User::find($user->parrain_id) : null;
        $meta = $this->accountMeta($user);

        return view('profile.index', array_merge(compact('user', 'sponsor'), $meta));
    }

    public function settings()
    {
        return redirect()->route('profile.index', ['edit' => 1]);
    }

    public function password()
    {
        return view('profile.password');
    }

    public function linkGoogle()
    {
        $user = Auth::user();

        if (! $user->hasPlaceholderEmail()) {
            return redirect()
                ->route('profile.settings')
                ->with('error', 'Votre adresse email n’est pas une adresse provisoire Salang.');
        }

        session([
            'socialite_intent' => 'link_google',
            'socialite_link_user_id' => $user->id,
        ]);

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('profile.settings')
                ->with('error', 'Connexion Google indisponible. Réessayez plus tard.');
        }
    }

    public function referral()
    {
        $user = Auth::user();
        $referralUrl = url('/register?ref=' . ($user->sponsor_id ?? ''));
        $qrSvg = QrCode::format('svg')
            ->size(220)
            ->margin(2)
            ->color(30, 93, 173)
            ->backgroundColor(255, 255, 255)
            ->generate($referralUrl);

        return view('profile.referral', compact('user', 'referralUrl', 'qrSvg'));
    }

    public function about()
    {
        return view('profile.about');
    }

    public function help()
    {
        return view('profile.help');
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

        return redirect()->route('profile.password')
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

        RankHistory::where('user_id', $user->id)->delete();
        Genealogy::where('user_id', $user->id)->orWhere('parent_id', $user->id)->delete();
        $user->tokens()->delete();
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([]);
        }

        $userId = $user->id;
        DB::table('users')->where('id', $userId)->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Votre compte a été supprimé.');
    }
}