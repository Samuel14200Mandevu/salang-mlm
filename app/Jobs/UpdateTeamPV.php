<?php

namespace App\Jobs;

use App\Services\MLM\TeamPVCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * UpdateTeamPV - DÉPRÉCIÉ
 * 
 * Ce job est conservé pour compatibilité mais redirige vers TeamPVCalculator.
 * Il sera supprimé quand tous les appelants auront été migrés.
 */
class UpdateTeamPV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected bool $recursive;
    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(?int $userId = null, bool $recursive = true)
    {
        $this->userId = $userId;
        $this->recursive = $recursive;
    }

    public function handle(TeamPVCalculator $teamPVCalculator): void
    {
        if (!$this->userId) {
            return;
        }

        $user = \App\Models\User::find($this->userId);
        if (!$user) {
            return;
        }

        try {
            $teamPVCalculator->updateUser($user);

            if ($this->recursive) {
                $teamPVCalculator->updateAncestors($user);
            }
        } catch (\Exception $e) {
            Log::error('UpdateTeamPV (deprecated) error', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}