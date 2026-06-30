<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Notifications\RankUpgradedNotification;
use Illuminate\Support\Facades\Log;

class RankService
{
    /**
     * Mettre à jour le grade d'un utilisateur
     */
    public function updateRank($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $oldRankId = $user->rank_id;
        $oldRankName = $user->rank;

        $ranks = Rank::orderBy('min_pv', 'asc')->get();
        $newRank = null;

        foreach ($ranks as $rank) {
            if ($user->pv_balance >= $rank->min_pv) {
                $newRank = $rank;
            }
        }

        if (!$newRank) {
            $newRank = $ranks->first();
        }

        if ($newRank->id != $oldRankId) {
            $user->rank = $newRank->name;
            $user->rank_id = $newRank->id;
            $user->save();

            RankHistory::create([
                'user_id' => $user->id,
                'old_rank_id' => $oldRankId,
                'new_rank_id' => $newRank->id,
                'old_rank_name' => $oldRankName,
                'new_rank_name' => $newRank->name,
                'pv_at_time' => $user->pv_balance,
                'bv_at_time' => $user->bv_balance,
                'notes' => 'Mise à jour automatique',
            ]);

            Log::info("Grade mis à jour", [
                'user_id' => $user->id,
                'old_rank' => $oldRankName,
                'new_rank' => $newRank->name,
            ]);

            // Envoyer la notification de promotion
            try {
                $user->notify(new RankUpgradedNotification(
                    $oldRankName,
                    $newRank->name,
                    $user->pv_balance
                ));
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification grade: ' . $e->getMessage());
            }

            return true;
        }

        return false;
    }

    /**
     * Mettre à jour les grades de tous les utilisateurs
     */
    public function updateAllRanks()
    {
        $users = User::all();
        $updated = 0;

        foreach ($users as $user) {
            if ($this->updateRank($user->id)) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Obtenir le prochain grade
     */
    public function getNextRank($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return Rank::where('min_pv', '>', $user->pv_balance)
            ->orderBy('min_pv', 'asc')
            ->first();
    }

    /**
     * Calculer la progression vers le prochain grade
     */
    public function getProgress($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $nextRank = $this->getNextRank($userId);

        if (!$nextRank) {
            return [
                'current' => $user->rank,
                'next' => 'Maximum Level',
                'progress' => 100,
                'pv_needed' => 0,
                'current_pv' => $user->pv_balance,
                'next_pv' => $user->pv_balance,
            ];
        }

        $progress = min(100, ($user->pv_balance / $nextRank->min_pv) * 100);

        return [
            'current' => $user->rank,
            'next' => $nextRank->name,
            'progress' => $progress,
            'pv_needed' => max(0, $nextRank->min_pv - $user->pv_balance),
            'current_pv' => $user->pv_balance,
            'next_pv' => $nextRank->min_pv,
        ];
    }
}