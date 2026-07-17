<?php
// app/Jobs/UpdateTeamPV.php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UpdateTeamPV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected bool $recursive;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(?int $userId = null, bool $recursive = true)
    {
        $this->userId = $userId;
        $this->recursive = $recursive;
    }

    public function handle(): void
    {
        Log::info('UpdateTeamPV started', [
            'user_id' => $this->userId ?? 'all',
            'recursive' => $this->recursive
        ]);

        $query = User::where('is_active', true);

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        $query->chunk(100, function ($users) {
            foreach ($users as $user) {
                try {
                    DB::beginTransaction();

                    // Mise à jour du Team PV
                    $this->calculateAndUpdateTeamPV($user);

                    Log::debug('TeamPV mis à jour', [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'team_pv' => $user->team_pv,
                    ]);

                    // Mettre à jour les ancêtres
                    if ($this->recursive) {
                        $this->updateAncestorsTeamPV($user);
                    }

                    // Calculer le grade
                    $user->calculateAndUpdateRank();

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('UpdateTeamPV error', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        Log::info('UpdateTeamPV completed');
    }

    /**
     * Calcule et met à jour le Team PV d'un utilisateur
     */
    private function calculateAndUpdateTeamPV(User $user): void
    {
        $teamPV = $this->calculateTeamPVRecursive($user);
        $teamBV = $this->calculateTeamBVRecursive($user);
        $totalTeam = $this->countDescendants($user);

        $user->team_pv = $teamPV;
        $user->team_bv = $teamBV;
        $user->total_team = $totalTeam;
        $user->saveQuietly();

        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
    }

    /**
     * Calcule le PV total de l'équipe (récursif)
     */
    private function calculateTeamPVRecursive(User $user): int
    {
        $totalPV = $user->pv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalPV += $this->calculateTeamPVRecursive($child);
        }

        return $totalPV;
    }

    /**
     * Calcule le BV total de l'équipe (récursif)
     */
    private function calculateTeamBVRecursive(User $user): int
    {
        $totalBV = $user->bv_balance ?? 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalBV += $this->calculateTeamBVRecursive($child);
        }

        return $totalBV;
    }

    /**
     * Compte le nombre total de descendants
     */
    private function countDescendants(User $user): int
    {
        $count = 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $count++;
            $count += $this->countDescendants($child);
        }

        return $count;
    }

    /**
     * Met à jour les PV d'équipe de tous les ancêtres
     */
    private function updateAncestorsTeamPV(User $user): void
    {
        $lockKey = "ancestor_update_{$user->id}";
        
        if (Cache::get($lockKey, false)) {
            return;
        }
        
        Cache::put($lockKey, true, 60);
        
        try {
            $current = $user->parrain;
            $level = 1;
            $maxLevel = 20;
            $processed = [];

            while ($current && $level <= $maxLevel) {
                if (in_array($current->id, $processed)) {
                    break;
                }
                
                $processed[] = $current->id;
                $this->calculateAndUpdateTeamPV($current);
                
                $current = $current->parrain;
                $level++;
            }
        } finally {
            Cache::forget($lockKey);
        }
    }
}