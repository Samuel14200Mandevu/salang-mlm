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
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Database\QueryException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // ✅ Validation avec messages personnalisés
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
            // ✅ Messages personnalisés
            'name.required' => '👤 Le nom complet est obligatoire.',
            'name.max' => '👤 Le nom ne doit pas dépasser 255 caractères.',
            'email.required' => '📧 L\'adresse email est obligatoire.',
            'email.email' => '📧 Veuillez saisir une adresse email valide.',
            'email.unique' => '📧 Cette adresse email est déjà utilisée par un autre compte.',
            'email.max' => '📧 L\'email ne doit pas dépasser 255 caractères.',
            'password.required' => '🔑 Le mot de passe est obligatoire.',
            'password.confirmed' => '🔑 Les mots de passe ne correspondent pas.',
            'password.min' => '🔑 Le mot de passe doit contenir au moins 8 caractères.',
            'sponsor_id.required' => '🔗 L\'identifiant du parrain est obligatoire.',
            'terms.required' => '📄 Vous devez accepter les conditions générales.',
            'terms.accepted' => '📄 Vous devez accepter les conditions générales.',
        ]);

        try {
            // ✅ Trouver le sponsor avec messages d'erreur
            $sponsor = null;
            if ($request->sponsor_id) {
                $sponsor = User::find($request->sponsor_id);
                
                if (!$sponsor) {
                    $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();
                }
            }

            if (!$sponsor) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'sponsor_id' => '❌ L\'identifiant du parrain est invalide. Veuillez vérifier le code saisi.',
                    ]);
            }

            // ✅ Vérifier si l'email existe déjà (double vérification)
            if (User::where('email', $request->email)->exists()) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => '📧 Cette adresse email est déjà utilisée. Veuillez vous connecter ou utiliser une autre adresse.',
                    ]);
            }

            // ✅ Générer un ID de parrain unique
            $sponsorCode = $this->generateSponsorId();

            // ✅ Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'sponsor_id' => $sponsorCode,
                'rank_id' => Rank::where('slug', 'distributor')->first()?->id,
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

            // ✅ Créer l'entrée de généalogie
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
            $this->updateTeamCounters($sponsor);

            // ✅ Envoyer la notification de bienvenue
            try {
                $user->notify(new WelcomeNotification($sponsor->name));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification bienvenue: ' . $e->getMessage());
            }

            event(new Registered($user));

            Auth::login($user);

            return redirect(route('dashboard'))
                ->with('success', '🎉 Bienvenue ' . $user->name . ' ! Votre compte a été créé avec succès.');

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'email' => '📧 Cette adresse email est déjà utilisée par un autre compte.',
                    ]);
            }
            
            Log::error('Erreur lors de l\'inscription: ' . $e->getMessage());
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'email' => '⚠️ Une erreur est survenue lors de l\'inscription. Veuillez réessayer.',
                ]);
        }
    }

    /**
     * Générer un ID de parrain unique
     */
    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $random = strtoupper(substr(uniqid(), -6));
        $sponsorCode = $prefix . $random;
        
        while (User::where('sponsor_id', $sponsorCode)->exists()) {
            $random = strtoupper(substr(uniqid(), -6));
            $sponsorCode = $prefix . $random;
        }
        
        return $sponsorCode;
    }

    /**
     * Mettre à jour les compteurs d'équipe
     */
    private function updateTeamCounters(User $user)
    {
        $currentUser = $user;
        $level = 0;
        
        while ($currentUser->sponsor_id && $level < 10) {
            $sponsor = User::where('sponsor_id', $currentUser->sponsor_id)->first();
            
            if (!$sponsor) {
                break;
            }
            
            $sponsor->increment('total_team');
            $currentUser = $sponsor;
            $level++;
        }
    }
}