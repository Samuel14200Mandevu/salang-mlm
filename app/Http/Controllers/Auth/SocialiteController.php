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

    /**
     * Rediriger vers le provider OAuth
     */
    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provider non supporté.');
        }

        // Vérifier si l'ID du parrain est en session
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/login')->with('error', 'Vous devez avoir un ID de parrain pour vous inscrire via les réseaux sociaux.');
        }

        // Vérifier que le parrain existe
        $sponsor = User::where('id', $sponsorId)->orWhere('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            return redirect('/login')->with('error', 'ID de parrain invalide. Veuillez réessayer.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Callback du provider OAuth
     */
    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provider non supporté.');
        }

        // Vérifier que l'ID du parrain existe encore en session
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/login')->with('error', 'ID de parrain requis pour l\'inscription sociale.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Erreur d\'authentification avec ' . ucfirst($provider));
        }

        // Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Mettre à jour les informations si nécessaire
            $providerColumn = $provider . '_id';
            if (empty($user->$providerColumn)) {
                $user->$providerColumn = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->last_provider = $provider;
                $user->save();
            }
            
            session()->forget('sponsor_id');
            Auth::login($user);
            return redirect()->intended('/dashboard');
        }

        // Vérifier que le parrain existe
        $sponsor = User::where('id', $sponsorId)->orWhere('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            return redirect('/login')->with('error', 'ID de parrain invalide.');
        }

        // Créer l'utilisateur avec le sponsor_id
        $newUser = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'password' => bcrypt(Str::random(16)),
            'sponsor_id' => $sponsor->sponsor_id ?? $sponsor->id,
            $provider . '_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified_at' => now(),
            'last_provider' => $provider,
        ]);

        session()->forget('sponsor_id');
        Auth::login($newUser);
        return redirect()->intended('/dashboard');
    }

    /**
     * Stocker l'ID du parrain en session
     */
    public function storeSponsor(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ]);

        // Vérifier que le parrain existe
        $sponsor = User::where('id', $request->sponsor_id)
            ->orWhere('sponsor_id', $request->sponsor_id)
            ->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'ID de parrain invalide. Veuillez vérifier et réessayer.'
            ], 422);
        }

        session(['sponsor_id' => $request->sponsor_id]);
        return response()->json([
            'success' => true,
            'message' => 'ID de parrain validé.'
        ]);
    }
}