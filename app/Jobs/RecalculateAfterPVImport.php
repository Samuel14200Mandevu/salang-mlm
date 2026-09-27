<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\TeamPVCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateAfterPVImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    protected array $userIds;
    protected string $period;

    public function __construct(array $userIds, string $period)
    {
        $this->userIds = array_values(array_unique($userIds));
        $this->period = $period;
    }

    public function handle(
        AdvancedRankCalculator $rankCalculator,
        TeamPVCalculator $teamPVCalculator
    ): void {
        Log::info('🚀 Début recalcul post-modification', [
            'period' => $this->period,
            'user_ids' => $this->userIds,
            'users_count' => count($this->userIds),
        ]);

        $startTime = microtime(true);

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 1 : Recalculer monthly_pv depuis pv_history
        // ═══════════════════════════════════════════════════════════
        $this->recalcMonthlyFromHistory();

        // ═══════════════════════════════════════════════════════════
        // ✅ ÉTAPE 2 CORRIGÉE : Collecter TOUS les users + ancêtres
        //    (récursif : ancêtres des ancêtres inclus)
        // ═══════════════════════════════════════════════════════════
        $allUserIds = [];

        foreach ($this->userIds as $userId) {
            $allUserIds[$userId] = true;

            $user = User::find($userId);
            if (!$user) {
                continue;
            }

            // Ancêtres directs du user
            foreach ($teamPVCalculator->getAncestorIds($user) as $ancestorId) {
                $allUserIds[$ancestorId] = true;
            }
        }

        // ✅ AJOUT : Inclure les ancêtres des ancêtres (récursif)
        $processedAncestors = [];
        $queue = array_keys($allUserIds);

        while (!empty($queue)) {
            $currentId = array_shift($queue);

            if (isset($processedAncestors[$currentId])) {
                continue;
            }
            $processedAncestors[$currentId] = true;

            $current = User::find($currentId);
            if (!$current) continue;

            foreach ($teamPVCalculator->getAncestorIds($current) as $ancestorId) {
                if (!isset($allUserIds[$ancestorId])) {
                    $allUserIds[$ancestorId] = true;
                    $queue[] = $ancestorId;
                }
            }
        }

        $allUserIds = array_keys($allUserIds);

        Log::info('📊 Users totaux à recalculer (users + tous ancêtres récursifs)', [
            'count' => count($allUserIds),
            'user_ids' => array_slice($allUserIds, 0, 20), // Premiers 20 pour debug
        ]);

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 3 : Recalculer team_pv de TOUS ces users
        // ═══════════════════════════════════════════════════════════
        $users = User::whereIn('id', $allUserIds)
            ->where('is_active', true)
            ->get();

        // Trier par profondeur (les plus profonds d'abord)
        $users = $users->sortBy(function ($u) use ($teamPVCalculator) {
            return count($teamPVCalculator->getAncestorIds($u));
        })->values();

        foreach ($users as $user) {
            try {
                $teamPVCalculator->updateUser($user);
            } catch (\Exception $e) {
                Log::error('Erreur update team_pv', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 4 : Recalculer le grade de chaque user
        // ═══════════════════════════════════════════════════════════
        foreach ($users as $user) {
            try {
                $user->refresh();
                $this->updateUserRankAndSave($user, $rankCalculator, 'Post-modification');
            } catch (\Exception $e) {
                Log::error('Erreur recalcul grade user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        Log::info('✅ Recalcul post-modification terminé', [
            'period' => $this->period,
            'users_count' => count($allUserIds),
            'duration_seconds' => $duration,
        ]);
    }

    protected function updateUserRankAndSave(
        User $user,
        AdvancedRankCalculator $rankCalculator,
        string $reason
    ): void {
        try {
            // ✅ Vider TOUS les caches
            Cache::forget("rank_calculation_{$user->id}");
            Cache::forget("user_rank_{$user->id}");
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            Cache::forget("team_pv_{$user->id}");
            $rankCalculator->clearCache();

            $oldRankId = $user->rank_id;
            $oldRankName = $user->rank ?? 'Distributeur';
            $oldRankLevel = $user->rank_level ?? 1;

            $newRank = $rankCalculator->calculateAdvancedRank($user);

            if (!$newRank) {
                return;
            }

            // Détecte si UNE des 3 colonnes est différente
            $needsUpdate = (
                $newRank->id != $user->rank_id ||
                $newRank->name != $user->rank ||
                $newRank->level != $user->rank_level
            );

            if (!$needsUpdate) {
                return;
            }

            // FORCE la synchronisation des 3 colonnes
            $user->rank_id = $newRank->id;
            $user->rank = $newRank->name;
            $user->rank_level = $newRank->level;
            $user->last_rank_update = now();
            $user->saveQuietly();

            try {
                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $user->pv_balance ?? 0,
                    'bv_at_time' => $user->bv_balance ?? 0,
                    'monthly_pv_at_time' => $user->monthly_pv ?? 0,
                    'notes' => $reason,
                ]);
            } catch (\Exception $e) {
                Log::warning('Impossible de créer RankHistory', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('📈 Grade mis à jour (3 colonnes synchronisées)', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'old' => "{$oldRankName} (Niv.{$oldRankLevel})",
                'new' => "{$newRank->name} (Niv.{$newRank->level})",
                'reason' => $reason,
            ]);

            Cache::forget("user_rank_{$user->id}");
            Cache::forget("rank_calculation_{$user->id}");
            $rankCalculator->clearCache();

        } catch (\Exception $e) {
            Log::error('Erreur updateUserRankAndSave', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * Recalcul de monthly_pv depuis pv_history
     * 
     * RÈGLE : Ne recalculer monthly_pv QUE pour le mois EN COURS
     *         Pour les mois passés, monthly_pv reste à 0.
     * ═══════════════════════════════════════════════════════════════════
     */
    protected function recalcMonthlyFromHistory(): void
    {
        $currentPeriod = date('Y-m');

        // Si la période importée est un mois passé, on NE recalcule PAS
        if ($this->period !== $currentPeriod) {
            Log::info('⚠️ monthly_pv NON recalculé (mois passé)', [
                'period_import' => $this->period,
                'current_period' => $currentPeriod,
                'users_count' => count($this->userIds),
            ]);
            return;
        }

        try {
            $totals = DB::table('pv_history')
                ->select('user_id', DB::raw('SUM(amount) as total_amount'))
                ->where('period', $this->period)
                ->whereIn('type', ['personal', 'monthly'])
                ->whereIn('user_id', $this->userIds)
                ->groupBy('user_id')
                ->get();

            foreach ($totals as $row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->update([
                        'monthly_pv' => $row->total_amount,
                        'monthly_bv' => $row->total_amount * 0.8,
                        'updated_at' => now(),
                    ]);
            }

            // Pour les users sans PV ce mois, on reset monthly_pv à 0
            $usersWithPV = $totals->pluck('user_id')->toArray();
            $usersWithoutPV = array_diff($this->userIds, $usersWithPV);

            if (!empty($usersWithoutPV)) {
                DB::table('users')
                    ->whereIn('id', $usersWithoutPV)
                    ->update([
                        'monthly_pv' => 0,
                        'monthly_bv' => 0,
                        'updated_at' => now(),
                    ]);
            }

            Log::info('✅ monthly_pv recalculé (mois en cours)', [
                'period' => $this->period,
                'users_updated' => $totals->count(),
                'users_reset' => count($usersWithoutPV),
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur recalcMonthlyFromHistory', [
                'period' => $this->period,
                'error' => $e->getMessage(),
            ]);
        }
    }
}