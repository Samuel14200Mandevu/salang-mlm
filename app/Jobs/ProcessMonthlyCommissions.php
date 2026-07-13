<?php
// app/Jobs/ProcessMonthlyCommissions.php

namespace App\Jobs;

use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyCommissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $period;
    protected bool $dryRun;
    public int $timeout = 3600; // 1 hour timeout

    public function __construct(string $period = null, bool $dryRun = false)
    {
        $this->period = $period ?? date('Y-m', strtotime('last month'));
        $this->dryRun = $dryRun;
    }

    public function handle(MonthlyCommissionService $commissionService): void
    {
        Log::info('Starting monthly commission processing', [
            'period' => $this->period,
            'dry_run' => $this->dryRun,
        ]);

        try {
            $year = substr($this->period, 0, 4);
            $month = substr($this->period, 5, 2);

            // Check if period already exists
            $existing = CommissionPeriod::where('period', $this->period)->first();

            if ($existing && $existing->status === 'closed') {
                Log::warning('Period already closed', ['period' => $this->period]);
                return;
            }

            if ($this->dryRun) {
                Log::info('Dry run mode - No changes will be made');
                $this->simulateProcessing($commissionService);
                return;
            }

            // Create or get period
            $period = $existing ?? $commissionService->createMonthlyPeriod((int)$year, (int)$month);

            $results = [
                'period' => $period->period,
                'steps' => []
            ];

            // Step 1: Calculate PV/BV
            $result = $commissionService->calculateMonthlyPVBV($period->id);
            $results['steps']['pv'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \Exception('Failed to calculate PV/BV');
            }

            // Step 2: Calculate ranks
            $result = $commissionService->calculateMonthlyRanks($period->id);
            $results['steps']['ranks'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \Exception('Failed to calculate ranks');
            }

            // Step 3: Calculate commissions
            $result = $commissionService->calculateMonthlyCommissions($period->id);
            $results['steps']['commissions'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \Exception('Failed to calculate commissions');
            }

            // Step 4: Generate payments
            $result = $commissionService->generatePayments($period->id);
            $results['steps']['payments'] = $result ? 'completed' : 'failed';

            Log::info('Monthly commission processing completed', $results);

        } catch (\Exception $e) {
            Log::error('Error processing monthly commissions', [
                'period' => $this->period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function simulateProcessing(MonthlyCommissionService $commissionService): void
    {
        Log::info('Simulating monthly processing', ['period' => $this->period]);

        $steps = [
            'create_period' => 'simulated',
            'calculate_pv' => 'simulated',
            'calculate_ranks' => 'simulated',
            'calculate_commissions' => 'simulated',
            'generate_payments' => 'simulated',
        ];

        Log::info('Simulation completed', ['steps' => $steps]);
    }
}