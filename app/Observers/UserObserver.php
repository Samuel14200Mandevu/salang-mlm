<?php
// app/Observers/UserObserver.php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Après la création d'un utilisateur
     */
    public function created(User $user): void
    {
        // Mettre à jour les PV d'équipe
        $user->updateTeamPVWithoutEvents();
        $user->updateAllAncestorsWithoutEvents();
        
        // Calculer le grade
        $user->calculateAndUpdateRank();
    }

    /**
     * Après la mise à jour d'un utilisateur
     */
    public function updated(User $user): void
    {
        $fieldsToWatch = [
            'pv_balance', 'bv_balance', 'monthly_pv', 'monthly_bv',
            'team_pv', 'team_bv', 'qualified_branches', 'direct_sponsors_count',
            'is_active', 'rank_id', 'parrain_id'
        ];

        foreach ($fieldsToWatch as $field) {
            if ($user->wasChanged($field)) {
                // Mettre à jour les PV d'équipe
                $user->updateTeamPVWithoutEvents();
                $user->updateAllAncestorsWithoutEvents();
                
                // Calculer le grade
                $user->calculateAndUpdateRank();
                break;
            }
        }
    }

    /**
     * Après la sauvegarde d'un utilisateur
     */
    public function saved(User $user): void
    {
        // Vérifier si le grade doit être mis à jour
        if (!$user->rank_id || $user->rank_id == 1) {
            $user->calculateAndUpdateRank();
        }
    }

    /**
     * Après la suppression d'un utilisateur
     */
    public function deleted(User $user): void
    {
        if ($user->parrain_id) {
            $parrain = User::find($user->parrain_id);
            if ($parrain) {
                $parrain->updateTeamPVWithoutEvents();
                $parrain->updateAllAncestorsWithoutEvents();
                $parrain->calculateAndUpdateRank();
            }
        }
    }
}