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

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'This provider is not supported.');
        }

        $sponsorId = session('sponsor_id') 
            ?? request()->query('sponsor_id')
            ?? request()->query('ref')
            ?? request()->input('sponsor_id');

        Log::info('=== SOCIALITE REDIRECT ===');
        Log::info('Provider: ' . $provider);
        Log::info('Sponsor ID: ' . $sponsorId);

        if (!$sponsorId) {
            return redirect('/register')->with('error', 'You must have a sponsor ID to register.');
        }

        // Vérifier le format du code sponsor
        if (!preg_match('/^51[0-9]{4}$/', $sponsorId)) {
            session()->forget('sponsor_id');
            return redirect('/register')->with('error', 'Invalid sponsor code format. Must start with 51 and have 6 digits.');
        }

        $sponsor = User::where('sponsor_id', $sponsorId)->first();
        
        if (!$sponsor) {
            Log::error('Sponsor not found: ' . $sponsorId);
            session()->forget('sponsor_id');
            return redirect('/register')->with('error', 'Invalid sponsor ID: ' . $sponsorId);
        }

        Log::info('Sponsor found: ' . $sponsor->name);

        session(['sponsor_id' => $sponsor->id]);
        session(['social_provider' => $provider]);

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Socialite redirect error: ' . $e->getMessage());
            return redirect('/register')->with('error', 'Erreur de connexion: ' . $e->getMessage());
        }
    }

    public function callback($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'This provider is not supported.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback error: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Authentication error with ' . ucfirst($provider) . '. Please try again.');
        }

        if (!$socialUser->getEmail()) {
            return redirect('/register')->with('error', 'No email address found with this account.');
        }

        // ✅ Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // ✅ Mettre à jour les informations sociales (utiliser provider et provider_id)
            if (empty($user->provider_id) || $user->provider != $provider) {
                $user->provider = $provider;
                $user->provider_id = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->save();
            }

            session()->forget('sponsor_id');
            session()->forget('social_provider');
            Auth::login($user);

            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back ' . $user->name . '!');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Welcome back ' . $user->name . '!');
        }

        // ✅ Nouvel utilisateur - vérifier le sponsor
        $sponsorId = session('sponsor_id');
        if (!$sponsorId) {
            return redirect('/register')->with('error', 'Sponsor ID required for registration.');
        }

        $sponsor = User::find($sponsorId);
        if (!$sponsor) {
            session()->forget('sponsor_id');
            session()->forget('social_provider');
            return redirect('/register')->with('error', 'Invalid sponsor ID.');
        }

        // Générer le code sponsor unique au format 51XXXX
        $sponsorCode = $this->generateSponsorId();

        try {
            $rankId = Rank::where('slug', 'distributor')->first()?->id ?? 1;

            // ✅ Créer l'utilisateur avec provider et provider_id
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'avatar' => $socialUser->getAvatar(),
                'rank_id' => $rankId,
                'rank' => 'Distributor',
                'is_active' => true,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'pv_balance' => 0,
                'bv_balance' => 0,
                'monthly_pv' => 0,
                'monthly_bv' => 0,
                'team_pv' => 0,
                'team_bv' => 0,
                'total_sponsors' => 0,
                'total_team' => 0,
                'qualified_branches' => 0,
                'direct_sponsors_count' => 0,
                'commission_balance' => 0,
                'total_earnings' => 0,
                'kyc_status' => 'not_submitted',
            ]);

            // Créer le portefeuille
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            // Créer la généalogie
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

            // Mettre à jour les statistiques du sponsor
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            // Nettoyer la session
            session()->forget('sponsor_id');
            session()->forget('social_provider');

            // Connecter l'utilisateur
            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome ' . $user->name . '! Your account has been created with ' . ucfirst($provider) . '.');

        } catch (\Exception $e) {
            Log::error('Social registration error: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return redirect('/register')->with('error', 'Error creating account: ' . $e->getMessage());
        }
    }

    /**
     * Générer un code de parrainage unique au format 51XXXX (6 chiffres)
     */
    private function generateSponsorId(): string
    {
        $prefix = '51';
        
        // Trouver le dernier code utilisé commençant par "51"
        $lastCode = User::where('sponsor_id', 'LIKE', '51%')
            ->orderBy('sponsor_id', 'desc')
            ->first();
        
        if ($lastCode) {
            // Extraire les 4 derniers chiffres du dernier code
            $lastNumber = (int) substr($lastCode->sponsor_id, 2);
            // Incrémenter de 1
            $newNumber = $lastNumber + 1;
        } else {
            // Si aucun code n'existe, commencer à 1671 (pour 511671)
            $newNumber = 1671;
        }
        
        // Vérifier si le code existe déjà
        $maxAttempts = 100;
        $attempts = 0;
        $sponsorCode = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        while (User::where('sponsor_id', $sponsorCode)->exists() && $attempts < $maxAttempts) {
            $newNumber++;
            $sponsorCode = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $attempts++;
        }
        
        if ($attempts >= $maxAttempts) {
            // En cas d'échec, générer aléatoirement
            $random = rand(1000, 9999);
            $sponsorCode = $prefix . $random;
            while (User::where('sponsor_id', $sponsorCode)->exists()) {
                $random = rand(1000, 9999);
                $sponsorCode = $prefix . $random;
            }
        }
        
        return $sponsorCode;
    }

    public function storeSponsor(Request $request)
    {
        $request->validate([
            'sponsor_id' => 'required|string'
        ], [
            'sponsor_id.required' => 'Sponsor ID is required.',
        ]);

        // Vérifier le format du code sponsor
        if (!preg_match('/^51[0-9]{4}$/', $request->sponsor_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sponsor code format. Must start with 51 and have 6 digits (e.g., 511739).'
            ], 422);
        }

        $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();

        if (!$sponsor) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid sponsor ID. No user found.'
            ], 422);
        }

        session(['sponsor_id' => $sponsor->id]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsor ID validated.',
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $sponsor->email,
        ]);
    }
}