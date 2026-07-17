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
    public int $tries = 3;

    public function __construct(string $period = null, bool $dryRun = false)
    {
        $this->period = $period ?? date('Y-m', strtotime('last month'));
        $this->dryRun = $dryRun;
    }

    /**
     * Execute the job.
     */
    public function handle(MonthlyCommissionService $commissionService): void
    {
        Log::info('Starting monthly commission processing', [
            'period' => $this->period,
            'dry_run' => $this->dryRun,
        ]);

        try {
            $year = substr($this->period, 0, 4);
            $month = substr($this->period, 5, 2);

            // Vérifier que la période est valide
            if ((int)$year < 2020 || (int)$month < 1 || (int)$month > 12) {
                Log::error('Invalid period format', ['period' => $this->period]);
                throw new \InvalidArgumentException("Invalid period format: {$this->period}");
            }

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

            // Vérifier que la période n'est pas déjà en cours
            if ($period->status === 'calculating' || $period->status === 'paying') {
                Log::warning('Period already being processed', [
                    'period' => $period->period,
                    'status' => $period->status
                ]);
                return;
            }

            $results = [
                'period' => $period->period,
                'steps' => []
            ];

            // Step 1: Calculate PV/BV
            Log::info('Step 1: Calculating PV/BV', ['period' => $period->period]);
            $result = $commissionService->calculateMonthlyPVBV($period->id);
            $results['steps']['pv'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \RuntimeException('Failed to calculate PV/BV');
            }

            // Step 2: Calculate ranks
            Log::info('Step 2: Calculating ranks', ['period' => $period->period]);
            $result = $commissionService->calculateMonthlyRanks($period->id);
            $results['steps']['ranks'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \RuntimeException('Failed to calculate ranks');
            }

            // Step 3: Calculate commissions
            Log::info('Step 3: Calculating commissions', ['period' => $period->period]);
            $result = $commissionService->calculateMonthlyCommissions($period->id);
            $results['steps']['commissions'] = $result ? 'completed' : 'failed';
            if (!$result) {
                throw new \RuntimeException('Failed to calculate commissions');
            }

            // Step 4: Generate payments
            Log::info('Step 4: Generating payments', ['period' => $period->period]);
            $result = $commissionService->generatePayments($period->id);
            $results['steps']['payments'] = $result ? 'completed' : 'failed';

            Log::info('Monthly commission processing completed', $results);

        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument in monthly commission processing', [
                'period' => $this->period,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\RuntimeException $e) {
            Log::error('Runtime error in monthly commission processing', [
                'period' => $this->period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Marquer la période comme en erreur
            if (isset($period)) {
                $period->status = 'pending';
                $period->notes = 'Erreur: ' . $e->getMessage();
                $period->save();
            }
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in monthly commission processing', [
                'period' => $this->period,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Marquer la période comme en erreur
            if (isset($period)) {
                $period->status = 'pending';
                $period->notes = 'Erreur inattendue: ' . $e->getMessage();
                $period->save();
            }
            
            throw $e;
        }
    }

    /**
     * Simulate processing without making changes
     */
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

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessMonthlyCommissions job failed', [
            'period' => $this->period,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Nettoyer la période en cas d'échec
        try {
            $period = CommissionPeriod::where('period', $this->period)->first();
            if ($period && in_array($period->status, ['calculating', 'paying'])) {
                $period->status = 'pending';
                $period->notes = 'Échec du job: ' . $exception->getMessage();
                $period->save();
            }
        } catch (\Exception $e) {
            Log::error('Error cleaning up period after job failure', [
                'period' => $this->period,
                'error' => $e->getMessage()
            ]);
        }
    }
}