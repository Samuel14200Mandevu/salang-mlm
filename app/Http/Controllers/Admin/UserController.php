<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rank;
use App\Models\Package;
use App\Models\Wallet;
use App\Models\Genealogy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index()
    {
        // ✅ Récupérer les utilisateurs avec pagination
        $users = User::with(['rank', 'package'])->orderBy('created_at', 'desc')->paginate(15);
        
        // ✅ Calculer les statistiques
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admins' => User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->count(),
        ];
        
        // ✅ Récupérer les packages pour le filtre
        $packages = Package::all();
        
        return view('admin.users.index', compact('users', 'stats', 'packages'));
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show($id)
    {
        $user = User::with(['rank', 'package'])->findOrFail($id);
        
        // ✅ Récupérer le sponsor (celui qui a ce code de parrain)
        $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();
        
        // ✅ Compter les fillules (ceux qui ont ce code de parrain)
        $downlinesCount = User::where('sponsor_id', $user->sponsor_id)->count();
        
        // ✅ Statistiques des commissions
        $commissionsCount = $user->commissions()->count();
        $totalCommissions = $user->commissions()->where('status', 'paid')->sum('amount') ?? 0;
        
        return view('admin.users.show', compact(
            'user', 
            'sponsor',
            'downlinesCount', 
            'commissionsCount', 
            'totalCommissions'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $ranks = Rank::all();
        $packages = Package::all();
        
        // ✅ Récupérer tous les utilisateurs avec leur code de parrain
        $users = User::select('id', 'name', 'sponsor_id')
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
            'sponsor_id' => 'nullable|string|max:255', // ✅ Code de parrain (VARCHAR)
        ]);

        // ✅ Vérifier si le sponsor existe (si un code est fourni)
        $sponsor = null;
        if ($request->sponsor_id) {
            $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();
            
            if (!$sponsor) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'sponsor_id' => 'Le code de parrain est invalide. Aucun utilisateur trouvé avec ce code.'
                    ]);
            }
        }

        // ✅ Générer un code de parrain unique pour le nouvel utilisateur
        $sponsorCode = $this->generateSponsorCode();
        
        // ✅ Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'sponsor_id' => $sponsorCode, // ✅ Son code de parrain unique
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
            'sponsor_id' => $sponsor?->id, // ✅ L'ID du sponsor (clé étrangère)
            'parent_id' => $sponsor?->id,   // ✅ L'ID du parent
            'level' => $sponsor ? ($sponsor->genealogy?->level ?? 0) + 1 : 0,
            'position' => null,
            'left_count' => 0,
            'right_count' => 0,
            'total_children' => 0,
        ]);

        // ✅ Mettre à jour le compteur du sponsor (si existe)
        if ($sponsor) {
            $sponsor->increment('total_sponsors');
            $sponsor->increment('total_team');
            
            // ✅ Mettre à jour les niveaux supérieurs
            $this->updateTeamCounters($sponsor);
        }

        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur créé avec succès. Code de parrain : ' . $sponsorCode);
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $ranks = Rank::all();
        $packages = Package::all();
        
        // ✅ Récupérer tous les utilisateurs avec leur code de parrain
        $users = User::select('id', 'name', 'sponsor_id')
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
            'sponsor_id' => 'nullable|string|max:255', // ✅ Code de parrain (VARCHAR)
        ]);

        // ✅ Vérifier si le sponsor existe (si un code est fourni)
        if ($request->sponsor_id) {
            $sponsor = User::where('sponsor_id', $request->sponsor_id)->first();
            
            if (!$sponsor) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'sponsor_id' => 'Le code de parrain est invalide. Aucun utilisateur trouvé avec ce code.'
                    ]);
            }
        }

        // ✅ Préparer les données
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'rank_id' => $request->rank_id,
            'package_id' => $request->package_id,
            'is_active' => $request->has('is_active'),
        ];

        // ✅ Mettre à jour le sponsor_id si fourni
        if ($request->filled('sponsor_id')) {
            // Ne pas modifier le code de parrain de l'utilisateur
            // On garde son propre code, on ne change que son sponsor
            // Le sponsor_id est un code VARCHAR, pas un ID
            // Pour changer de sponsor, on doit garder le même code
            // On ne fait rien car le code de parrain est unique et ne change pas
        }

        $user->update($data);

        // ✅ Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // ✅ Supprimer le wallet
        if ($user->wallet) {
            $user->wallet->delete();
        }
        
        // ✅ Supprimer la généalogie
        if ($user->genealogy) {
            $user->genealogy->delete();
        }
        
        // ✅ Supprimer l'avatar
        if ($user->avatar && file_exists(public_path('storage/avatars/' . $user->avatar))) {
            unlink(public_path('storage/avatars/' . $user->avatar));
        }
        
        $user->delete();
        
        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur supprimé avec succès.');
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
            ->with('success', "Utilisateur {$status} avec succès.");
    }

    /**
     * Rechercher des utilisateurs (AJAX)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('sponsor_id', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'email', 'sponsor_id']);
        
        return response()->json($users);
    }

    /**
     * Générer un code de parrain unique
     */
    private function generateSponsorCode(): string
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
        
        while ($currentUser->sponsor_id) {
            // ✅ Chercher le sponsor par son code
            $sponsor = User::where('sponsor_id', $currentUser->sponsor_id)->first();
            
            if (!$sponsor) {
                break;
            }
            
            $sponsor->increment('total_team');
            $currentUser = $sponsor;
        }
    }

    /**
     * Exporter les utilisateurs
     */
    public function export(Request $request)
    {
        $users = User::with(['rank', 'package'])->get();
        
        $filename = 'users_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');
        
        // En-têtes CSV
        fputcsv($handle, [
            'ID', 
            'Nom', 
            'Email', 
            'Téléphone', 
            'Code Parrain', 
            'Sponsor', 
            'Grade', 
            'Package', 
            'PV', 
            'BV', 
            'Statut', 
            'Date d\'inscription'
        ]);
        
        foreach ($users as $user) {
            $sponsor = User::where('sponsor_id', $user->sponsor_id)->first();
            
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone ?? '',
                $user->sponsor_id,
                $sponsor?->name ?? 'Aucun',
                $user->rank?->name ?? 'Distributor',
                $user->package?->name ?? 'Starter',
                $user->pv_balance ?? 0,
                $user->bv_balance ?? 0,
                $user->is_active ? 'Actif' : 'Inactif',
                $user->created_at?->format('d/m/Y H:i') ?? ''
            ]);
        }
        
        fclose($handle);
        
        return response()->stream(
            function() use ($filename) {
                // Le contenu est déjà envoyé
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}