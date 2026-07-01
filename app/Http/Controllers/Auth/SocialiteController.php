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

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname(),
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(Str::random(16)),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
        ]);

        Auth::login($newUser);
        return redirect()->intended('/dashboard');
    }
}