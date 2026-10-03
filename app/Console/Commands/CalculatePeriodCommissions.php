<?php
// app/Console/Commands/CalculatePeriodCommissions.php

namespace App\Console\Commands;

use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use App\Services\MLM\PeriodCommissionCalculator;
use Illuminate\Console\Command;

class CalculatePeriodCommissions extends Command
{
    protected $signature = 'commissions:calculate-period
                            {period? : Période YYYY-MM (défaut : période MLM courante)}
                            {--full : Pipeline complet (PV + rangs + commissions)}
                            {--user= : ID user spécifique (indirect/leadership uniquement)}';

    protected $description = 'Calcule les commissions pour une période MLM (8 → 7)';

    public function handle(
        MonthlyCommissionService $monthlyService,
        PeriodCommissionCalculator $periodCalculator
    ): int {
        $periodValue = $this->argument('period');

        if (!$periodValue) {
            // Pipeline --full le 8 : clôturer la période qui s’est terminée le 7
            $periodValue = $this->option('full')
                ? CommissionPeriod::getClosedPeriodValue()
                : CommissionPeriod::getPeriodValueForDate(now());
        }

        $period = CommissionPeriod::findOrCreateForValue($periodValue);

        if (!$period) {
            $this->error('❌ Période introuvable');
            return self::FAILURE;
        }

        $this->info("🔄 Période MLM : {$period->period}");
        $this->line("   Du {$period->start_date->format('d/m/Y')} au {$period->end_date->format('d/m/Y')}");
        $this->line("   Paiement prévu le {$period->payment_date->format('d/m/Y')}");
        $this->newLine();

        if ($this->option('full')) {
            $this->info('📊 [1/3] Calcul PV/BV...');
            $monthlyService->calculateMonthlyPVBV($period->id);

            $this->info('🏆 [2/3] Calcul des rangs...');
            $monthlyService->calculateMonthlyRanks($period->id);

            $this->info('💰 [3/3] Calcul des commissions...');
            $monthlyService->calculateMonthlyCommissions($period->id);

            $period->refresh();

            $this->newLine();
            $this->info('✅ Pipeline terminé');
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Statut',             $period->status],
                    ['Total commissions',  '$' . number_format($period->total_commissions, 2)],
                    ['Notes',              $period->notes ?? '-'],
                ]
            );
        } else {
            $userId = $this->option('user') ? (int) $this->option('user') : null;
            $result = $periodCalculator->calculateForPeriod($period, $userId);

            $this->info("✅ {$result['created']} commissions indirect/leadership créées");
            $this->info("💰 Total : $" . number_format($result['total_amount'], 2));
        }

        return self::SUCCESS;
    }
}
