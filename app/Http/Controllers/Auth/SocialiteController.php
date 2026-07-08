<?php
// app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialiteController extends Controller
{
    protected $providers = ['google', 'facebook', 'twitter', 'instagram', 'tiktok'];

    /**
     * Rediriger vers le provider OAuth
     */
    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', '❌ Ce fournisseur n\'est pas supporté.');
        }

        // ✅ Vérifier l'ID du parrain
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/register')->with('error', '🔗 Vous devez avoir un ID de parrain pour vous inscrire.');
        }

        // ✅ Vérifier que le parrain existe
        $sponsor = User::find($sponsorId) ?? User::where('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            return redirect('/register')->with('error', '❌ ID de parrain invalide. Veuillez réessayer.');
        }

        session(['social_provider' => $provider]);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Callback du provider OAuth
     */
    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', '❌ Ce fournisseur n\'est pas supporté.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback error: ' . $e->getMessage());
            return redirect('/register')->with('error', '⚠️ Erreur d\'authentification avec ' . ucfirst($provider) . '. Veuillez réessayer.');
        }

        // ✅ Vérifier l'email
        if (!$socialUser->getEmail()) {
            return redirect('/register')->with('error', '📧 Aucune adresse email trouvée avec ce compte ' . ucfirst($provider) . '.');
        }

        // ✅ Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // ✅ Mettre à jour les informations
            $providerColumn = $provider . '_id';
            if (empty($user->$providerColumn)) {
                $user->$providerColumn = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->last_provider = $provider;
                $user->save();
            }
            
            session()->forget('sponsor_id');
            session()->forget('social_provider');
            Auth::login($user);
            
            return redirect()->intended('/dashboard')
                ->with('success', '👋 Bonjour ' . $user->name . ' ! Connexion réussie avec ' . ucfirst($provider) . '.');
        }

        // ✅ Vérifier que le parrain existe encore
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/register')->with('error', '🔗 ID de parrain requis pour l\'inscription.');
        }

        $sponsor = User::find($sponsorId) ?? User::where('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            session()->forget('social_provider');
            return redirect('/register')->with('error', '❌ ID de parrain invalide. Veuillez réessayer.');
        }

        // ✅ Stocker les données sociales
        session([
            'social_name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
            'social_email' => $socialUser->getEmail(),
            'social_avatar' => $socialUser->getAvatar(),
            'social_provider' => $provider,
            'social_provider_id' => $socialUser->getId(),
            'social_sponsor_id' => $sponsor->id,
        ]);

        return redirect()->route('register')->with('social_data', [
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'sponsor_id' => $sponsor->id,
        ]);
    }

    /**
     * Stocker l'ID du parrain en session
     */
    public function storeSponsor(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ], [
            'sponsor_id.required' => '🔗 L\'ID du parrain est obligatoire.',
        ]);

        // ✅ Vérifier que le parrain existe
        $sponsor = User::find($request->sponsor_id) ?? User::where('sponsor_id', $request->sponsor_id)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => '❌ ID de parrain invalide. Aucun utilisateur trouvé.',
                'details' => 'Vérifiez l\'ID saisi et réessayez.'
            ], 422);
        }

        session(['sponsor_id' => $sponsor->id]);
        
        return response()->json([
            'success' => true,
            'message' => '✅ ID de parrain validé.',
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $sponsor->email,
        ]);
    }
}