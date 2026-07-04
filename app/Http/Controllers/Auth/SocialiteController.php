<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    protected $providers = ['google', 'facebook', 'twitter', 'instagram', 'tiktok'];

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provider non supporté.');
        }
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provider non supporté.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Erreur d\'authentification avec ' . ucfirst($provider));
        }

        // Vérifie si l'utilisateur existe déjà avec cet email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Met à jour le provider_id si nécessaire (pour un utilisateur existant)
            $providerColumn = $provider . '_id';
            if (empty($user->$providerColumn)) {
                $user->$providerColumn = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->last_provider = $provider;
                $user->save();
            }
            
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        // Crée un nouvel utilisateur
        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(Str::random(16)),
            $provider . '_id' => $socialUser->getId(),  // google_id, facebook_id, etc.
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
            'last_provider' => $provider,
        ]);

        Auth::login($newUser);
        return redirect()->intended('/dashboard');
    }
}