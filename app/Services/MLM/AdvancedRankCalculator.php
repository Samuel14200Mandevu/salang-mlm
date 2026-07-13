<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\QualifiedBranch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdvancedRankCalculator
{
    /**
     * Calcule le grade avancé de l'utilisateur
     */
    public function calculateAdvancedRank(User $user): ?Rank
    {
        if (!$user->is_active) {
            return null;
        }

        $ranks = Rank::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get();

        $highestRank = null;

        foreach ($ranks as $rank) {
            if ($this->isEligibleForRank($user, $rank)) {
                $highestRank = $rank;
            }
        }

        return $highestRank;
    }

    /**
     * Vérifie si l'utilisateur est éligible pour un grade spécifique
     */
    public function isEligibleForRank(User $user, Rank $rank): bool
    {
        // Vérification du PV et BV minimum
        if ($user->pv_balance < ($rank->pv_required ?? 0)) {
            return false;
        }

        if ($user->bv_balance < ($rank->bv_required ?? 0)) {
            return false;
        }

        // Vérification du PV mensuel requis pour toucher les commissions
        $monthlyPv = $this->getMonthlyPV($user);
        if ($monthlyPv < ($rank->pv_payment_required ?? 0)) {
            return false;
        }

        // Vérification des conditions spécifiques selon le niveau
        $conditions = $this->getRankConditions($rank);
        if (!$this->checkConditions($user, $rank->level, $conditions)) {
            return false;
        }

        return true;
    }

    /**
     * Récupère les conditions du grade depuis la base de données ou les données par défaut
     */
    private function getRankConditions(Rank $rank): array
    {
        if ($rank->conditions) {
            if (is_string($rank->conditions)) {
                return json_decode($rank->conditions, true) ?? [];
            }
            return $rank->conditions ?? [];
        }

        // Conditions par défaut pour chaque niveau
        return $this->getDefaultConditions($rank->level);
    }

    /**
     * Conditions par défaut pour chaque niveau
     */
    private function getDefaultConditions(int $level): array
    {
        $conditions = [
            1 => [
                ['label' => 'Inscription', 'value' => 'Validée', 'type' => 'simple']
            ],
            2 => [
                ['label' => 'PV Personnel', 'value' => '≥ 100 PV', 'type' => 'simple'],
                ['label' => 'PV Mensuel', 'value' => '≥ 20 PV', 'type' => 'monthly_pv']
            ],
            3 => [
                ['label' => 'PV Personnel', 'value' => '≥ 200 PV', 'type' => 'simple'],
                ['label' => 'PV Mensuel', 'value' => '≥ 20 PV', 'type' => 'monthly_pv']
            ],
            4 => [
                ['label' => 'Être niveau 4', 'value' => 'Avoir ≥ 1000 PV personnel', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 3 filleuls directs de niveau 4 avec ≥ 1000 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 3 avec un total ≥ 2200 PV', 'type' => 'option', 'option' => 2]
            ],
            5 => [
                ['label' => 'Être niveau 5', 'value' => 'Avoir 3 filleuls directs de niveau 4 avec ≥ 3800 PV', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 4 avec ≥ 7800 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 4 et 4 filleuls de niveau 3 avec ≥ 3800 PV', 'type' => 'option', 'option' => 2],
                ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 4 et 6 filleuls de niveau 3 avec ≥ 3800 PV', 'type' => 'option', 'option' => 3]
            ],
            6 => [
                ['label' => 'Être niveau 6', 'value' => 'Avoir 3 filleuls directs de niveau 5 avec ≥ 16000 PV', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 5 avec ≥ 35000 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 5 et 4 filleuls de niveau 4 avec ≥ 16000 PV', 'type' => 'option', 'option' => 2],
                ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 5 et 6 filleuls de niveau 4 avec ≥ 16000 PV', 'type' => 'option', 'option' => 3]
            ],
            7 => [
                ['label' => 'Être niveau 7', 'value' => 'Avoir 3 filleuls directs de niveau 6 avec ≥ 73000 PV', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 6 avec ≥ 145000 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 6 et 4 filleuls de niveau 5 avec ≥ 73000 PV', 'type' => 'option', 'option' => 2],
                ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 6 et 6 filleuls de niveau 5 avec ≥ 73000 PV', 'type' => 'option', 'option' => 3]
            ],
            8 => [
                ['label' => 'Être niveau 8', 'value' => 'Avoir 3 filleuls directs de niveau 7 avec ≥ 280000 PV', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 7 avec ≥ 580000 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 7 et 4 filleuls de niveau 6 avec ≥ 280000 PV', 'type' => 'option', 'option' => 2],
                ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 7 et 6 filleuls de niveau 6 avec ≥ 280000 PV', 'type' => 'option', 'option' => 3]
            ],
            9 => [
                ['label' => 'Être niveau 9', 'value' => 'Avoir 3 filleuls directs de niveau 8 avec ≥ 400000 PV', 'type' => 'simple'],
                ['label' => 'Option 1', 'value' => 'Avoir 2 filleuls de niveau 8 avec ≥ 780000 PV', 'type' => 'option', 'option' => 1],
                ['label' => 'Option 2', 'value' => 'Avoir 2 filleuls de niveau 8 et 4 filleuls de niveau 7 avec ≥ 400000 PV', 'type' => 'option', 'option' => 2],
                ['label' => 'Option 3', 'value' => 'Avoir 1 filleul de niveau 8 et 6 filleuls de niveau 7 avec ≥ 400000 PV', 'type' => 'option', 'option' => 3]
            ],
            10 => [
                ['label' => 'Être niveau 10', 'value' => 'Avoir les conditions requises pour Pearl', 'type' => 'simple']
            ]
        ];

        return $conditions[$level] ?? [];
    }

    /**
     * Vérifie toutes les conditions pour un grade
     */
    private function checkConditions(User $user, int $level, array $conditions): bool
    {
        // Séparer les conditions principales des options
        $mainConditions = [];
        $options = [];

        foreach ($conditions as $condition) {
            if (isset($condition['type']) && $condition['type'] === 'option') {
                $options[] = $condition;
            } else {
                $mainConditions[] = $condition;
            }
        }

        // Vérifier toutes les conditions principales
        foreach ($mainConditions as $condition) {
            if (!$this->checkCondition($user, $level, $condition)) {
                return false;
            }
        }

        // Si pas d'options, c'est validé
        if (empty($options)) {
            return true;
        }

        // Vérifier qu'au moins une option est validée
        foreach ($options as $option) {
            if ($this->checkCondition($user, $level, $option)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie une condition spécifique
     */
    private function checkCondition(User $user, int $level, array $condition): bool
    {
        $type = $condition['type'] ?? 'simple';
        $label = $condition['label'] ?? 'Condition';
        $value = $condition['value'] ?? '';

        switch ($type) {
            case 'simple':
                return $this->checkSimpleCondition($user, $level, $value);
            
            case 'monthly_pv':
                return $this->checkMonthlyPVCondition($user, $value);
            
            case 'option':
                return $this->checkOptionCondition($user, $level, $value, $condition['option'] ?? 1);
            
            default:
                return true;
        }
    }

    /**
     * Vérifie une condition simple
     */
    private function checkSimpleCondition(User $user, int $level, string $value): bool
    {
        // Extraire le nombre de PV de la condition
        if (preg_match('/(\d+)\s*PV/', $value, $matches)) {
            $requiredPV = (int) $matches[1];
            return ($user->pv_balance ?? 0) >= $requiredPV;
        }

        // Vérifier le nombre de filleuls
        if (preg_match('/(\d+)\s*filleuls?/', $value, $matches)) {
            $requiredCount = (int) $matches[1];
            $downlines = $this->getDirectDownlines($user);
            return count($downlines) >= $requiredCount;
        }

        // Vérifier le niveau des filleuls
        if (preg_match('/niveau\s*(\d+)/i', $value, $matches)) {
            $requiredLevel = (int) $matches[1];
            $downlines = $this->getDirectDownlines($user);
            
            // Compter les filleuls directs avec ce niveau
            $count = 0;
            foreach ($downlines as $downline) {
                if (($downline->rank?->level ?? 0) >= $requiredLevel) {
                    $count++;
                }
            }
            
            // Extraire le nombre requis
            if (preg_match('/(\d+)\s*filleuls?/', $value, $countMatches)) {
                $requiredCount = (int) $countMatches[1];
                return $count >= $requiredCount;
            }
            
            return $count > 0;
        }

        // Si on ne peut pas analyser la condition, on la considère comme validée
        return true;
    }

    /**
     * Vérifie la condition de PV mensuel
     */
    private function checkMonthlyPVCondition(User $user, string $value): bool
    {
        if (preg_match('/(\d+)\s*PV/', $value, $matches)) {
            $requiredPV = (int) $matches[1];
            $monthlyPV = $this->getMonthlyPV($user);
            return $monthlyPV >= $requiredPV;
        }
        return true;
    }

    /**
     * Vérifie une condition optionnelle (Option 1, 2, 3)
     */
    private function checkOptionCondition(User $user, int $level, string $value, int $optionNumber): bool
    {
        // Extraire les informations de la condition
        $result = $this->parseOptionCondition($level, $optionNumber, $value);
        
        if (!$result) {
            return false;
        }

        $downlines = $this->getDirectDownlines($user);
        
        // Vérifier les différents cas selon l'option
        switch ($optionNumber) {
            case 1: // 2 filleuls de niveau X avec Y PV
                return $this->checkOption1($user, $level, $result['target_level'], $result['required_pv']);
            
            case 2: // 2 filleuls niveau X et 4 filleuls niveau Y avec Z PV
                return $this->checkOption2($user, $level, $result['target_level'], $result['secondary_level'], $result['required_pv']);
            
            case 3: // 1 filleul niveau X et 6 filleuls niveau Y avec Z PV
                return $this->checkOption3($user, $level, $result['target_level'], $result['secondary_level'], $result['required_pv']);
            
            default:
                return false;
        }
    }

    /**
     * Parse une condition optionnelle pour extraire les informations
     */
    private function parseOptionCondition(int $level, int $optionNumber, string $value): ?array
    {
        $result = [
            'target_level' => 0,
            'secondary_level' => 0,
            'required_pv' => 0
        ];

        // Extraire le niveau cible
        if (preg_match('/niveau\s*(\d+)/i', $value, $matches)) {
            $result['target_level'] = (int) $matches[1];
        }

        // Extraire le niveau secondaire (pour options 2 et 3)
        if (preg_match('/niveau\s*(\d+).*?niveau\s*(\d+)/i', $value, $matches)) {
            $result['target_level'] = (int) $matches[1];
            $result['secondary_level'] = (int) $matches[2];
        }

        // Extraire le PV requis
        if (preg_match('/(\d+)\s*PV/', $value, $matches)) {
            $result['required_pv'] = (int) $matches[1];
        }

        // Définir les valeurs par défaut en fonction du niveau
        if ($result['target_level'] == 0) {
            $result['target_level'] = $level - 1;
        }

        if ($result['secondary_level'] == 0) {
            $result['secondary_level'] = $level - 2;
        }

        if ($result['required_pv'] == 0) {
            // Utiliser les PV requis du niveau cible
            $targetRank = Rank::where('level', $result['target_level'])->first();
            if ($targetRank) {
                $result['required_pv'] = $targetRank->pv_required ?? 0;
            }
        }

        return $result;
    }

    /**
     * Vérifie l'option 1: 2 filleuls de niveau X avec Y PV
     */
    private function checkOption1(User $user, int $level, int $targetLevel, int $requiredPV): bool
    {
        $downlines = $this->getDirectDownlines($user);
        $qualified = 0;

        foreach ($downlines as $downline) {
            if (($downline->rank?->level ?? 0) >= $targetLevel && 
                ($downline->pv_balance ?? 0) >= $requiredPV) {
                $qualified++;
            }
        }

        return $qualified >= 2;
    }

    /**
     * Vérifie l'option 2: 2 filleuls niveau X et 4 filleuls niveau Y avec Z PV
     */
    private function checkOption2(User $user, int $level, int $targetLevel, int $secondaryLevel, int $requiredPV): bool
    {
        $downlines = $this->getDirectDownlines($user);
        $qualifiedTarget = 0;
        $qualifiedSecondary = 0;

        foreach ($downlines as $downline) {
            $downlineLevel = $downline->rank?->level ?? 0;
            $downlinePV = $downline->pv_balance ?? 0;

            if ($downlineLevel >= $targetLevel && $downlinePV >= $requiredPV) {
                $qualifiedTarget++;
            }

            if ($downlineLevel >= $secondaryLevel && $downlinePV >= $requiredPV) {
                $qualifiedSecondary++;
            }
        }

        return $qualifiedTarget >= 2 && $qualifiedSecondary >= 4;
    }

    /**
     * Vérifie l'option 3: 1 filleul niveau X et 6 filleuls niveau Y avec Z PV
     */
    private function checkOption3(User $user, int $level, int $targetLevel, int $secondaryLevel, int $requiredPV): bool
    {
        $downlines = $this->getDirectDownlines($user);
        $qualifiedTarget = 0;
        $qualifiedSecondary = 0;

        foreach ($downlines as $downline) {
            $downlineLevel = $downline->rank?->level ?? 0;
            $downlinePV = $downline->pv_balance ?? 0;

            if ($downlineLevel >= $targetLevel && $downlinePV >= $requiredPV) {
                $qualifiedTarget++;
            }

            if ($downlineLevel >= $secondaryLevel && $downlinePV >= $requiredPV) {
                $qualifiedSecondary++;
            }
        }

        return $qualifiedTarget >= 1 && $qualifiedSecondary >= 6;
    }

    /**
     * Récupère le PV mensuel de l'utilisateur
     */
    public function getMonthlyPV(User $user): float
    {
        // Implémentez la logique pour récupérer le PV mensuel
        // Exemple: somme des ventes du mois en cours
        $monthlyPV = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('type', 'sale')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('pv_amount');
            
        return $monthlyPV ?? 0;
    }

    /**
     * Récupère les filleuls directs
     */
    public function getDirectDownlines(User $user): array
    {
        return User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->with('rank')
            ->get()
            ->toArray();
    }

    /**
     * Récupère tous les filleuls (tous niveaux)
     */
    public function getAllDownlines(User $user): array
    {
        $downlines = [];
        $this->getDownlinesRecursive($user, $downlines);
        return $downlines;
    }

    /**
     * Récupère récursivement tous les filleuls
     */
    private function getDownlinesRecursive(User $user, array &$downlines): void
    {
        $directs = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->with('rank')
            ->get();

        foreach ($directs as $direct) {
            $downlines[] = $direct;
            $this->getDownlinesRecursive($direct, $downlines);
        }
    }

    /**
     * Récupère le PV total du réseau
     */
    public function getTeamPV(User $user): float
    {
        $totalPV = 0;
        $downlines = $this->getAllDownlines($user);
        
        foreach ($downlines as $downline) {
            $totalPV += $downline->pv_balance ?? 0;
        }
        
        return $totalPV;
    }

    /**
     * Récupère le PV d'une branche spécifique
     */
    public function getBranchPV(User $user, User $branchUser): float
    {
        $branchPV = $branchUser->pv_balance ?? 0;
        $downlines = $this->getAllDownlines($branchUser);
        
        foreach ($downlines as $downline) {
            $branchPV += $downline->pv_balance ?? 0;
        }
        
        return $branchPV;
    }

    /**
     * Compte les branches qualifiées par niveau
     */
    public function countQualifiedBranches(User $user, int $level, int $minPV): int
    {
        $count = 0;
        $directs = $this->getDirectDownlines($user);
        
        foreach ($directs as $direct) {
            $branchPV = $this->getBranchPV($user, $direct);
            if ($branchPV >= $minPV && ($direct['rank']['level'] ?? 0) >= $level) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Vérifie si l'utilisateur a les branches requises
     */
    public function hasRequiredBranches(User $user, int $requiredBranches, int $minLevel, int $minPV): bool
    {
        $qualifiedBranches = $this->countQualifiedBranches($user, $minLevel, $minPV);
        return $qualifiedBranches >= $requiredBranches;
    }

    /**
     * Calcule la progression vers le prochain grade
     */
    public function getRankProgress(User $user): array
    {
        $currentRank = $this->calculateAdvancedRank($user);
        $currentLevel = $currentRank?->level ?? 0;
        
        $nextRank = Rank::where('level', '>', $currentLevel)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
        
        if (!$nextRank) {
            return [
                'current_rank' => $currentRank?->name ?? 'Distributeur',
                'current_level' => $currentLevel,
                'next_rank' => 'Maximum Level',
                'next_level' => $currentLevel,
                'current_pv' => $user->pv_balance ?? 0,
                'next_pv' => 0,
                'progress' => 100,
                'pv_needed' => 0
            ];
        }

        $currentPV = $user->pv_balance ?? 0;
        $nextPV = $nextRank->pv_required ?? 0;
        $progress = $nextPV > 0 ? min(100, ($currentPV / $nextPV) * 100) : 0;
        $pvNeeded = max(0, $nextPV - $currentPV);

        return [
            'current_rank' => $currentRank?->name ?? 'Distributeur',
            'current_level' => $currentLevel,
            'next_rank' => $nextRank->name,
            'next_level' => $nextRank->level,
            'current_pv' => $currentPV,
            'next_pv' => $nextPV,
            'progress' => $progress,
            'pv_needed' => $pvNeeded
        ];
    }

    /**
     * Récupère les statistiques des grades de l'utilisateur
     */
    public function getRankStats(User $user): array
    {
        $rankHistory = DB::table('rank_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPromotions = $rankHistory->where('type', 'promotion')->count();
        $currentRank = $this->calculateAdvancedRank($user);

        return [
            'total_promotions' => $totalPromotions,
            'current_rank' => $currentRank?->name ?? 'Distributeur',
            'current_level' => $currentRank?->level ?? 1,
            'history_count' => $rankHistory->count(),
            'last_promotion' => $rankHistory->where('type', 'promotion')->first()
        ];
    }

    /**
     * Récupère la distribution des grades
     */
    public function getRankDistribution(): array
    {
        $distribution = [];
        $ranks = Rank::where('is_active', true)->orderBy('level', 'asc')->get();
        
        foreach ($ranks as $rank) {
            $count = User::where('rank_id', $rank->id)
                ->where('is_active', true)
                ->count();
            $distribution[$rank->name] = $count;
        }
        
        return $distribution;
    }
}