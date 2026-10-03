<?php

namespace App\Jobs;


use App\Support\MlmPeriod;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * UpdateCumulativePV - DÉPRÉCIÉ
 * 
 * Redirige vers RecalculateAfterPVImport.
 */
class UpdateCumulativePV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(?int $userId = null)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        if (!$this->userId) {
            return;
        }

        RecalculateAfterPVImport::dispatch([$this->userId], MlmPeriod::current())
            ->onQueue('rank-recalculation');
    }
}