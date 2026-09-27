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
 * CalculatePVBV - DÉPRÉCIÉ
 * 
 * Redirige vers RecalculateAfterPVImport.
 */
class CalculatePVBV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $userId;
    protected string $period;
    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(?int $userId = null, string $period = null)
    {
        $this->userId = $userId;
        $this->period = $period ?? date('Y-m');
    }

    public function handle(): void
    {
        if (!$this->userId) {
            return;
        }

        // ✅ Redirige vers le nouveau job unifié
        RecalculateAfterPVImport::dispatch([$this->userId], $this->period)
            ->onQueue('rank-recalculation');
    }
}