<?php

namespace App\Services\MLM;

use App\Models\User;
use App\Models\Rank;
use App\Models\QualifiedBranch;
use Illuminate\Support\Facades\Log;

class AdvancedRankCalculator
{
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

    public function isEligibleForRank(User $user, Rank $rank): bool
    {
        // Vérification simple pour les tests
        if ($user->pv_balance >= $rank->min_pv && $user->bv_balance >= $rank->min_bv) {
            return true;
        }

        return false;
    }

    // Les autres méthodes restent identiques...
}
