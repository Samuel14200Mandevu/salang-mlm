<?php
// app/Http/Controllers/NetworkController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NetworkController extends Controller
{
    /**
     * Afficher la page principale du reseau
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $parrain = $user->parrain_id ? User::find($user->parrain_id) : null;

        // Niveau 1 visible ; branches plus profondes via « Afficher filleuls »
        $tree = $this->buildTree($user, 0, 1);
        $stats = $this->networkStatsFast($user);

        // Vue liste retirée de index — collection vide pour compatibilité blade.
        $filleuls = collect();
        $allDescendants = [];

        return view('network.index', compact(
            'user',
            'parrain',
            'filleuls',
            'tree',
            'stats',
            'allDescendants'
        ))->with('controller', $this);
    }

    /**
     * Recuperer les enfants d'un noeud specifique (AJAX)
     */
    public function getChildren($userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();
        
        // Verifier que l'utilisateur a acces a ce noeud
        if ($currentUser->id != $userId && !$this->isInNetwork($currentUser->id, $userId)) {
            return response()->json(['error' => 'Acces non autorise'], 403);
        }
        
        $children = User::where('parrain_id', $userId)
            ->with(['package', 'rank', 'genealogy'])
            ->withCount('filleuls as children_count')
            ->get();

        $parentLevel = $currentUser->id == $userId
            ? 0
            : $this->getUserLevel($currentUser->id, (int) $userId);
        $childLevel = $parentLevel + 1;

        $result = [];
        foreach ($children as $child) {
            $rankInfo = $this->getUserRankInfo($child);
            $rankLevel = (int) ($rankInfo['level'] ?? 1);
            $childrenCount = (int) ($child->children_count ?? 0);
            $result[] = [
                'id' => $child->id,
                'name' => $child->name,
                'email' => $child->email,
                'avatar' => strtoupper(substr($child->name, 0, 2)),
                'is_active' => $child->is_active,
                'rank' => $rankInfo,
                'rank_image' => \App\Support\MlmRank::imageUrl($rankLevel),
                'avatar_color' => $this->getAvatarColor($child),
                'pv_balance' => $child->pv_balance ?? 0,
                'has_children' => $childrenCount > 0,
                'children_count' => $childrenCount,
                'created_at' => $child->created_at->format('d/m/Y'),
                'sponsor_id' => $child->sponsor_id,
                'level' => $childLevel,
            ];
        }
        
        return response()->json([
            'success' => true,
            'children' => $result,
            'has_children' => count($result) > 0,
            'count' => count($result)
        ]);
    }

    /**
     * Recuperer les donnees de l'arbre en AJAX avec profondeur
     */
public function treeData()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // Formater le noeud racine (l'utilisateur connecté)
        $data = $this->formatUserForOrgChart($user);

        // Charger les filleuls directs (niveau 1)
        // On n'envoie PAS les relations en récursivité pour l'OrgChart,
        // c'est le lazy loading qui s'en occupera.
        $children = User::where('parrain_id', $user->id)
            ->with(['rank', 'package'])
            ->get();

        // Formater et ajouter les enfants
        foreach ($children as $child) {
            $childData = $this->formatUserForOrgChart($child);
            
            // OrgChart a besoin de savoir si le noeud a des enfants pour afficher le bouton "+"
            // On fait une requête légère pour vérifier l'existence plutôt que de tout charger.
            $hasChildren = User::where('parrain_id', $child->id)->exists();
            $childData['className'] = $hasChildren ? 'has-children' : 'no-children';
            
            // Ajout obligatoire d'un tableau children vide pour indiquer la possibilité de lazy loading
            $childData['children'] = [];

            $data['children'][] = $childData;
        }

        return response()->json($data);
    }

    // ============================================================
    // METHODES PRIVEES
    // ============================================================

    private function buildTree($user, $level = 0, $maxLevel = 1)
    {
        if ($level > $maxLevel || !$user) {
            return null;
        }

        $downlines = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->withCount('filleuls as children_count')
            ->get();

        $children = [];

        foreach ($downlines as $child) {
            $child->level = $level + 1;
            $childrenCount = (int) ($child->children_count ?? 0);

            $childTree = null;
            if ($level + 1 < $maxLevel) {
                $childTree = $this->buildTree($child, $level + 1, $maxLevel);
            }

            $children[] = [
                'user' => $child,
                'level' => $level + 1,
                'children' => $childTree ? $childTree['children'] : [],
                'has_children' => $childrenCount > 0,
                'children_count' => $childrenCount,
            ];
        }

        return [
            'user' => $user,
            'level' => $level,
            'children' => $children,
            'has_children' => count($children) > 0,
            'children_count' => count($children),
        ];
    }

    /**
     * Stats réseau via CTE (évite de charger 2000+ User en mémoire).
     *
     * @return array<string, int|float>
     */
    private function networkStatsFast(User $user): array
    {
        return Cache::remember('network_stats_'.$user->id, 300, function () use ($user) {
            $row = DB::selectOne(
                'WITH RECURSIVE team AS (
                    SELECT id, is_active, 1 AS lvl
                    FROM users
                    WHERE parrain_id = ?
                    UNION ALL
                    SELECT u.id, u.is_active, t.lvl + 1
                    FROM users u
                    INNER JOIN team t ON u.parrain_id = t.id
                    WHERE t.lvl < 20
                )
                SELECT
                    COUNT(*) AS total,
                    SUM(lvl = 1) AS level_1,
                    SUM(lvl = 2) AS level_2,
                    SUM(lvl = 3) AS level_3,
                    SUM(lvl = 4) AS level_4,
                    SUM(lvl = 5) AS level_5,
                    SUM(lvl = 6) AS level_6,
                    SUM(lvl = 7) AS level_7,
                    SUM(lvl = 8) AS level_8,
                    SUM(lvl = 9) AS level_9,
                    SUM(lvl = 10) AS level_10,
                    SUM(lvl = 1 AND is_active = 1) AS active
                FROM team',
                [(int) $user->id]
            );

            return [
                'total' => (int) ($row->total ?? 0),
                'level_1' => (int) ($row->level_1 ?? 0),
                'level_2' => (int) ($row->level_2 ?? 0),
                'level_3' => (int) ($row->level_3 ?? 0),
                'level_4' => (int) ($row->level_4 ?? 0),
                'level_5' => (int) ($row->level_5 ?? 0),
                'level_6' => (int) ($row->level_6 ?? 0),
                'level_7' => (int) ($row->level_7 ?? 0),
                'level_8' => (int) ($row->level_8 ?? 0),
                'level_9' => (int) ($row->level_9 ?? 0),
                'level_10' => (int) ($row->level_10 ?? 0),
                'active' => (int) ($row->active ?? 0),
                'total_pv' => $user->team_pv ?? 0,
            ];
        });
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
        if ($parrainId == $userId) {
            return 0;
        }

        $row = DB::selectOne(
            'WITH RECURSIVE up AS (
                SELECT id, parrain_id, 0 AS depth FROM users WHERE id = ?
                UNION ALL
                SELECT u.id, u.parrain_id, up.depth + 1
                FROM users u
                INNER JOIN up ON u.id = up.parrain_id
                WHERE up.parrain_id IS NOT NULL AND up.depth < 100
            )
            SELECT depth FROM up WHERE id = ? LIMIT 1',
            [(int) $userId, (int) $parrainId]
        );

        if ($row) {
            return (int) $row->depth;
        }

        return 1;
    }


    private function isInNetwork($parrainId, $userId)
    {
        if ($parrainId == $userId) {
            return true;
        }

        $row = DB::selectOne(
            'WITH RECURSIVE up AS (
                SELECT id, parrain_id, 0 AS depth FROM users WHERE id = ?
                UNION ALL
                SELECT u.id, u.parrain_id, up.depth + 1
                FROM users u
                INNER JOIN up ON u.id = up.parrain_id
                WHERE up.parrain_id IS NOT NULL AND up.depth < 100
            )
            SELECT 1 AS ok FROM up WHERE id = ? LIMIT 1',
            [(int) $userId, (int) $parrainId]
        );

        return $row !== null;
    }
    // ============================================================
    // METHODES D'AFFICHAGE
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
                'Manager Senior' => 5, 'Senior Manager' => 5, 'Directeur Envolee' => 6, 'Soaring Manager' => 6,
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
        if ($level == 1) return 'avatar-rank-1';
        if ($level == 2) return 'avatar-rank-2';
        if ($level == 3) return 'avatar-rank-3';
        if ($level == 4) return 'avatar-rank-4';
        if ($level == 5) return 'avatar-rank-5';
        if ($level == 6) return 'avatar-rank-6';
        if ($level == 7) return 'avatar-rank-7';
        if ($level == 8) return 'avatar-rank-8';
        if ($level >= 9) return 'avatar-rank-9';
        return 'avatar-rank-1';
    }

    public function countNodes($tree)
    {
        if (!$tree || !isset($tree['children']) || !is_array($tree['children'])) return 0;
        $count = 1;
        foreach ($tree['children'] as $child) {
            $count += $this->countNodes($child);
        }
        return $count;
    }

    // ============================================================
    // METHODE DE RENDU DE L'ARBRE AVEC INDICATEUR D'EXPANSION
    // ============================================================
    
    public function renderNetworkNodeCard(User $user, int $level, bool $isRoot, self $controller): string
    {
        $rankInfo = $controller->getUserRankInfo($user);
        $rankName = e($rankInfo['name'] ?? 'Distributeur');
        $rankLevel = (int) ($rankInfo['level'] ?? 1);
        $statusClass = $user->is_active ? 'is-active' : 'is-inactive';
        $rankImageUrl = \App\Support\MlmRank::imageUrl($rankLevel);

        $attrs = $isRoot
            ? ''
            : ' role="button" tabindex="0" onclick="expandNode(' . (int) $user->id . ', this)"';

        $subtitle = $isRoot
            ? 'Compte racine'
            : 'Niv. ' . $level . ' — ' . $rankName;

        $mark = $rankImageUrl
            ? '<img class="network-node-insignia" src="' . e($rankImageUrl) . '" alt="' . $rankName . '" width="48" height="56" loading="lazy" decoding="async">'
            : '';

        return '<div class="network-node-card' . ($isRoot ? ' is-root' : '') . '"' . $attrs . '>'
            . '<div class="network-node-mark" role="button" tabindex="0" onclick="event.stopPropagation(); toggleNetworkBranch(this, event)" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();event.stopPropagation();toggleNetworkBranch(this,event);}">'
            . $mark
            . '<span class="network-node-status ' . $statusClass . '" aria-hidden="true"></span></div>'
            . '<div class="network-node-body">'
            . '<p class="network-node-name" title="' . e($user->name) . '">' . e($user->name) . '</p>'
            . '<p class="network-node-sub">' . $subtitle . '</p>'
            . '</div></div>';
    }

    public function renderGenealogyTree($node, $controller)
    {
        if (!$node || !isset($node['user'])) {
            return '';
        }

        $user = $node['user'];
        $level = (int) ($node['level'] ?? 0);
        $children = $node['children'] ?? [];
        $hasChildren = $node['has_children'] ?? false;
        $childrenCount = (int) ($node['children_count'] ?? 0);
        if ($childrenCount === 0 && ! empty($children) && is_array($children)) {
            $childrenCount = count($children);
        }
        $isRoot = ($level === 0);

        $html = '<li class="network-branch" data-user-id="' . $user->id . '" data-level="' . $level . '" data-children-count="' . $childrenCount . '">';
        $html .= $controller->renderNetworkNodeCard($user, $level, $isRoot, $controller);

        if (!empty($children) && is_array($children)) {
            $html .= '<ul class="network-children-row">';
            foreach ($children as $child) {
                $html .= $controller->renderGenealogyTree($child, $controller);
            }
            $html .= '</ul>';
        } elseif ($hasChildren && $childrenCount > 0) {
            $html .= '<div class="network-tree-expand">';
            $html .= '<button type="button" class="network-tree-expand-btn" onclick="event.stopPropagation(); loadChildren(' . $user->id . ', this)">';
            $html .= 'Développer · ' . $childrenCount . ' filleul' . ($childrenCount > 1 ? 's' : '');
            $html .= '</button></div>';
        }

        $html .= '</li>';

        return $html;
    }

    // ============================================================
    // AUTRES METHODES REQUISES PAR LA VUE
    // ============================================================

    public function show($id)
    {
        $member = User::with(['package', 'rank', 'genealogy'])->findOrFail($id);
        $currentUser = Auth::user();
        
        $isInNetwork = $this->isInNetwork($currentUser->id, $member->id);
        
        if (!$isInNetwork && $currentUser->id != $member->id) {
            abort(403, 'Vous n\'avez pas acces a ce profil.');
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

    public function downlines()
    {
        $user = Auth::user();
        $all = collect($this->getAllDescendantsWithLevel($user->id, 1, 999));
        $perPage = 20;
        $page = max(1, (int) request()->query('page', 1));

        $downlines = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('network.downlines', compact('downlines'))->with('controller', $this);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $user = Auth::user();
        
        $results = User::where('parrain_id', $user->id)
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->with(['package', 'rank', 'genealogy'])
            ->limit(20)
            ->get();
            
        return response()->json($results);
    }

    public function apiStats()
    {
        $user = Auth::user();

        return response()->json($this->networkStatsFast($user));
    }

    public function getParentNodeChildren($id)
    {
        $currentUser = Auth::user();

        // 1. SÉCURITÉ : Vérifier que l'id demandé appartient bien au réseau de l'utilisateur connecté.
        // (En MLM, un membre ne doit pas pouvoir espionner l'arbre de quelqu'un d'autre).
        // Si tu as déjà implémenté une méthode isInNetwork(), utilise-la ici.
        // Sinon, cette vérification est cruciale.
        if ($currentUser->id != $id && !$this->isInNetwork($currentUser->id, $id)) {
            return response()->json(['error' => 'Accès non autorisé au réseau de ce membre'], 403);
        }

        // 2. Charger les enfants du parent demandé
        $children = User::where('parrain_id', $id)
            ->with(['rank', 'package'])
            ->get();

        // 3. Formater le résultat
        $result = [];
        foreach ($children as $child) {
            $childData = $this->formatUserForOrgChart($child);
            
            // Vérifier s'il y a une profondeur supplémentaire
            $hasChildren = User::where('parrain_id', $child->id)->exists();
            $childData['className'] = $hasChildren ? 'has-children' : 'no-children';
            $childData['children'] = []; // Obligatoire pour le lazy loading

            $result[] = $childData;
        }

        // OrgChart attend directement le tableau d'objets enfants, pas un objet formaté.
        return response()->json($result);
    }

    private function formatUserForOrgChart(User $user)
    {
        // Utilisation de tes méthodes existantes pour garantir la cohérence des infos
        $rankInfo = $this->getUserRankInfo($user);
        $rankName = $rankInfo['name'] ?? 'Distributeur';
        $rankLevel = $rankInfo['level'] ?? 1;

        $packageName = 'Aucun package';
        if ($user->relationLoaded('package') && $user->package) {
            $packageName = $user->package->name;
        } elseif ($user->package_id) {
            // Optionnel : chargement si pas présent (pour la racine)
            $packageName = $user->package ? $user->package->name : 'Aucun package';
        }

        return [
            // Données techniques OrgChart
            'id' => $user->id,
            
            // Données pour le template JS personnalisé nodeTemplate
            'name' => e($user->name),
            'email' => e($user->email),
            'sponsor_id' => $user->sponsor_id,
            'avatar_text' => strtoupper(substr($user->name, 0, 2)),
            
            // Utilisation de ta fonction existante
            'avatar_color' => $this->getAvatarColor($user),
            
            'rank_name' => e($rankName),
            'rank_level' => $rankLevel,
            'package_name' => e($packageName),
            'is_active' => $user->is_active,
            'pv_balance' => number_format($user->pv_balance ?? 0),
            'team_pv' => number_format($user->team_pv ?? 0),
            'total_earnings' => number_format($user->total_earnings ?? 0, 2),
            
            // Initialisation pour le lazy loading (sera remplacé par la librairie)
            'children' => []
        ];
    }

}