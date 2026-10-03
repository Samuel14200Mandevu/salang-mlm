<?php
// app/Console/Commands/ResetMonthlyPV.php

namespace App\Console\Commands;

use App\Services\MLM\MonthlyCommissionService;
use App\Models\CommissionPeriod;
use Illuminate\Console\Command;

class ResetMonthlyPV extends Command
{
    protected $signature = 'mlm:reset-monthly-pv';
    protected $description = 'Réinitialise les PV/BV mensuels (le 8 à 00h00, début du mois MLM)';

    public function handle(MonthlyCommissionService $service): int
    {
        $this->info('🔄 Reset des PV/BV mensuels...');

        $period = CommissionPeriod::getCurrentPeriod();

        if ($period) {
            $this->line("   Période concernée : {$period->period}");
            $this->line("   Du {$period->start_date->format('d/m/Y')} au {$period->end_date->format('d/m/Y')}");
        }

        if (!$service->resetMonthlyPV()) {
            $this->error('❌ Erreur lors du reset');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Reset effectué');
        $this->line('   → Tous les monthly_pv / monthly_bv sont à 0');
        $this->line('   → Les données historiques restent dans pv_history');

        return self::SUCCESS;
    }
}
