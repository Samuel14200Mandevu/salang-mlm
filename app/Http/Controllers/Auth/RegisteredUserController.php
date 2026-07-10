<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Models\Rank;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // ✅ Validation
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 
                'string', 
                'lowercase', 
                'email', 
                'max:255', 
                'unique:users,email',
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'string'],
            'terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
            'sponsor_id.required' => 'L\'identifiant du parrain est obligatoire.',
            'terms.required' => 'Vous devez accepter les conditions générales.',
            'terms.accepted' => 'Vous devez accepter les conditions générales.',
        ]);

        try {
            // ✅ LOG : Voir ce qui est reçu
            Log::info('Tentative d\'inscription', [
                'email' => $request->email,
                'sponsor_id_input' => $request->sponsor_id,
            ]);

            // ✅ Trouver le sponsor
            $sponsor = $this->findSponsor($request->sponsor_id);

            // ✅ LOG : Résultat de la recherche
            Log::info('Résultat recherche sponsor', [
                'sponsor_id_input' => $request->sponsor_id,
                'sponsor_trouve' => $sponsor ? 'OUI' : 'NON',
                'sponsor_id' => $sponsor ? $sponsor->id : 'N/A',
                'sponsor_name' => $sponsor ? $sponsor->name : 'N/A',
                'sponsor_sponsor_id' => $sponsor ? $sponsor->sponsor_id : 'N/A',
            ]);

            if (!$sponsor) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'sponsor_id' => 'L\'identifiant du parrain est invalide ou n\'existe pas. Vérifiez que le code est correct (ex: SALDEBF71).'
                    ]);
            }

            // ✅ Vérifier que l'email n'existe pas déjà
            if (User::where('email', $request->email)->exists()) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => 'Cette adresse email est déjà utilisée par un autre compte.'
                    ]);
            }

            DB::beginTransaction();

            // ✅ Générer un ID de parrain unique
            $sponsorCode = $this->generateSponsorId();

            // ✅ Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'sponsor_id' => $sponsorCode,
                'parrain_id' => $sponsor->id, // ✅ Le vrai parrain (ID de l'utilisateur)
                'rank_id' => Rank::where('slug', 'distributor')->first()?->id,
                'rank' => 'Distributor',
                'is_active' => true,
                'pv_balance' => 0,
                'bv_balance' => 0,
                'total_sponsors' => 0,
                'total_team' => 0,
            ]);

            Log::info('Utilisateur créé avec succès', [
                'user_id' => $user->id,
                'user_sponsor_id' => $user->sponsor_id,
                'user_parrain_id' => $user->parrain_id,
                'parrain_name' => $sponsor->name,
            ]);

            // ✅ Créer le portefeuille
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);

            // ✅ Créer l'entrée de généalogie
            $level = ($sponsor->genealogy?->level ?? 0) + 1;

            Genealogy::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'parent_id' => $sponsor->id,
                'level' => $level,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            // ✅ Mettre à jour les compteurs du sponsor
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            $sponsor->save();

            // ✅ Mettre à jour les compteurs d'équipe (limité à 5 niveaux)
            $this->updateTeamCountersOptimized($sponsor, 1);

            DB::commit();

            // ✅ Envoyer la notification
            try {
                $user->notify(new WelcomeNotification($sponsor->name));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification: ' . $e->getMessage());
            }

            event(new Registered($user));
            Auth::login($user);

            // ✅ Redirection vers le dashboard
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Bienvenue ' . $user->name . ' ! Vous êtes maintenant membre de Salang Group.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Erreur SQL inscription: ' . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            
            if ($e->getCode() == 23000) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => 'Cette adresse email est déjà utilisée par un autre compte.'
                    ]);
            }
            
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur inscription: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
                ]);
        }
    }

    /**
     * Trouver le sponsor - Version améliorée
     */
    private function findSponsor(string $sponsorId): ?User
    {
        // ✅ 1. Si c'est un nombre, chercher par ID utilisateur
        if (is_numeric($sponsorId)) {
            $sponsor = User::find((int)$sponsorId);
            if ($sponsor) {
                Log::info('Sponsor trouvé par ID utilisateur', ['id' => $sponsorId, 'name' => $sponsor->name]);
                return $sponsor;
            }
        }
        
        // ✅ 2. Chercher par sponsor_id (code unique comme "SALDEBF71") - C'est le plus courant
        $sponsor = User::where('sponsor_id', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor trouvé par sponsor_id', ['sponsor_id' => $sponsorId, 'name' => $sponsor->name]);
            return $sponsor;
        }
        
        // ✅ 3. Chercher par email (cas où l'utilisateur entre l'email du parrain)
        $sponsor = User::where('email', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor trouvé par email', ['email' => $sponsorId, 'name' => $sponsor->name]);
            return $sponsor;
        }
        
        // ✅ 4. Chercher par nom (cas où l'utilisateur entre le nom du parrain)
        $sponsor = User::where('name', $sponsorId)->first();
        if ($sponsor) {
            Log::info('Sponsor trouvé par nom', ['name' => $sponsorId, 'id' => $sponsor->id]);
            return $sponsor;
        }
        
        Log::warning('Sponsor NON trouvé', ['sponsor_id' => $sponsorId]);
        return null;
    }

    /**
     * Générer un ID de parrain unique
     */
    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $maxAttempts = 10;
        $attempts = 0;
        
        do {
            $random = strtoupper(substr(uniqid(), -6));
            $sponsorCode = $prefix . $random;
            $attempts++;
        } while (User::where('sponsor_id', $sponsorCode)->exists() && $attempts < $maxAttempts);
        
        if ($attempts >= $maxAttempts) {
            // Fallback avec timestamp
            $sponsorCode = $prefix . strtoupper(substr(md5(time() . rand()), -6));
        }
        
        return $sponsorCode;
    }

    /**
     * Mettre à jour les compteurs d'équipe (limité à 5 niveaux)
     */
    private function updateTeamCountersOptimized(User $user, int $level): void
    {
        // ✅ Limiter à 5 niveaux maximum
        if ($level > 5) {
            return;
        }

        $currentUser = $user;
        $currentLevel = $level;

        while ($currentUser->parrain_id && $currentLevel <= 5) {
            try {
                $sponsor = User::find($currentUser->parrain_id);
                
                if (!$sponsor) {
                    break;
                }

                $sponsor->increment('total_team');
                $currentUser = $sponsor;
                $currentLevel++;

            } catch (\Exception $e) {
                Log::warning('Erreur update compteurs équipe', [
                    'user_id' => $currentUser->id,
                    'level' => $currentLevel,
                    'error' => $e->getMessage()
                ]);
                break;
            }
        }
    }
}