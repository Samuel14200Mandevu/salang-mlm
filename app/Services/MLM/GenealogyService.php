<?php
// app/Services/MLM/GenealogyService.php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Genealogy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenealogyService
{
    /**
     * Créer l'arbre généalogique pour un nouvel utilisateur
     */
    public function createGenealogy(User $user, User $sponsor): Genealogy
    {
        $level = ($sponsor->genealogy?->level ?? 0) + 1;

        return Genealogy::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'parent_id' => $sponsor->id,
            'level' => $level,
            'position' => $this->getNextPosition($sponsor),
            'left_count' => 0,
            'right_count' => 0,
            'total_children' => 0,
        ]);
    }

    /**
     * Obtenir la prochaine position disponible (gauche ou droite)
     */
    protected function getNextPosition(User $sponsor): string
    {
        $genealogy = $sponsor->genealogy;

        if (!$genealogy) {
            return 'left';
        }

        $leftCount = $genealogy->left_count ?? 0;
        $rightCount = $genealogy->right_count ?? 0;

        if ($leftCount <= $rightCount) {
            return 'left';
        }

        return 'right';
    }

    /**
     * Placer un utilisateur dans l'arbre
     */
    public function placeUser(User $user, User $sponsor): bool
    {
        try {
            DB::beginTransaction();

            // Trouver la position libre
            $position = $this->findFreePosition($sponsor);

            if (!$position) {
                Log::warning('No free position found', [
                    'sponsor_id' => $sponsor->id,
                    'user_id' => $user->id,
                ]);
                DB::rollBack();
                return false;
            }

            // Créer la généalogie
            $genealogy = $this->createGenealogyAtPosition($user, $sponsor, $position);

            // Mettre à jour les compteurs du sponsor
            $this->updateCounters($sponsor, $position);

            DB::commit();

            Log::info('User placed in genealogy', [
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'position' => $position,
                'level' => $genealogy->level,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error placing user in genealogy', [
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Créer la généalogie à une position spécifique
     */
    protected function createGenealogyAtPosition(User $user, User $sponsor, string $position): Genealogy
    {
        $level = ($sponsor->genealogy?->level ?? 0) + 1;

        return Genealogy::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'parent_id' => $sponsor->id,
            'level' => $level,
            'position' => $position,
            'left_count' => 0,
            'right_count' => 0,
            'total_children' => 0,
        ]);
    }

    /**
     * Trouver une position libre dans l'arbre
     */
    public function findFreePosition(User $sponsor): ?string
    {
        $genealogy = $sponsor->genealogy;

        if (!$genealogy) {
            return 'left';
        }

        // Vérifier si une position directe est disponible
        $leftChild = Genealogy::where('parent_id', $sponsor->id)
            ->where('position', 'left')
            ->first();

        $rightChild = Genealogy::where('parent_id', $sponsor->id)
            ->where('position', 'right')
            ->first();

        if (!$leftChild) {
            return 'left';
        }

        if (!$rightChild) {
            return 'right';
        }

        // Rechercher la position libre la plus proche (BFS)
        return $this->findFreePositionBFS($sponsor);
    }

    /**
     * Rechercher une position libre en BFS (parcours en largeur)
     */
    protected function findFreePositionBFS(User $root): ?string
    {
        $queue = [$root];
        $visited = [];

        while (!empty($queue)) {
            $current = array_shift($queue);

            if (in_array($current->id, $visited)) {
                continue;
            }

            $visited[] = $current->id;

            $leftChild = Genealogy::where('parent_id', $current->id)
                ->where('position', 'left')
                ->first();

            $rightChild = Genealogy::where('parent_id', $current->id)
                ->where('position', 'right')
                ->first();

            if (!$leftChild) {
                return 'left';
            }

            if (!$rightChild) {
                return 'right';
            }

            // Ajouter les enfants à la queue
            $children = Genealogy::where('parent_id', $current->id)->get();
            foreach ($children as $child) {
                $childUser = User::find($child->user_id);
                if ($childUser) {
                    $queue[] = $childUser;
                }
            }
        }

        return null;
    }

    /**
     * Mettre à jour les compteurs d'un sponsor
     */
    protected function updateCounters(User $sponsor, string $position): void
    {
        $genealogy = $sponsor->genealogy;

        if (!$genealogy) {
            return;
        }

        if ($position === 'left') {
            $genealogy->left_count += 1;
        } else {
            $genealogy->right_count += 1;
        }

        $genealogy->total_children += 1;
        $genealogy->save();

        // Mettre à jour récursivement les ancêtres
        $this->updateAncestors($sponsor);
    }

    /**
     * Mettre à jour les ancêtres récursivement
     */
    protected function updateAncestors(User $user): void
    {
        $current = $user;

        while ($current->parrain_id) {
            $parent = User::find($current->parrain_id);

            if (!$parent) {
                break;
            }

            $parentGenealogy = $parent->genealogy;

            if ($parentGenealogy) {
                $parentGenealogy->total_children += 1;
                $parentGenealogy->save();
            }

            $current = $parent;
        }
    }

    /**
     * Obtenir l'arbre généalogique complet d'un utilisateur
     */
    public function getFullTree(User $user, int $maxDepth = 5): array
    {
        $tree = [
            'user' => $this->formatUser($user),
            'children' => $this->getChildrenTree($user, 1, $maxDepth),
        ];

        return $tree;
    }

    /**
     * Obtenir l'arbre des enfants récursivement
     */
    protected function getChildrenTree(User $parent, int $currentDepth, int $maxDepth): array
    {
        if ($currentDepth > $maxDepth) {
            return [];
        }

        $children = Genealogy::where('parent_id', $parent->id)
            ->orderBy('position', 'asc')
            ->get();

        $tree = [];

        foreach ($children as $childGenealogy) {
            $child = User::find($childGenealogy->user_id);

            if ($child) {
                $tree[] = [
                    'user' => $this->formatUser($child),
                    'position' => $childGenealogy->position,
                    'level' => $childGenealogy->level,
                    'children' => $this->getChildrenTree($child, $currentDepth + 1, $maxDepth),
                ];
            }
        }

        return $tree;
    }

    /**
     * Formater un utilisateur pour l'arbre
     */
    protected function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rank' => $user->rank_name,
            'rank_level' => $user->rank_level,
            'pv' => $user->pv_balance,
            'sponsor_id' => $user->sponsor_id,
            'avatar' => $user->avatar,
            'is_active' => $user->is_active,
            'joined_at' => $user->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Compter les descendants d'un utilisateur
     */
    public function countDescendants(User $user): int
    {
        $genealogy = $user->genealogy;

        if (!$genealogy) {
            return 0;
        }

        return $genealogy->total_children ?? 0;
    }

    /**
     * Compter les descendants actifs d'un utilisateur
     */
    public function countActiveDescendants(User $user): int
    {
        $children = Genealogy::where('parent_id', $user->id)->get();

        $count = 0;

        foreach ($children as $childGenealogy) {
            $child = User::find($childGenealogy->user_id);

            if ($child && $child->is_active) {
                $count++;
                $count += $this->countActiveDescendants($child);
            }
        }

        return $count;
    }

    /**
     * Obtenir les descendants à un niveau spécifique
     */
    public function getDescendantsAtLevel(User $user, int $level): array
    {
        $descendants = [];

        $this->collectDescendantsAtLevel($user, $level, 1, $descendants);

        return $descendants;
    }

    /**
     * Collecter les descendants à un niveau spécifique (récursif)
     */
    protected function collectDescendantsAtLevel(User $user, int $targetLevel, int $currentLevel, array &$descendants): void
    {
        if ($currentLevel > $targetLevel) {
            return;
        }

        $children = Genealogy::where('parent_id', $user->id)->get();

        foreach ($children as $childGenealogy) {
            $child = User::find($childGenealogy->user_id);

            if (!$child) {
                continue;
            }

            if ($currentLevel === $targetLevel) {
                $descendants[] = $this->formatUser($child);
            } else {
                $this->collectDescendantsAtLevel($child, $targetLevel, $currentLevel + 1, $descendants);
            }
        }
    }

    /**
     * Obtenir les statistiques de l'arbre
     */
    public function getTreeStats(User $user): array
    {
        $genealogy = $user->genealogy;

        if (!$genealogy) {
            return [
                'total_children' => 0,
                'left_count' => 0,
                'right_count' => 0,
                'total_descendants' => 0,
                'active_descendants' => 0,
                'max_depth' => 0,
            ];
        }

        return [
            'total_children' => $genealogy->total_children ?? 0,
            'left_count' => $genealogy->left_count ?? 0,
            'right_count' => $genealogy->right_count ?? 0,
            'total_descendants' => $this->countDescendants($user),
            'active_descendants' => $this->countActiveDescendants($user),
            'max_depth' => $this->getMaxDepth($user),
        ];
    }

    /**
     * Obtenir la profondeur maximale de l'arbre
     */
    public function getMaxDepth(User $user): int
    {
        $maxDepth = 0;

        $this->calculateMaxDepth($user, 1, $maxDepth);

        return $maxDepth;
    }

    /**
     * Calculer la profondeur maximale (récursif)
     */
    protected function calculateMaxDepth(User $user, int $currentDepth, int &$maxDepth): void
    {
        if ($currentDepth > $maxDepth) {
            $maxDepth = $currentDepth;
        }

        $children = Genealogy::where('parent_id', $user->id)->get();

        foreach ($children as $childGenealogy) {
            $child = User::find($childGenealogy->user_id);

            if ($child) {
                $this->calculateMaxDepth($child, $currentDepth + 1, $maxDepth);
            }
        }
    }

    /**
     * Vérifier si l'arbre est équilibré
     */
    public function isBalanced(User $user): bool
    {
        $genealogy = $user->genealogy;

        if (!$genealogy) {
            return true;
        }

        $leftCount = $genealogy->left_count ?? 0;
        $rightCount = $genealogy->right_count ?? 0;

        // Un arbre est équilibré si la différence est ≤ 1
        return abs($leftCount - $rightCount) <= 1;
    }

    /**
     * Obtenir le ratio gauche/droite
     */
    public function getLeftRightRatio(User $user): float
    {
        $genealogy = $user->genealogy;

        if (!$genealogy) {
            return 0;
        }

        $leftCount = $genealogy->left_count ?? 0;
        $rightCount = $genealogy->right_count ?? 0;

        if ($rightCount === 0) {
            return $leftCount > 0 ? 100 : 0;
        }

        return ($leftCount / $rightCount) * 100;
    }
}