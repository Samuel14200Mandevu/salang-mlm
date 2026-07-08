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

        // ✅ Récupérer l'arbre généalogique
        $tree = $this->buildTree($user);
        
        // ✅ Statistiques du réseau
        $stats = [
            'total' => User::where('sponsor_id', $user->sponsor_id)->count(),
            'level_1' => User::where('sponsor_id', $user->sponsor_id)->count(),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'active' => User::where('sponsor_id', $user->sponsor_id)
                ->where('is_active', true)
                ->count(),
        ];
        
        // ✅ Derniers membres parrainés
        $recentDownlines = User::where('sponsor_id', $user->sponsor_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['package', 'rank'])
            ->get();
        
        return view('network.index', compact('user', 'tree', 'stats', 'recentDownlines'));
    }

    /**
     * Construire l'arbre généalogique
     */
    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        // ✅ Récupérer les fillules
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
        $level1SponsorCodes = User::where('sponsor_id', $user->sponsor_id)
            ->pluck('sponsor_id')
            ->filter()
            ->toArray();
        
        if (empty($level1SponsorCodes)) {
            return 0;
        }
        
        return User::whereIn('sponsor_id', $level1SponsorCodes)->count();
    }

    /**
     * Compter les membres de niveau 3
     */
    private function countLevel3($user)
    {
        $level1SponsorCodes = User::where('sponsor_id', $user->sponsor_id)
            ->pluck('sponsor_id')
            ->filter()
            ->toArray();
        
        if (empty($level1SponsorCodes)) {
            return 0;
        }
        
        $level2SponsorCodes = User::whereIn('sponsor_id', $level1SponsorCodes)
            ->pluck('sponsor_id')
            ->filter()
            ->toArray();
        
        if (empty($level2SponsorCodes)) {
            return 0;
        }
        
        return User::whereIn('sponsor_id', $level2SponsorCodes)->count();
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