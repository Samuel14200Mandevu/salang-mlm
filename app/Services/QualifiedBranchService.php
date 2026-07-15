<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class QualifiedBranchService
{
    /**
     * Calcule et stocke les branches qualifiées pour une période donnée
     */
    public function calculateQualifiedBranches(string $period): void
    {
        // 1. Récupérer tous les utilisateurs actifs
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            // 2. Pour chaque utilisateur, trouver ses filleuls directs
            $directSponsors = User::where('parrain_id', $user->id)
                ->where('is_active', true)
                ->get();

            foreach ($directSponsors as $sponsor) {
                // 3. Calculer le PV total de la branche (le sponsor + tous ses descendants)
                $branchPV = $this->calculateBranchPV($sponsor);
                
                // 4. Déterminer le niveau le plus élevé atteint par la branche
                $branchRankLevel = $this->getBranchMaxRankLevel($sponsor, $period);

                // 5. Stocker dans qualified_branches
                DB::table('qualified_branches')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'branch_user_id' => $sponsor->id,
                        'period' => $period,
                    ],
                    [
                        'branch_rank_level' => $branchRankLevel,
                        'branch_pv' => $branchPV,
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function calculateBranchPV(User $sponsor): int
    {
        // Utiliser votre CTE pour calculer le PV total de la branche
        // (le sponsor + tous ses descendants)
        // ...
    }

    private function getBranchMaxRankLevel(User $sponsor, string $period): int
    {
        // Récupérer le rang le plus élevé atteint par le sponsor ou ses descendants
        // pour la période donnée
        // ...
    }
}