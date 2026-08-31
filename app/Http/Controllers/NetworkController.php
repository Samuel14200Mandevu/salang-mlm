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
     * Afficher la page principale du reseau
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $parrain = User::find($user->parrain_id);
        
        // Charger UNIQUEMENT le niveau 1 initialement
        $tree = $this->buildTree($user, 0, 1);
        
        // Recuperer TOUS les descendants pour les statistiques
        $allDescendants = $this->getAllDescendantsWithLevel($user->id, 1, 999);
        
        // Statistiques du reseau
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

        // Recuperer les filleuls directs pour la vue liste
        $filleuls = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->get();

        foreach ($filleuls as $member) {
            $member->level = 1;
            $member->downline_count = User::where('parrain_id', $member->id)->count();
        }

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
            ->get();
            
        $result = [];
        foreach ($children as $child) {
            $rankInfo = $this->getUserRankInfo($child);
            $result[] = [
                'id' => $child->id,
                'name' => $child->name,
                'email' => $child->email,
                'avatar' => strtoupper(substr($child->name, 0, 2)),
                'is_active' => $child->is_active,
                'rank' => $rankInfo,
                'avatar_color' => $this->getAvatarColor($child),
                'pv_balance' => $child->pv_balance ?? 0,
                'has_children' => User::where('parrain_id', $child->id)->exists(),
                'children_count' => User::where('parrain_id', $child->id)->count(),
                'created_at' => $child->created_at->format('d/m/Y'),
                'sponsor_id' => $child->sponsor_id,
                'level' => $this->getUserLevel($currentUser->id, $child->id),
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
            ->get();
            
        $children = [];

        foreach ($downlines as $child) {
            $child->level = $level + 1;
            
            // Verifier si l'enfant a des enfants (sans les charger)
            $hasChildren = User::where('parrain_id', $child->id)->exists();
            $childrenCount = User::where('parrain_id', $child->id)->count();
            
            // Si on est au maxLevel, on ne charge pas les enfants
            $childTree = null;
            if ($level + 1 < $maxLevel) {
                $childTree = $this->buildTree($child, $level + 1, $maxLevel);
            }
            
            $children[] = [
                'user' => $child,
                'level' => $level + 1,
                'children' => $childTree ? $childTree['children'] : [],
                'has_children' => $hasChildren,
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
        
        while ($current && $current->parrain_id && $level < 999) { // Sécurité anti-boucle
            $level++;
            if ($current->parrain_id == $parrainId) return true;
            $current = User::find($current->parrain_id);
        }
        
        return false;
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
    
    public function renderGenealogyTree($node, $controller)
    {
        if (!$node || !isset($node['user'])) {
            return '';
        }

        $user = $node['user'];
        $level = $node['level'] ?? 0;
        $children = $node['children'] ?? [];
        $hasChildren = $node['has_children'] ?? false;
        $childrenCount = $node['children_count'] ?? 0;

        $rankInfo = $controller->getUserRankInfo($user);
        $rankName = $rankInfo['name'];
        $rankLevel = $rankInfo['level'];
        $avatarColor = $controller->getAvatarColor($user);

        $levelClass = 'level-' . min($level, 9);
        $isRoot = ($level == 0);

        $html = '<div class="tree-branch" data-user-id="' . $user->id . '" data-level="' . $level . '" data-has-children="' . ($hasChildren ? 'true' : 'false') . '" data-children-count="' . $childrenCount . '">';

        // Le noeud
        $html .= '<div class="tree-node ' . $levelClass . ($isRoot ? ' active' : '') . '" onclick="expandNode(' . $user->id . ', this)">';
        
        $html .= '<div class="avatar-wrapper">';
        $html .= '<div class="avatar ' . $avatarColor . '">';
        $html .= strtoupper(substr($user->name, 0, 2));
        $html .= '<span class="status-dot ' . ($user->is_active ? 'online' : 'offline') . '"></span>';
        if ($level > 0) {
            $badgeClass = 'lv-' . min($level, 10);
            $html .= '<span class="level-badge ' . $badgeClass . '">' . $level . '</span>';
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<span class="node-name">' . e($user->name) . '</span>';
        
        if ($isRoot) {
            $html .= '<span class="node-rank" style="background:rgba(90,182,56,0.12);color:#5ab638;font-weight:700;">Moi</span>';
        } else {
            $rankClass = 'rank-badge-' . min($rankLevel, 9);
            $html .= '<span class="node-rank ' . $rankClass . '">' . e($rankName) . '</span>';
        }
        
        // Afficher le PV
        if (($user->pv_balance ?? 0) > 0) {
            $html .= '<span class="node-pv">PV ' . number_format($user->pv_balance ?? 0) . '</span>';
        }
        
        // Afficher le nombre d'enfants
        if ($childrenCount > 0 && !$isRoot) {
            $html .= '<span class="node-children-count">' . $childrenCount . ' filleuls</span>';
        }
        
        $html .= '</div>';

        // Les enfants deja charges
        if (!empty($children) && is_array($children) && count($children) > 0) {
            $html .= '<div class="tree-children-container">';

            if (count($children) > 1) {
                $html .= '<div class="horizontal-branch-line"></div>';
            }

            $html .= '<div class="tree-children">';
            foreach ($children as $child) {
                $html .= $controller->renderGenealogyTree($child, $controller);
            }
            $html .= '</div>';
            $html .= '</div>';
        } 
        // Si l'utilisateur a des enfants mais qu'ils ne sont pas charges (niveau > maxDepth)
        else if ($hasChildren && $childrenCount > 0) {
            $html .= '<div class="tree-children-container">';
            $html .= '<div class="expand-indicator">';
            $html .= '<span class="expand-btn" data-user-id="' . $user->id . '" onclick="event.stopPropagation(); loadChildren(' . $user->id . ', this)">';
            $html .= '<span class="expand-icon">+</span> ' . $childrenCount . ' filleul' . ($childrenCount > 1 ? 's' : '');
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>'; // Fin tree-branch
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
        $downlines = User::where('parrain_id', $user->id)
            ->with(['package', 'rank', 'genealogy'])
            ->paginate(20);
            
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
        $allDescendants = $this->getAllDescendantsWithLevel($user->id, 1, 999);
        
        return response()->json([
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
            'total_pv' => $user->team_pv ?? 0,
        ]);
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