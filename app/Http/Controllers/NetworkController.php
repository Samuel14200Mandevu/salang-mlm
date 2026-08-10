<?php
// app/Http/Controllers/NetworkController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NetworkController extends Controller
{
    /**
     * Afficher la page principale du réseau
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $parrain = User::find($user->parrain_id);
        
        // ✅ Construire l'arbre complet avec TOUS les niveaux
        $tree = $this->buildTree($user, 0, 999);

        // ✅ Récupérer TOUS les descendants pour les statistiques
        $allDescendants = $this->getAllDescendantsWithLevel($user->id, 1, 999);
        
        // Statistiques du réseau
        $stats = [
            'total' => count($allDescendants),
            'level_1' => $this->countLevelN($user, 1),
            'level_2' => $this->countLevelN($user, 2),
            'level_3' => $this->countLevelN($user, 3),
            'level_4' => $this->countLevelN($user, 4),
            'level_5' => $this->countLevelN($user, 5),
            'level_6' => $this->countLevelN($user, 6),
            'level_7' => $this->countLevelN($user, 7),
            'level_8' => $this->countLevelN($user, 8),
            'level_9' => $this->countLevelN($user, 9),
            'level_10' => $this->countLevelN($user, 10),
            'active' => User::where('parrain_id', $user->id)->where('is_active', true)->count(),
            'total_pv' => $user->team_pv ?? 0,
        ];

        // ✅ Récupérer les filleuls directs pour la vue liste
        $filleuls = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        foreach ($filleuls as $member) {
            $member->level = 1;
            $member->downline_count = User::where('parrain_id', $member->id)->count();
        }

        $recentDownlines = User::where('parrain_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        foreach ($recentDownlines as $member) {
            $member->level = 1;
            $member->downline_count = User::where('parrain_id', $member->id)->count();
        }

        return view('network.index', compact(
            'user',
            'parrain',
            'filleuls',
            'tree',
            'stats',
            'recentDownlines',
            'allDescendants'
        ))->with('controller', $this);
    }

    public function show($id)
    {
        $member = User::with(['package', 'rank', 'genealogy'])->findOrFail($id);
        $currentUser = Auth::user();
        
        $isInNetwork = $this->isInNetwork($currentUser->id, $member->id);
        
        if (!$isInNetwork && $currentUser->id != $member->id) {
            abort(403, 'Vous n\'avez pas accès à ce profil.');
        }

        $downlines = User::where('parrain_id', $member->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        foreach ($downlines as $downline) {
            $downline->level = $this->getUserLevel($member->id, $downline->id);
            $downline->downline_count = User::where('parrain_id', $downline->id)->count();
        }

        $memberStats = [
            'total_downlines' => $downlines->count(),
            'active_downlines' => $downlines->where('is_active', true)->count(),
            'total_pv' => $member->team_pv ?? 0,
            'level' => $this->getUserLevel($currentUser->id, $member->id),
        ];

        return view('network.show', compact('member', 'downlines', 'memberStats'))->with('controller', $this);
    }

    public function treeData(Request $request)
    {
        $userId = $request->get('user_id', Auth::id());
        $user = User::findOrFail($userId);
        $depth = $request->get('depth', 999);
        
        $tree = $this->buildTree($user, 0, $depth);

        return response()->json([
            'success' => true,
            'data' => $tree
        ]);
    }

    // ============================================================
    // MÉTHODES PRIVÉES (BASE DE DONNÉES)
    // ============================================================

    private function buildTree($user, $level = 0, $maxLevel = 999)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        $downlines = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();
            
        $children = [];

        foreach ($downlines as $child) {
            $child->level = $level + 1;
            $childTree = $this->buildTree($child, $level + 1, $maxLevel);
            
            $children[] = [
                'user' => $child,
                'level' => $level + 1,
                'children' => $childTree ? $childTree['children'] : [],
            ];
        }

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children,
        ];
    }

    public function getAllDescendantsWithLevel($userId, $currentLevel = 1, $maxLevel = 999)
    {
        if ($currentLevel > $maxLevel) return [];
        
        $results = [];
        $children = User::where('parrain_id', $userId)->with(['package', 'rank', 'genealogy'])->get();
        
        foreach ($children as $child) {
            $child->level = $currentLevel;
            $results[] = $child;
            $results = array_merge($results, $this->getAllDescendantsWithLevel($child->id, $currentLevel + 1, $maxLevel));
        }
        return $results;
    }

    private function countLevelN($user, $targetLevel)
    {
        $level = 0;
        $currentIds = [$user->id];
        while ($level < $targetLevel) {
            $nextIds = User::whereIn('parrain_id', $currentIds)->pluck('id')->toArray();
            if (empty($nextIds)) return 0;
            $currentIds = $nextIds;
            $level++;
        }
        return count($currentIds);
    }

    private function getUserLevel($parrainId, $userId)
    {
        if ($parrainId == $userId) return 0;
        $level = 0;
        $current = User::find($userId);
        while ($current && $current->parrain_id && $level < 999) {
            $level++;
            if ($current->parrain_id == $parrainId) return $level;
            $current = User::find($current->parrain_id);
        }
        return $level > 0 ? $level : 1;
    }

    private function isInNetwork($parrainId, $userId)
    {
        if ($parrainId == $userId) return true;
        $level = 0;
        $current = User::find($userId);
        while ($current && $current->parrain_id && $level < 999) {
            $level++;
            if ($current->parrain_id == $parrainId) return true;
            $current = User::find($current->parrain_id);
        }
        return false;
    }

    // ============================================================
    // MÉTHODES D'AFFICHAGE (GRADES & AVATARS)
    // ============================================================

    public function getUserRankInfo($user)
    {
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return ['name' => $user->rank->name, 'level' => $user->rank->level];
        }
        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank) return ['name' => $rank->name, 'level' => $rank->level];
        }
        if (is_string($user->rank) && !empty($user->rank)) {
            $levels = [
                'Distributeur' => 1, 'Distributor' => 1, 'Qualification' => 2, 'Supervisor' => 2,
                'Cumul Directeur' => 3, 'Assistant Manager' => 3, 'Directeur' => 4, 'Manager' => 4,
                'Manager Senior' => 5, 'Senior Manager' => 5, 'Directeur Envolée' => 6, 'Soaring Manager' => 6,
                'Saphire Manager' => 7, 'Blue Diamond' => 8, 'Diamond Pearl' => 9,
            ];
            return ['name' => $user->rank, 'level' => $levels[$user->rank] ?? 1];
        }
        return ['name' => 'Distributor', 'level' => 1];
    }

    public function getRankColor($level)
    {
        $colors = [1 => 'rank-level-1', 2 => 'rank-level-2', 3 => 'rank-level-3', 4 => 'rank-level-4', 5 => 'rank-level-5', 6 => 'rank-level-6', 7 => 'rank-level-7', 8 => 'rank-level-8', 9 => 'rank-level-9'];
        return $colors[$level] ?? 'rank-level-1';
    }

    public function getAvatarColor($user)
    {
        if (!$user->is_active) return 'avatar-danger';
        $rankInfo = $this->getUserRankInfo($user);
        $level = $rankInfo['level'];
        if ($level == 1) return 'avatar-neutral';
        if ($level == 2) return 'avatar-info';
        if ($level == 3) return 'avatar-purple';
        if ($level >= 4 && $level <= 6) return 'avatar-warning';
        if ($level >= 7) return 'avatar-gold';
        return 'avatar-success';
    }

    public function countNodes($tree)
    {
        if (!$tree || !isset($tree['children']) || !is_array($tree['children'])) return 0;
        $count = 1;
        foreach ($tree['children'] as $child) $count += $this->countNodes($child);
        return $count;
    }

    // ============================================================
    // LA MÉTHODE D'AFFICHAGE "ARBRE GÉNÉALOGIQUE" (COMME L'IMAGE)
    // ============================================================
    
    public function renderGenealogyTree($node, $controller)
    {
        if (!$node || !isset($node['user'])) {
            return '';
        }

        $user = $node['user'];
        $level = $node['level'] ?? 0;
        $children = $node['children'] ?? [];

        $rankInfo = $controller->getUserRankInfo($user);
        $rankName = $rankInfo['name'];
        $rankLevel = $rankInfo['level'];
        $avatarColor = $controller->getAvatarColor($user);

        // Taille de l'avatar selon le niveau
        $avatarSize = ($level == 0) ? 'avatar-xl' : (($level <= 2) ? 'avatar-lg' : 'avatar-md');
        $badgeSize = ($level <= 2) ? 'text-[9px]' : 'text-[7px]';
        $nameSize = ($level <= 2) ? 'text-sm' : 'text-xs';

        $html = '<div class="tree-branch">';

        // Le noeud (Avatar + Nom + Badges)
        $html .= '<div class="tree-node-container">';
        $html .= '<div class="tree-node' . ($level == 0 ? ' active' : '') . '" onclick="navigateToUser(' . $user->id . ')">';
        
        $html .= '<div class="avatar ' . $avatarSize . ' ' . $avatarColor . ' relative">';
        $html .= strtoupper(substr($user->name, 0, 2));
        $html .= '<span class="status-dot ' . ($user->is_active ? 'online' : 'offline') . '"></span>';
        if ($level > 0) $html .= '<span class="level-badge level-' . $level . '">' . $level . '</span>';
        $html .= '</div>';

        $html .= '<span class="name ' . $nameSize . '">' . e($user->name) . '</span>';
        if ($level == 0) $html .= '<span class="badge badge-success ' . $badgeSize . '">Moi</span>';
        else $html .= '<span class="badge ' . $controller->getRankColor($rankLevel) . ' ' . $badgeSize . '">' . e($rankName) . '</span>';
        $html .= '</div>';
        $html .= '</div>';

        // Les enfants (Récursivité + lignes de connexion)
        if (!empty($children) && is_array($children)) {
            $html .= '<div class="tree-children-container">';

            // Ligne horizontale reliant les enfants s'il y en a plus d'un
            if (count($children) > 1) {
                $html .= '<div class="horizontal-branch-line"></div>';
            }

            $html .= '<div class="tree-children">';
            foreach ($children as $child) {
                $html .= $this->renderGenealogyTree($child, $controller);
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>'; // Fin tree-branch
        return $html;
    }
}