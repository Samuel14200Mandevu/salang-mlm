<?php

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
        
        // Récupérer l'arbre généalogique
        $tree = $this->buildTree($user);
        
        // Statistiques du réseau
        $stats = [
            'total_downlines' => User::where('sponsor_id', $user->sponsor_id)->count(),
            'level_1' => User::where('sponsor_id', $user->sponsor_id)->count(),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'active_members' => User::where('sponsor_id', $user->sponsor_id)
                ->where('is_active', true)
                ->count(),
        ];
        
        // Derniers membres parrainés
        $recentDownlines = User::where('sponsor_id', $user->sponsor_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with('package')
            ->get();
        
        return view('network.index', compact('tree', 'stats', 'recentDownlines'));
    }

    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel) {
            return null;
        }

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

    private function countLevel2($user)
    {
        $level1Ids = User::where('sponsor_id', $user->sponsor_id)->pluck('sponsor_id');
        return User::whereIn('sponsor_id', $level1Ids)->count();
    }

    private function countLevel3($user)
    {
        $level1Ids = User::where('sponsor_id', $user->sponsor_id)->pluck('sponsor_id');
        $level2Ids = User::whereIn('sponsor_id', $level1Ids)->pluck('sponsor_id');
        return User::whereIn('sponsor_id', $level2Ids)->count();
    }

    public function treeData()
    {
        $user = Auth::user();
        $tree = $this->buildTree($user, 0, 5);
        return response()->json($tree);
    }

    public function downlines()
    {
        $user = Auth::user();
        
        $downlines = User::where('sponsor_id', $user->sponsor_id)
            ->with(['package', 'rank'])
            ->paginate(20);
        
        return view('network.downlines', compact('downlines'));
    }
}