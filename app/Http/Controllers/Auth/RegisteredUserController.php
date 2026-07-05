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

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['nullable', 'string'], // ✅ Plus besoin de exists car c'est une colonne
        ]);

        // ✅ Trouver le sponsor (peut être par ID utilisateur ou sponsor_id)
        $sponsor = null;
        if ($request->sponsor_id) {
            // Chercher d'abord par ID utilisateur
            $sponsor = User::find($request->sponsor_id);
            
            // Si pas trouvé, chercher par sponsor_id
            if (!$sponsor) {
                $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();
            }
        }

        // ✅ Générer un ID de parrain unique
        $sponsorCode = $this->generateSponsorId();

        // ✅ Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'sponsor_id' => $sponsorCode, // ✅ Le code de parrain unique
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
            'sponsor_id' => $sponsor?->id,  // ✅ L'ID du sponsor (clé étrangère)
            'parent_id' => $sponsor?->id,   // ✅ L'ID du parent
            'level' => $sponsor ? ($sponsor->genealogy?->level ?? 0) + 1 : 0,
            'position' => null,
            'left_count' => 0,
            'right_count' => 0,
            'total_children' => 0,
        ]);

        // ✅ Mettre à jour le compteur du sponsor
        if ($sponsor) {
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            
            // ✅ Mettre à jour les niveaux supérieurs
            $this->updateTeamCounters($sponsor);
        }

        // ✅ Envoyer la notification de bienvenue
        try {
            $sponsorName = $sponsor ? $sponsor->name : null;
            $user->notify(new WelcomeNotification($sponsorName));
        } catch (\Exception $e) {
            Log::error('Erreur envoi notification bienvenue: ' . $e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Générer un ID de parrain unique
     */
    private function generateSponsorId(): string
    {
        $prefix = 'SAL';
        $random = strtoupper(substr(uniqid(), -6));
        $sponsorCode = $prefix . $random;
        
        // Vérifier l'unicité
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
        // ✅ Monter dans la hiérarchie des sponsors
        $currentUser = $user;
        
        while ($currentUser->sponsor_id) {
            // Trouver le sponsor de l'utilisateur actuel
            $sponsor = User::where('sponsor_id', $currentUser->sponsor_id)->first();
            
            if (!$sponsor) {
                break;
            }
            
            $sponsor->increment('total_team');
            $currentUser = $sponsor;
        }
    }
}