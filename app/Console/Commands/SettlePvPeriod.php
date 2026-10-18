<?php

namespace App\Console\Commands;

use App\Models\CommissionPeriod;
use App\Services\PvPeriodSettlementService;
use Illuminate\Console\Command;

class SettlePvPeriod extends Command
{
    protected $signature = 'mlm:settle-pv-period
                            {period? : Période YYYY-MM clôturée (défaut : période terminée le 7)}
                            {--dry-run : Simulation sans écriture}';

    protected $description = 'Clôture PV POS : reliquat available_pv → pv_balance + pv_history (après le 7, ex. le 8)';

    public function handle(PvPeriodSettlementService $service): int
    {
        $periodValue = $this->argument('period')
            ?? CommissionPeriod::getClosedPeriodValue();

        $dates = CommissionPeriod::getPeriodDates($periodValue);

        $this->info("Clôture PV — période MLM {$periodValue}");
        $this->line("   Du {$dates['start']->format('d/m/Y')} au {$dates['end']->format('d/m/Y')}");
        $this->newLine();

        $stats = $service->settleClosedPeriod(
            $periodValue,
            (bool) $this->option('dry-run')
        );

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Membres soldés', $stats['users_settled']],
                ['PV validés', number_format($stats['pv_settled'])],
                ['BV validés', number_format($stats['bv_settled'])],
                ['Mode', $stats['dry_run'] ? 'dry-run' : 'production'],
            ]
        );

        if ($stats['dry_run']) {
            $this->warn('Dry-run : aucune modification en base.');
        } else {
            $this->info('Recalcul grades / team_pv dispatché (rank-recalculation).');
        }

        return self::SUCCESS;
    }
}
