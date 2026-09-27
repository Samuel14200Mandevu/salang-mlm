<?php

namespace App\Jobs;

use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * UpdateRanks - DÉPRÉCIÉ
 * 
 * Ce job est conservé pour compatibilité mais redirige vers AdvancedRankCalculator.
 * Il sera supprimé quand tous les appelants auront été migrés.
 */
class UpdateRanks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected bool $forceAll;
    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(?int $userId = null, bool $forceAll = false)
    {
        $this->userId = $userId;
        $this->forceAll = $forceAll;
    }

    public function handle(AdvancedRankCalculator $rankCalculator): void
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            if ($user && $user->is_active) {
                try {
                    $rankCalculator->recalculateUserRankLight($user, 'UpdateRanks deprecated');
                } catch (\Exception $e) {
                    Log::error('UpdateRanks (deprecated) error', [
                        'user_id' => $this->userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            return;
        }

        // Ne traite pas tous les users en mode déprécié (trop lourd)
        // Le scheduler s'en charge via mlm:fix-team-pv
        Log::debug('UpdateRanks (deprecated) skipped for all users');
    }
}