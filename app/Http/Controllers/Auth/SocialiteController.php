<?php
// app/Http/Controllers/Auth/SocialiteController.php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use App\Notifications\ActivationCodeNotification;
use App\Support\ActivationCodeGenerator;
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

    private const INTENT_LOGIN = 'login';

    private const INTENT_LINK_GOOGLE = 'link_google';

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'This provider is not supported.');
        }

        if (request()->query('intent') === self::INTENT_LOGIN) {
            session([
                'socialite_intent' => self::INTENT_LOGIN,
                'social_provider' => $provider,
            ]);
            session()->forget('sponsor_id');

            try {
                return Socialite::driver($provider)->redirect();
            } catch (\Exception $e) {
                Log::error('Socialite login redirect error: '.$e->getMessage());

                return redirect()->route('login')
                    ->with('error', 'Connexion Google indisponible. Réessayez ou utilisez email et mot de passe.');
            }
        }

        $sponsorId = session('sponsor_id')
            ?? request()->query('sponsor_id')
            ?? request()->query('ref')
            ?? request()->input('sponsor_id');

        Log::info('=== SOCIALITE REDIRECT ===');
        Log::info('Provider: ' . $provider);
        Log::info('Sponsor ID: ' . $sponsorId);

        session()->forget('socialite_intent');

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

        if (session('socialite_intent') === self::INTENT_LINK_GOOGLE) {
            return $this->callbackLinkGoogle($provider);
        }

        $loginIntent = session('socialite_intent') === self::INTENT_LOGIN;

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            Log::error('Socialite callback error: ' . $e->getMessage());
            session()->forget(['socialite_intent', 'sponsor_id', 'social_provider']);

            return redirect()->route('login')
                ->with('error', 'Authentification '.ucfirst($provider).' échouée. Réessayez.');
        }

        if (!$socialUser->getEmail()) {
            session()->forget(['socialite_intent', 'sponsor_id', 'social_provider']);

            return redirect($loginIntent ? route('login') : '/register')
                ->with('error', 'Aucune adresse email associée à ce compte Google.');
        }

        $user = User::query()
            ->where(function ($query) use ($provider, $socialUser) {
                $query->where('email', $socialUser->getEmail())
                    ->orWhere(function ($q) use ($provider, $socialUser) {
                        $q->where('provider', $provider)
                            ->where('provider_id', $socialUser->getId());
                    });
            })
            ->first();

        if ($user) {
            if (empty($user->provider_id) || $user->provider != $provider) {
                $user->provider = $provider;
                $user->provider_id = $socialUser->getId();
                $user->avatar = $socialUser->getAvatar() ?? $user->avatar;
                $user->save();
            }

            session()->forget(['sponsor_id', 'social_provider', 'socialite_intent']);

            Auth::login($user);

            if (! $user->is_active) {
                return redirect()->route('activate.index')
                    ->with('warning', 'Votre compte est inactif. Activez-le pour continuer.');
            }

            return redirect()->to($user->loginRedirectUrl())
                ->with('success', 'Bon retour, '.$user->name.' !');
        }

        if ($loginIntent) {
            session()->forget(['socialite_intent', 'sponsor_id', 'social_provider']);

            return redirect()->route('login')
                ->with('error', 'Aucun compte Salang pour cet email Google. Créez un compte avec un code parrain.');
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
            $activationCode = ActivationCodeGenerator::generate();

            // ✅ Créer l'utilisateur avec provider et provider_id (inactif jusqu'à activation)
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id,
                'avatar' => $socialUser->getAvatar(),
                'rank_id' => $rankId,
                'rank' => 'Distributor',
                'is_active' => false,
                'activation_code' => $activationCode,
                'activation_code_expires_at' => now()->addDays(7),
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

            try {
                $user->notify(new ActivationCodeNotification($activationCode));
            } catch (\Exception $e) {
                Log::error('Error sending activation notification (social): '.$e->getMessage());
            }

            Auth::login($user);

            return redirect()->route('activate.index')
                ->with('warning', 'Votre compte a été créé. Activez-le en achetant un package, avec votre code guichet, ou via une demande à l\'administration.');

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

    protected function callbackLinkGoogle(string $provider)
    {
        if ($provider !== 'google') {
            return redirect()->route('profile.settings')
                ->with('error', 'Seul Google est pris en charge pour associer votre email.');
        }

        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google link callback error: ' . $e->getMessage());

            return redirect()->route('profile.settings')
                ->with('error', 'Authentification Google échouée. Réessayez.');
        }

        $userId = session('socialite_link_user_id');
        session()->forget(['socialite_intent', 'socialite_link_user_id', 'social_provider']);

        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Reconnectez-vous, puis associez Google depuis votre profil.');
        }

        if (! $user->hasPlaceholderEmail()) {
            Auth::login($user);

            return redirect()->route('profile.settings')
                ->with('error', 'Votre email a déjà été mis à jour.');
        }

        $googleEmail = strtolower(trim((string) $socialUser->getEmail()));

        if ($googleEmail === '') {
            Auth::login($user);

            return redirect()->route('profile.settings')
                ->with('error', 'Google n’a pas fourni d’adresse email.');
        }

        if (User::where('email', $googleEmail)->where('id', '!=', $user->id)->exists()) {
            Auth::login($user);

            return redirect()->route('profile.settings')
                ->with('error', 'Cette adresse Google est déjà utilisée par un autre compte Salang.');
        }

        $user->email = $googleEmail;
        $user->provider = 'google';
        $user->provider_id = $socialUser->getId();
        $user->email_verified_at = now();
        $user->save();

        Auth::login($user);

        return redirect()->route('profile.settings')
            ->with('success', 'Compte Google associé. Vous pouvez vous connecter avec ' . $googleEmail . '.');
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