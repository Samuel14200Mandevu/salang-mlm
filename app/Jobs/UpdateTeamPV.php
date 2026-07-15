<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTeamPV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected bool $recursive;
    public int $timeout = 3600;
    public int $tries = 3;

    public function __construct(int $userId, bool $recursive = true)
    {
        $this->userId = $userId;
        $this->recursive = $recursive;
    }

    public function handle(): void
    {
        Log::info('UpdateTeamPV started', [
            'user_id' => $this->userId,
            'recursive' => $this->recursive
        ]);

        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('UpdateTeamPV: Utilisateur non trouvé', ['user_id' => $this->userId]);
            return;
        }

        try {
            DB::beginTransaction();

            // ✅ Mise à jour du team_pv de l'utilisateur
            $this->calculateAndUpdateTeamPV($user);

            Log::info('UpdateTeamPV: TeamPV mis à jour', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'team_pv' => $user->team_pv,
                'total_team' => $user->total_team ?? 0,
            ]);

            // ✅ Si récursif, mettre à jour les ancêtres
            if ($this->recursive) {
                $this->updateAncestorsTeamPV($user);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UpdateTeamPV failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calcule et met à jour le team_pv d'un utilisateur
     */
private function calculateAndUpdateTeamPV(User $user): void
{
    $teamPV = $this->calculateTeamPVRecursive($user);
    $teamBV = $this->calculateTeamBVRecursive($user);

    $user->team_pv = $teamPV;
    $user->team_bv = $teamBV;
    $user->total_team = $this->countDescendants($user);
    
    // ✅ Utiliser saveQuietly() pour éviter les événements
    $user->saveQuietly();
}

    /**
     * Calcule le PV total de l'équipe (récursif)
     */
    private function calculateTeamPVRecursive(User $user): int
    {
        $totalPV = 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalPV += $child->pv_balance;
            $totalPV += $this->calculateTeamPVRecursive($child);
        }

        return $totalPV;
    }

    /**
     * Calcule le BV total de l'équipe (récursif)
     */
    private function calculateTeamBVRecursive(User $user): int
    {
        $totalBV = 0;
        $children = User::where('parrain_id', $user->id)->where('is_active', true)->get();

        foreach ($children as $child) {
            $totalBV += $child->bv_balance;
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
        Log::debug('Ancestor update already in progress', ['user_id' => $user->id]);
        return;
    }
    
    Cache::put($lockKey, true, 60);
    
    try {
        $current = $user->parrain;
        $level = 1;
        $maxLevel = 9;
        $processed = [];

        while ($current && $level <= $maxLevel) {
            if (in_array($current->id, $processed)) {
                break;
            }
            
            $processed[] = $current->id;
            $this->calculateAndUpdateTeamPV($current);
            
            Log::debug('Ancestor team PV updated', [
                'ancestor_id' => $current->id,
                'level' => $level,
                'team_pv' => $current->team_pv
            ]);
            
            $current = $current->parrain;
            $level++;
        }
    } finally {
        Cache::forget($lockKey);
    }
}
}