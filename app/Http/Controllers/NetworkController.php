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
        
        if (!$user) {
            return redirect()->route('login');
        }

        // ✅ Récupérer le parrain (celui qui a parrainé l'utilisateur)
        $parrain = User::find($user->parrain_id);
        
        // ✅ Récupérer les filleuls (ceux qui ont parrain_id = id de l'utilisateur)
        $filleuls = User::where('parrain_id', $user->id)->get();
        
        // ✅ Construire l'arbre généalogique
        $tree = $this->buildTree($user);
        
        // ✅ Statistiques du réseau
        $stats = [
            'total' => User::where('parrain_id', $user->id)->count(),
            'level_1' => User::where('parrain_id', $user->id)->count(),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'active' => User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ];
        
        // ✅ Derniers membres parrainés
        $recentDownlines = User::where('parrain_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['package', 'rank'])
            ->get();
        
        return view('network.index', compact(
            'user', 
            'parrain', 
            'filleuls',
            'tree', 
            'stats', 
            'recentDownlines'
        ));
    }

    /**
     * Construire l'arbre généalogique
     */
    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        // ✅ Récupérer les fillules (parrain_id = id de l'utilisateur)
        $downlines = User::where('parrain_id', $user->id)->get();
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
        // Récupérer les IDs des filleuls directs
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();
        
        if (empty($level1Ids)) {
            return 0;
        }
        
        return User::whereIn('parrain_id', $level1Ids)->count();
    }

    /**
     * Compter les membres de niveau 3
     */
    private function countLevel3($user)
    {
        // Récupérer les IDs des filleuls directs
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();
        
        if (empty($level1Ids)) {
            return 0;
        }
        
        // Récupérer les IDs des filleuls de niveau 2
        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();
        
        if (empty($level2Ids)) {
            return 0;
        }
        
        return User::whereIn('parrain_id', $level2Ids)->count();
    }

    /**
     * Données de l'arbre en JSON
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
        
        $downlines = User::where('parrain_id', $user->id)
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
        
        $results = User::where('parrain_id', $user->id)
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