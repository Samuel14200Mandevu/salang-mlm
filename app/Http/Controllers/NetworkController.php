<?php
// app/Http/Controllers/NetworkController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rank;
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

        $parrain = User::find($user->parrain_id);
        
        $filleuls = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        $tree = $this->buildTree($user);

        $stats = [
            'total' => $filleuls->count(),
            'level_1' => $this->countLevel1($user),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'level_4' => $this->countLevel4($user),
            'level_5' => $this->countLevel5($user),
            'active' => $filleuls->where('is_active', true)->count(),
        ];

        $recentDownlines = User::where('parrain_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        return view('network.index', compact(
            'user',
            'parrain',
            'filleuls',
            'tree',
            'stats',
            'recentDownlines'
        ))->with('controller', $this);
    }

    /**
     * ✅ MÉTHODE POUR OBTENIR LES INFOS DU GRADE
     */
    public function getUserRankInfo($user)
    {
        // ✅ Si rank est un objet (relation chargée)
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return [
                'name' => $user->rank->name,
                'level' => $user->rank->level,
            ];
        }
        
        // ✅ Si rank_id existe
        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) {
                return [
                    'name' => $rank->name,
                    'level' => $rank->level,
                ];
            }
        }
        
        // ✅ Si rank est une chaîne (stocké directement)
        if (is_string($user->rank) && !empty($user->rank)) {
            $levels = [
                'Distributeur' => 1, 'Distributor' => 1,
                'Qualification' => 2, 'Supervisor' => 2,
                'Cumul Directeur' => 3, 'Assistant Manager' => 3,
                'Directeur' => 4, 'Manager' => 4,
                'Manager Senior' => 5, 'Senior Manager' => 5,
                'Directeur Envolée' => 6, 'Soaring Manager' => 6,
                'Saphire Manager' => 7,
                'Blue Diamond' => 8,
                'Diamond Pearl' => 9,
            ];
            
            return [
                'name' => $user->rank,
                'level' => $levels[$user->rank] ?? 1,
            ];
        }
        
        // ✅ Valeur par défaut
        return ['name' => 'Distributor', 'level' => 1];
    }

    /**
     * ✅ MÉTHODE POUR OBTENIR LA COULEUR DU GRADE
     */
    public function getRankColor($level)
    {
        $colors = [
            1 => 'rank-level-1',
            2 => 'rank-level-2',
            3 => 'rank-level-3',
            4 => 'rank-level-4',
            5 => 'rank-level-5',
            6 => 'rank-level-6',
            7 => 'rank-level-7',
            8 => 'rank-level-8',
            9 => 'rank-level-9',
        ];
        return $colors[$level] ?? 'rank-level-1';
    }

    /**
     * ✅ MÉTHODE POUR OBTENIR LA COULEUR DE L'AVATAR
     */
    public function getAvatarColor($user)
    {
        if (!$user->is_active) {
            return 'avatar-danger';
        }
        
        $rankInfo = $this->getUserRankInfo($user);
        $level = $rankInfo['level'];
        
        if ($level == 1) return 'avatar-neutral';
        if ($level == 2) return 'avatar-info';
        if ($level == 3) return 'avatar-purple';
        if ($level >= 4 && $level <= 6) return 'avatar-warning';
        if ($level >= 7) return 'avatar-gold';
        
        return 'avatar-success';
    }

    private function countLevel1($user)
    {
        return User::where('parrain_id', $user->id)->count();
    }

    private function countLevel2($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();

        if (empty($level1Ids)) {
            return 0;
        }

        return User::whereIn('parrain_id', $level1Ids)->count();
    }

    private function countLevel3($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();

        if (empty($level1Ids)) {
            return 0;
        }

        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();

        if (empty($level2Ids)) {
            return 0;
        }

        return User::whereIn('parrain_id', $level2Ids)->count();
    }

    private function countLevel4($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();
        if (empty($level1Ids)) return 0;

        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();
        if (empty($level2Ids)) return 0;

        $level3Ids = User::whereIn('parrain_id', $level2Ids)->pluck('id')->toArray();
        if (empty($level3Ids)) return 0;

        return User::whereIn('parrain_id', $level3Ids)->count();
    }

    private function countLevel5($user)
    {
        $level1Ids = User::where('parrain_id', $user->id)->pluck('id')->toArray();
        if (empty($level1Ids)) return 0;

        $level2Ids = User::whereIn('parrain_id', $level1Ids)->pluck('id')->toArray();
        if (empty($level2Ids)) return 0;

        $level3Ids = User::whereIn('parrain_id', $level2Ids)->pluck('id')->toArray();
        if (empty($level3Ids)) return 0;

        $level4Ids = User::whereIn('parrain_id', $level3Ids)->pluck('id')->toArray();
        if (empty($level4Ids)) return 0;

        return User::whereIn('parrain_id', $level4Ids)->count();
    }

    private function getAllDescendants($user)
    {
        $descendants = collect();
        $this->getDescendantsRecursive($user, $descendants);
        return $descendants;
    }

    private function getDescendantsRecursive($user, &$descendants)
    {
        $children = User::where('parrain_id', $user->id)->get();
        
        foreach ($children as $child) {
            $descendants->push($child);
            $this->getDescendantsRecursive($child, $descendants);
        }
    }

    private function buildTree($user, $level = 0, $maxLevel = 3)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        $downlines = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();
            
        $children = [];

        foreach ($downlines as $child) {
            $children[] = [
                'user' => $child,
                'level' => $level + 1,
                'children' => $this->buildTree($child, $level + 1, $maxLevel),
            ];
        }

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children,
        ];
    }

    public function treeData()
    {
        $user = Auth::user();
        $tree = $this->buildTree($user, 0, 5);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    public function downlines(Request $request)
    {
        $user = Auth::user();

        $query = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('sponsor_id', 'like', "%{$search}%");
            });
        }

        $downlines = $query->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $downlines
            ]);
        }

        return view('network.downlines', compact('downlines'));
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('q');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $results = User::where('parrain_id', $user->id)
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('sponsor_id', 'LIKE', "%{$search}%");
            })
            ->with(['genealogy'])
            ->limit(20)
            ->get(['id', 'name', 'email', 'sponsor_id', 'avatar', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function apiStats()
    {
        $user = Auth::user();

        $stats = [
            'total' => User::where('parrain_id', $user->id)->count(),
            'level_1' => $this->countLevel1($user),
            'level_2' => $this->countLevel2($user),
            'level_3' => $this->countLevel3($user),
            'level_4' => $this->countLevel4($user),
            'level_5' => $this->countLevel5($user),
            'active' => User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}