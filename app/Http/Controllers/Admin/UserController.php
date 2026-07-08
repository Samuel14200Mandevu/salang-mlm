<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rank;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Genealogy;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::with(['rank', 'package']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('rank')) {
            $query->where('rank', $request->rank);
        }

        if ($request->filled('package')) {
            $query->where('package_id', $request->package);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count(),
            'with_package' => User::whereNotNull('package_id')->count(),
            'with_kyc' => User::where('kyc_status', 'verified')->count(),
        ];

        $ranks = Rank::all();
        $packages = Package::all();

        return view('admin.users.index', compact('users', 'stats', 'ranks', 'packages'));
    }

    /**
     * Détails d'un utilisateur
     */
    public function show($id)
    {
        $user = User::with(['rank', 'package', 'wallet'])->findOrFail($id);
        
        // ✅ Récupérer le parrain (celui qui a invité l'utilisateur)
        $parrain = User::find($user->parrain_id);
        
        // ✅ Filleuls (ceux invités par l'utilisateur)
        $filleuls = User::where('parrain_id', $user->id)->get();
        $filleulsCount = $filleuls->count();
        $filleulsActifs = $filleuls->where('is_active', true)->count();
        
        // ✅ Statistiques des commissions
        $commissionsStats = [
            'total' => $user->commissions()->where('status', 'paid')->sum('amount'),
            'direct' => $user->commissions()->where('type', 'direct')->where('status', 'paid')->sum('amount'),
            'indirect' => $user->commissions()->where('type', 'indirect')->where('status', 'paid')->sum('amount'),
            'leadership' => $user->commissions()->where('type', 'leadership')->where('status', 'paid')->sum('amount'),
            'retail' => $user->commissions()->where('type', 'retail')->where('status', 'paid')->sum('amount'),
            'pending' => $user->commissions()->where('status', 'pending')->sum('amount'),
        ];

        // ✅ Historique des grades
        $rankHistory = $user->rankHistory()->with(['oldRank', 'newRank'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ✅ Dernières transactions
        $recentTransactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.users.show', compact(
            'user',
            'parrain',
            'filleuls',
            'filleulsCount',
            'filleulsActifs',
            'commissionsStats',
            'rankHistory',
            'recentTransactions'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $ranks = Rank::all();
        $packages = Package::all();
        
        // ✅ Récupérer tous les utilisateurs pour le choix du parrain
        $users = User::select('id', 'name', 'email', 'sponsor_id')
            ->orderBy('name')
            ->get();
            
        return view('admin.users.create', compact('ranks', 'packages', 'users'));
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'rank_id' => 'nullable|exists:ranks,id',
            'package_id' => 'nullable|exists:packages,id',
            'parrain_id' => 'nullable|exists:users,id', // ✅ ID du parrain
            'is_active' => 'boolean',
        ]);

        // ✅ Vérifier si le parrain existe
        $parrain = null;
        if ($request->filled('parrain_id')) {
            $parrain = User::find($request->parrain_id);
            if (!$parrain) {
                return back()->withInput()->withErrors([
                    'parrain_id' => 'Le parrain sélectionné n\'existe pas.'
                ]);
            }
        }

        // ✅ Générer un code de parrain unique
        $sponsorCode = $this->generateSponsorCode();
        
        // ✅ Déterminer le grade si non spécifié
        $rankId = $request->rank_id;
        if (!$rankId && $request->package_id) {
            $package = Package::find($request->package_id);
            if ($package) {
                $rank = Rank::where('min_pv', '<=', $package->pv_value)
                    ->orderBy('min_pv', 'desc')
                    ->first();
                $rankId = $rank?->id;
            }
        }

        DB::beginTransaction();

        try {
            // ✅ Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'rank_id' => $rankId,
                'package_id' => $request->package_id,
                'parrain_id' => $parrain?->id,
                'sponsor_id' => $sponsorCode,
                'is_active' => $request->has('is_active'),
                'pv_balance' => $request->package_id ? Package::find($request->package_id)?->pv_value ?? 0 : 0,
                'bv_balance' => $request->package_id ? Package::find($request->package_id)?->bv_value ?? 0 : 0,
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
                'sponsor_id' => $parrain?->id,
                'parent_id' => $parrain?->id,
                'level' => $parrain ? ($parrain->genealogy?->level ?? 0) + 1 : 0,
                'position' => null,
                'left_count' => 0,
                'right_count' => 0,
                'total_children' => 0,
            ]);

            // ✅ Mettre à jour les compteurs du parrain
            if ($parrain) {
                $parrain->increment('total_sponsors');
                $parrain->increment('total_team');
                $this->updateTeamCounters($parrain);
            }

            DB::commit();

            // ✅ Envoyer la notification de bienvenue
            try {
                $user->notify(new WelcomeNotification($parrain?->name));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification bienvenue', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('admin.users')
                ->with('success', "✅ Utilisateur créé avec succès. Code de parrain : {$sponsorCode}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création utilisateur', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $user = User::with(['rank', 'package'])->findOrFail($id);
        $ranks = Rank::all();
        $packages = Package::all();
        
        // ✅ Récupérer tous les utilisateurs pour le choix du parrain
        $users = User::select('id', 'name', 'email', 'sponsor_id')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get();
            
        return view('admin.users.edit', compact('user', 'ranks', 'packages', 'users'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'rank_id' => 'nullable|exists:ranks,id',
            'package_id' => 'nullable|exists:packages,id',
            'parrain_id' => 'nullable|exists:users,id|not_in:' . $id,
            'is_active' => 'boolean',
            'kyc_status' => 'nullable|in:not_submitted,pending,partial,verified,rejected',
        ]);

        DB::beginTransaction();

        try {
            // ✅ Mise à jour des données
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'rank_id' => $request->rank_id,
                'package_id' => $request->package_id,
                'parrain_id' => $request->parrain_id,
                'is_active' => $request->has('is_active'),
                'kyc_status' => $request->kyc_status ?? $user->kyc_status,
            ];

            // ✅ Mise à jour du mot de passe si fourni
            if ($request->filled('password')) {
                $request->validate(['password' => 'min:8|confirmed']);
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // ✅ Mettre à jour la généalogie si parrain changé
            if ($request->has('parrain_id') && $request->parrain_id != $user->parrain_id) {
                $genealogy = Genealogy::where('user_id', $user->id)->first();
                if ($genealogy) {
                    $newParrain = User::find($request->parrain_id);
                    $genealogy->sponsor_id = $newParrain?->id;
                    $genealogy->parent_id = $newParrain?->id;
                    $genealogy->level = $newParrain ? ($newParrain->genealogy?->level ?? 0) + 1 : 0;
                    $genealogy->save();
                }
            }

            DB::commit();

            return redirect()->route('admin.users')
                ->with('success', "✅ Utilisateur {$user->name} mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour utilisateur', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // ✅ Empêcher la suppression de son propre compte
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        DB::beginTransaction();

        try {
            // ✅ Supprimer les dépendances
            if ($user->wallet) {
                $user->wallet->delete();
            }
            
            if ($user->genealogy) {
                $user->genealogy->delete();
            }
            
            // ✅ Supprimer l'avatar
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }
            
            // ✅ Mettre à jour les compteurs du parrain
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $parrain->decrement('total_sponsors');
                }
            }

            // ✅ Réassigner les filleuls
            User::where('parrain_id', $user->id)->update(['parrain_id' => null]);

            $user->delete();

            DB::commit();

            return redirect()->route('admin.users')
                ->with('success', "🗑️ Utilisateur {$user->name} supprimé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression utilisateur', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // ✅ Empêcher la désactivation de son propre compte
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activé' : 'désactivé';
        return redirect()->route('admin.users')
            ->with('success', "✅ Utilisateur {$status} avec succès.");
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $newPassword = Str::random(10);
        
        $user->password = Hash::make($newPassword);
        $user->save();
        
        // TODO: Envoyer un email avec le nouveau mot de passe
        // $user->notify(new PasswordResetNotification($newPassword));
        
        return redirect()->route('admin.users')
            ->with('success', "🔑 Mot de passe réinitialisé pour {$user->name}. Nouveau mot de passe : {$newPassword}");
    }

    /**
     * Assigner un package à un utilisateur
     */
    public function assignPackage(Request $request, $id)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($id);
        $package = Package::find($request->package_id);

        $user->package_id = $package->id;
        $user->pv_balance += $package->pv_value;
        $user->bv_balance += $package->bv_value;
        $user->save();

        // ✅ Mettre à jour le grade
        $rank = Rank::where('min_pv', '<=', $user->pv_balance)
            ->orderBy('min_pv', 'desc')
            ->first();
        if ($rank) {
            $user->rank_id = $rank->id;
            $user->rank = $rank->name;
            $user->save();
        }

        return redirect()->route('admin.users.show', $id)
            ->with('success', "📦 Package {$package->name} assigné à {$user->name}.");
    }

    /**
     * Générer un code de parrain unique
     */
    private function generateSponsorCode(): string
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
     * Mettre à jour les compteurs d'équipe
     */
    private function updateTeamCounters(User $user)
    {
        $currentUser = $user;
        $level = 0;
        
        while ($currentUser && $level < 10) {
            $parrain = User::find($currentUser->parrain_id);
            if (!$parrain) break;
            
            $parrain->increment('total_team');
            $currentUser = $parrain;
            $level++;
        }
    }

    /**
     * Rechercher des utilisateurs (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('sponsor_id', 'like', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'email', 'sponsor_id', 'avatar']);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Exporter les utilisateurs
     */
    public function export(Request $request)
    {
        $query = User::with(['rank', 'package']);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'Nom', 'Email', 'Téléphone', 'Code Parrain', 'Parrain ID',
                'Grade', 'Package', 'PV', 'BV', 'Statut', 'KYC', 'Date d\'inscription'
            ]);

            foreach ($users as $user) {
                $parrain = User::find($user->parrain_id);
                
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '',
                    $user->sponsor_id,
                    $parrain?->name ?? 'Aucun',
                    $user->rank?->name ?? 'Distributor',
                    $user->package?->name ?? 'Aucun',
                    $user->pv_balance ?? 0,
                    $user->bv_balance ?? 0,
                    $user->is_active ? 'Actif' : 'Inactif',
                    $user->kyc_status ?? 'Non soumis',
                    $user->created_at?->format('d/m/Y H:i') ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}