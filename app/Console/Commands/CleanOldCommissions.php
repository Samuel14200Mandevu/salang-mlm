<?php
// app/Console/Commands/CleanOldCommissions.php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use Illuminate\Console\Command;

class CleanOldCommissions extends Command
{
    protected $signature = 'commissions:clean {--days=90 : Nombre de jours à conserver} {--dry-run : Simulation}';
    protected $description = 'Nettoyer les anciennes commissions';

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'error' => '<fg=red>✗</>',
            'process' => '<fg=cyan>⟳</>',
            'clean' => '<fg=magenta>◉</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $days = (int) $this->option('days');
        $isDryRun = $this->option('dry-run');
        
        $this->info($this->icon('clean') . " Nettoyage des commissions de plus de {$days} jours...");

        $date = now()->subDays($days);
        
        $periods = CommissionPeriod::where('status', 'closed')
            ->where('end_date', '<=', $date)
            ->get();

        if ($periods->isEmpty()) {
            $this->info($this->icon('info') . ' Aucune ancienne période à nettoyer');
            return 0;
        }

        $this->info("{$periods->count()} période(s) à nettoyer");

        if ($isDryRun) {
            $this->warn($this->icon('warning') . ' Mode SIMULATION - Aucune modification');
            
            $headers = ['Période', 'Date fin', 'Commissions', 'Total'];
            $rows = [];
            
            foreach ($periods as $period) {
                $commissionCount = Commission::where('commission_period_id', $period->id)->count();
                $rows[] = [
                    $period->period,
                    $period->end_date->format('d/m/Y'),
                    $commissionCount,
                    '$' . number_format($period->total_commissions ?? 0, 2),
                ];
            }
            
            $this->table($headers, $rows);
            $this->info($this->icon('success') . ' Simulation terminée');
            return 0;
        }

        $totalDeleted = 0;

        foreach ($periods as $period) {
            $deleted = Commission::where('commission_period_id', $period->id)->delete();
            $totalDeleted += $deleted;
            $this->line("   - Période {$period->period}: {$deleted} commissions supprimées");
        }

        $this->info($this->icon('success') . " Nettoyage terminé: {$totalDeleted} commissions supprimées");
        return 0;
    }
}