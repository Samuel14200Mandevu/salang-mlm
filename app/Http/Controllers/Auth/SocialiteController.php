<?php
// app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
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
            return redirect('/login')->with('error', 'Ce fournisseur n\'est pas supporté.');
        }

        // ✅ Vérifier l'ID du parrain (session, paramètre GET ou POST)
        $sponsorId = session('sponsor_id') ?? request()->query('ref') ?? request()->input('sponsor_id');
        
        if (!$sponsorId) {
            return redirect('/register')->with('error', 'Vous devez avoir un ID de parrain pour vous inscrire.');
        }

        // ✅ Vérifier que le parrain existe
        $sponsor = User::find($sponsorId) ?? User::where('sponsor_id', $sponsorId)->first();
        if (!$sponsor) {
            session()->forget('sponsor_id');
            return redirect('/register')->with('error', 'ID de parrain invalide. Veuillez réessayer.');
        }

        session(['sponsor_id' => $sponsor->id]);
        session(['social_provider' => $provider]);

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Socialite redirect error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Erreur de connexion avec Google. Veuillez réessayer.');
        }
    }

    /**
     * Callback du provider OAuth
     */
    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Ce fournisseur n\'est pas supporté.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Erreur d\'authentification avec ' . ucfirst($provider) . '. Veuillez réessayer.');
        }

        // ✅ Vérifier l'email
        if (!$socialUser->getEmail()) {
            return redirect('/register')->with('error', 'Aucune adresse email trouvée avec ce compte.');
        }

        // ✅ Vérifier si l'utilisateur existe déjà (par email OU provider_id)
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
            
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Bonjour ' . $user->name . ' ! Connexion réussie.');
            }
            
            return redirect()->route('dashboard')
                ->with('success', 'Bonjour ' . $user->name . ' ! Connexion réussie.');
        }

        // ✅ Vérifier que le parrain existe encore
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/register')->with('error', 'ID de parrain requis pour l\'inscription.');
        }

        $sponsor = User::find($sponsorId);
        if (!$sponsor) {
            session()->forget('sponsor_id');
            session()->forget('social_provider');
            return redirect('/register')->with('error', 'ID de parrain invalide.');
        }

        // ✅ Générer un code de parrain unique
        $sponsorCode = $this->generateSponsorId();

        // ✅ Créer le nouvel utilisateur
        try {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'avatar' => $socialUser->getAvatar(),
                $provider . '_id' => $socialUser->getId(),
                'last_provider' => $provider,
                'rank_id' => Rank::where('slug', 'distributor')->first()?->id ?? 1,
                'is_active' => true,
            ]);

            // ✅ Créer le portefeuille
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            // ✅ Créer la généalogie
            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'parent_id' => $sponsor->id,
                'level' => ($sponsor->genealogy?->level ?? 0) + 1,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            // ✅ Mettre à jour les compteurs du sponsor
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');

            session()->forget('sponsor_id');
            session()->forget('social_provider');

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Bienvenue ' . $user->name . ' ! Votre compte a été créé avec Google.');

        } catch (\Exception $e) {
            Log::error('Erreur création utilisateur social: ' . $e->getMessage());
            return redirect('/register')->with('error', 'Erreur lors de la création du compte. Veuillez réessayer.');
        }
    }

    /**
     * Générer un ID de parrain unique
     */
    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $random = strtoupper(Str::random(6));
        $sponsorCode = $prefix . $random;
        
        while (User::where('sponsor_id', $sponsorCode)->exists()) {
            $random = strtoupper(Str::random(6));
            $sponsorCode = $prefix . $random;
        }
        
        return $sponsorCode;
    }

    /**
     * Stocker l'ID du parrain en session
     */
    public function storeSponsor(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ], [
            'sponsor_id.required' => 'L\'ID du parrain est obligatoire.',
        ]);

        $sponsor = User::find($request->sponsor_id) ?? User::where('sponsor_id', $request->sponsor_id)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'ID de parrain invalide. Aucun utilisateur trouvé.'
            ], 422);
        }

        session(['sponsor_id' => $sponsor->id]);
        
        return response()->json([
            'success' => true,
            'message' => 'ID de parrain validé.',
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $sponsor->email,
        ]);
    }
}