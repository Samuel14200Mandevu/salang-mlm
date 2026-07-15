<?php
// app/Models/Rank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'level',
        'min_pv',
        'min_bv',
        'monthly_pv_required',
        'team_pv_required',
        'min_sponsors',
        'min_team',
        'bonus_percentage',
        'pv_payment_required',
        'description',
        'conditions',
        'commission_types',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_pv' => 'integer',
        'min_bv' => 'integer',
        'monthly_pv_required' => 'integer',
        'team_pv_required' => 'integer',
        'min_sponsors' => 'integer',
        'min_team' => 'integer',
        'bonus_percentage' => 'decimal:2',
        'pv_payment_required' => 'integer',
        'level' => 'integer',
        'conditions' => 'array',
        'commission_types' => 'array',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    public function monthlyRanks()
    {
        return $this->hasMany(UserMonthlyRank::class);
    }

    // ============================================================
    // MÉTHODES DE NAVIGATION
    // ============================================================

    public function getNextRank()
    {
        return Rank::where('level', '>', $this->level)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
    }

    public function getPreviousRank()
    {
        return Rank::where('level', '<', $this->level)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->first();
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getLevelNameAttribute()
    {
        $levels = [
            1 => 'Level 1',
            2 => 'Level 2',
            3 => 'Level 3',
            4 => 'Level 4',
            5 => 'Level 5',
            6 => 'Level 6',
            7 => 'Level 7',
            8 => 'Level 8',
            9 => 'Level 9',
            10 => 'Level 10',
        ];
        return $levels[$this->level] ?? $this->name;
    }

    public function getColorAttribute()
    {
        $colors = [
            1 => 'gray',
            2 => 'gray',
            3 => 'blue',
            4 => 'blue',
            5 => 'green',
            6 => 'green',
            7 => 'purple',
            8 => 'purple',
            9 => 'gold',
            10 => 'gold',
        ];
        return $colors[$this->level] ?? 'gray';
    }

    public function getIconAttribute()
    {
        $icons = [
            1 => 'level-1',
            2 => 'level-2',
            3 => 'level-3',
            4 => 'level-4',
            5 => 'level-5',
            6 => 'level-6',
            7 => 'level-7',
            8 => 'level-8',
            9 => 'level-9',
            10 => 'level-10',
        ];
        return $icons[$this->level] ?? 'level';
    }

    public function getFormattedBonusAttribute()
    {
        return number_format($this->bonus_percentage, 2) . '%';
    }

    public function getCommissionTypesListAttribute()
    {
        if ($this->commission_types) {
            return implode(', ', $this->commission_types);
        }
        return 'Standard';
    }

    public function getConditionsListAttribute()
    {
        if ($this->conditions) {
            $list = [];
            foreach ($this->conditions as $condition) {
                $list[] = $condition['label'] ?? 'Condition';
            }
            return implode(' | ', $list);
        }
        return 'No specific conditions';
    }

    // ============================================================
    // MÉTHODES D'ÉLIGIBILITÉ (CORRIGÉES)
    // ============================================================

    /**
     * Vérifie si l'utilisateur est éligible pour ce grade
     */
    public function isEligible(User $user): bool
    {
        // Vérification du PV minimum
        if ($user->pv_balance < $this->min_pv) {
            return false;
        }

        // Vérification du BV minimum
        if ($user->bv_balance < $this->min_bv) {
            return false;
        }

        // Pour les niveaux 1 à 3, c'est simple
        if ($this->level <= 3) {
            return true;
        }

        // Pour les niveaux 4 à 9, vérifier les conditions complexes
        return $this->checkConditions($user);
    }

    /**
     * Vérifie les conditions complexes du grade
     */
    public function checkConditions(User $user): bool
    {
        $conditions = $this->conditions;
        
        if (empty($conditions) || !is_array($conditions)) {
            // Si pas de conditions, utiliser la règle par défaut
            return $user->pv_balance >= $this->min_pv;
        }

        $directChildren = $user->filleuls()->with('rank')->get();
        
        // Parcourir toutes les conditions (une suffit)
        foreach ($conditions as $condition) {
            if ($this->checkSingleCondition($condition, $directChildren, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie une condition simple
     */
    private function checkSingleCondition(array $condition, $directChildren, User $user): bool
    {
        // ============================================================
        // TYPE 1: PERSONAL PV
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'personal_pv') {
            $requiredPV = $condition['value'] ?? 0;
            $result = $user->pv_balance >= $requiredPV;
            
            Log::debug('Vérification personal_pv', [
                'user_id' => $user->id,
                'pv_balance' => $user->pv_balance,
                'required_pv' => $requiredPV,
                'result' => $result
            ]);
            
            return $result;
        }

        // ============================================================
        // TYPE 2: BRANCHES (Ex: 3 branches niveau X avec Y PV)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches') {
            $branchLevel = $condition['rank_level'] ?? 0;
            $minBranches = $condition['branches'] ?? 1;
            $minBranchPV = $condition['group_pv'] ?? 0;

            $count = $directChildren->filter(function ($child) use ($branchLevel, $minBranchPV) {
                $childRank = $this->getUserRankObject($child);
                $childRankLevel = $childRank?->level ?? 0;
                return $childRankLevel >= $branchLevel
                    && $child->pv_balance >= $minBranchPV;
            })->count();

            $result = $count >= $minBranches;
            
            Log::debug('Vérification branches', [
                'user_id' => $user->id,
                'branch_level' => $branchLevel,
                'min_branches' => $minBranches,
                'min_branch_pv' => $minBranchPV,
                'count' => $count,
                'result' => $result
            ]);

            return $result;
        }

        // ============================================================
        // TYPE 3: BRANCHES MIXED (Ex: 2 branches X + 4 branches Y)
        // ============================================================
        if (isset($condition['type']) && $condition['type'] === 'branches_mixed') {
            $branchRequirements = $condition['branches'] ?? [];
            $minGroupPV = $condition['group_pv'] ?? 0;

            foreach ($branchRequirements as $count => $level) {
                $actualCount = $directChildren->filter(function ($child) use ($level) {
                    $childRank = $this->getUserRankObject($child);
                    return ($childRank?->level ?? 0) >= $level;
                })->count();

                if ($actualCount < $count) {
                    return false;
                }
            }

            // Calculer le PV total des branches
            $totalGroupPV = $directChildren->sum('pv_balance');
            $result = $totalGroupPV >= $minGroupPV;
            
            Log::debug('Vérification branches_mixed', [
                'user_id' => $user->id,
                'requirements' => $branchRequirements,
                'min_group_pv' => $minGroupPV,
                'total_group_pv' => $totalGroupPV,
                'result' => $result
            ]);

            return $result;
        }

        // ============================================================
        // RÉTROCOMPATIBILITÉ (ancien format)
        // ============================================================
        if (isset($condition['branch_level'])) {
            $count = $directChildren->filter(function ($child) use ($condition) {
                $childRank = $this->getUserRankObject($child);
                $rankLevel = $childRank?->level ?? 0;
                return $rankLevel >= ($condition['branch_level'] ?? 0)
                    && $child->pv_balance >= ($condition['min_branch_pv'] ?? 0);
            })->count();

            return $count >= ($condition['min_branches'] ?? 1);
        }

        if (isset($condition['branches']) && is_array($condition['branches'])) {
            foreach ($condition['branches'] as $branchCondition) {
                $count = $directChildren->filter(function ($child) use ($branchCondition) {
                    $childRank = $this->getUserRankObject($child);
                    $rankLevel = $childRank?->level ?? 0;
                    return $rankLevel >= ($branchCondition['branch_level'] ?? 0)
                        && $child->pv_balance >= ($branchCondition['min_branch_pv'] ?? 0);
                })->count();

                if ($count < ($branchCondition['min_branches'] ?? 1)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Obtient l'objet Rank d'un utilisateur
     */
    private function getUserRankObject(User $user): ?Rank
    {
        if ($user->relationLoaded('rank') && $user->rank && !is_string($user->rank)) {
            return $user->rank;
        }

        if ($user->rank_id) {
            return Rank::find($user->rank_id);
        }

        if (is_string($user->rank)) {
            $rank = Rank::where('name', $user->rank)->first();
            if ($rank) {
                return $rank;
            }
            $rank = Rank::where('slug', $user->rank)->first();
            if ($rank) {
                return $rank;
            }
        }

        return Rank::where('level', 1)->first();
    }

    /**
     * Calcule le PV total d'une branche (chef + descendants)
     */
    private function getBranchTotalPV(User $branchRoot): int
    {
        $totalPV = $branchRoot->pv_balance;
        
        $descendants = $this->getAllDescendants($branchRoot);
        foreach ($descendants as $descendant) {
            $totalPV += $descendant->pv_balance;
        }
        
        return $totalPV;
    }

    /**
     * Récupère tous les descendants d'un utilisateur
     */
    private function getAllDescendants(User $user): array
    {
        $descendants = [];
        $this->getDescendantsRecursive($user, $descendants);
        return $descendants;
    }

    /**
     * Récupère récursivement les descendants
     */
    private function getDescendantsRecursive($user, array &$descendants): void
    {
        $children = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($children as $child) {
            $descendants[] = $child;
            $this->getDescendantsRecursive($child, $descendants);
        }
    }
}