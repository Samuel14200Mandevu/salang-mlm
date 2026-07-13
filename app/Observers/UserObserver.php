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
        // Calculer le grade initial
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
            'is_active'
        ];

        foreach ($fieldsToWatch as $field) {
            if ($user->wasChanged($field)) {
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
}