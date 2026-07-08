<?php
// app/Http/Controllers/NetworkController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Genealogy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // ✅ Récupérer l'arbre généalogique
        $tree = $this->buildTree($user);
        
        // ✅ Statistiques du réseau - CORRIGÉ
        $stats = [
            // Total des fillules (ceux qui ont ce user comme sponsor)
            'total_downlines' => User::where('sponsor_id', $user->sponsor_id)->count(),
            // Niveau 1 (sponsorisés directement)
            'level_1' => User::where('sponsor_id', $user->sponsor_id)->count(),
            // Niveau 2 (sponsorisés par les fillules)
            'level_2' => $this->countLevel2($user),
            // Niveau 3
            'level_3' => $this->countLevel3($user),
            // Membres actifs
            'active_members' => User::where('sponsor_id', $user->sponsor_id)
                ->where('is_active', true)
                ->count(),
        ];
        
        // ✅ Derniers membres parrainés
        $recentDownlines = User::where('sponsor_id', $user->sponsor_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with('package')
            ->get();
        
        return view('network.index', compact('tree', 'stats', 'recentDownlines'));
    }

    /**
     * Construire l'arbre généalogique
     * Utilise sponsor_id comme code unique
     */
    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel) {
            return null;
        }

        // ✅ Récupérer les fillules (ceux qui ont sponsor_id = code de parrain du user)
        $downlines = User::where('sponsor_id', $user->sponsor_id)->get();
        $children = [];

        foreach ($downlines as $child) {
            $children[] = [
                'user' => $child,
                'children' => $this->buildTree($child, $level + 1, $maxLevel),
            ];
        }

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children,
        ];
    }

    /**
     * Compter les membres de niveau 2
     */
    private function countLevel2($user)
    {
        // ✅ Récupérer les codes de parrain des fillules directes
        $level1SponsorCodes = User::where('sponsor_id', $user->sponsor_id)
            ->pluck('sponsor_id');
        
        // ✅ Compter les utilisateurs qui ont ces codes comme sponsor
        return User::whereIn('sponsor_id', $level1SponsorCodes)->count();
    }

    /**
     * Compter les membres de niveau 3
     */
    private function countLevel3($user)
    {
        // ✅ Récupérer les codes de parrain des fillules directes
        $level1SponsorCodes = User::where('sponsor_id', $user->sponsor_id)
            ->pluck('sponsor_id');
        
        // ✅ Récupérer les codes de parrain des fillules de niveau 2
        $level2SponsorCodes = User::whereIn('sponsor_id', $level1SponsorCodes)
            ->pluck('sponsor_id');
        
        // ✅ Compter les utilisateurs qui ont ces codes comme sponsor
        return User::whereIn('sponsor_id', $level2SponsorCodes)->count();
    }

    /**
     * Données de l'arbre en JSON (pour les graphiques)
     */
    public function treeData()
    {
        $user = Auth::user();
        $tree = $this->buildTree($user, 0, 5);
        return response()->json($tree);
    }

    /**
     * Liste paginée des fillules
     */
    public function downlines()
    {
        $user = Auth::user();
        
        // ✅ Récupérer les fillules avec leurs relations
        $downlines = User::where('sponsor_id', $user->sponsor_id)
            ->with(['package', 'rank'])
            ->paginate(20);
        
        return view('network.downlines', compact('downlines'));
    }

    /**
     * Rechercher un membre dans le réseau
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q');
        
        $results = User::where('sponsor_id', $user->sponsor_id)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();
        
        return response()->json($results);
    }
}