<?php
// app/Console/Commands/ProcessMonthlyCommissions.php

namespace App\Console\Commands;

use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessMonthlyCommissions extends Command
{
    protected $signature = 'commissions:process-monthly 
                            {--year= : Année (ex: 2024)}
                            {--month= : Mois (ex: 07)}
                            {--steps=all : Étapes (pv,ranks,commissions,payments)}
                            {--dry-run : Simulation sans modifications}
                            {--force : Forcer l\'exécution même si pas le bon moment}';
    
    protected $description = 'Traiter les commissions mensuelles (calcul + paiement)';

    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        parent::__construct();
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'error' => '<fg=red>✗</>',
            'process' => '<fg=cyan>⟳</>',
            'clock' => '<fg=yellow>⌛</>',
            'money' => '<fg=green>$</>',
            'database' => '<fg=magenta>◉</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $month = $this->option('month') ?? Carbon::now()->subMonth()->month;
        $steps = $this->option('steps');
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info($this->icon('process') . ' Traitement des commissions mensuelles');
        $this->line("   Période: {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));
        $this->line("   Étapes: {$steps}");
        if ($isDryRun) {
            $this->warn($this->icon('warning') . ' Mode simulation');
        }
        $this->newLine();

        if (!$isForce) {
            $today = Carbon::now();
            $isFirstOfMonth = $today->day == 1;
            $isFifteenth = $today->day == 15;
            
            if (!$isFirstOfMonth && !$isFifteenth) {
                $this->warn($this->icon('warning') . ' Cette commande est conçue pour être exécutée le 1er ou le 15 du mois.');
                $this->line('   - 1er du mois: Calcul des commissions');
                $this->line('   - 15 du mois: Paiement des commissions');
                $this->line('   Utilisez --force pour forcer l\'exécution.');
                
                if (!$this->confirm('Voulez-vous continuer quand même ?')) {
                    return 0;
                }
            }
        }

        $period = $this->monthlyCommissionService->createMonthlyPeriod($year, $month);
        $this->info($this->icon('info') . " Période: {$period->period} (Statut: {$period->status_label})");

        $stepsToRun = $steps === 'all' 
            ? ['pv', 'ranks', 'commissions', 'payments']
            : explode(',', $steps);

        $results = [];

        foreach ($stepsToRun as $step) {
            $this->newLine();
            $this->info($this->icon('database') . " Étape: " . strtoupper($step));

            try {
                switch ($step) {
                    case 'pv':
                        $result = $this->monthlyCommissionService->calculateMonthlyPVBV($period->id);
                        $results['pv'] = $result ? '✓' : '✗';
                        $this->line("   PV/BV: " . ($result ? 'Calculés' : 'Échec'));
                        break;

                    case 'ranks':
                        $result = $this->monthlyCommissionService->calculateMonthlyRanks($period->id);
                        $results['ranks'] = $result ? '✓' : '✗';
                        $this->line("   Rangs: " . ($result ? 'Calculés' : 'Échec'));
                        break;

                    case 'commissions':
                        $result = $this->monthlyCommissionService->calculateMonthlyCommissions($period->id);
                        $results['commissions'] = $result ? '✓' : '✗';
                        $this->line("   Commissions: " . ($result ? 'Calculées' : 'Échec'));
                        $period->refresh();
                        $this->line("   Total: $" . number_format($period->total_commissions ?? 0, 2));
                        break;

                    case 'payments':
                        if (!$isDryRun) {
                            $today = Carbon::now();
                            if ($today->day >= 15 || $isForce) {
                                $result = $this->monthlyCommissionService->generatePayments($period->id);
                                $results['payments'] = $result ? '✓' : '✗';
                                $this->line("   Paiements: " . ($result ? 'Générés' : 'Échec'));
                                if ($result) {
                                    $period->refresh();
                                    $this->line("   Total payé: $" . number_format($period->total_paid ?? 0, 2));
                                }
                            } else {
                                $this->warn($this->icon('clock') . " Paiements disponibles à partir du 15 (aujourd'hui: {$today->day})");
                                $results['payments'] = '⌛';
                            }
                        } else {
                            $this->warn($this->icon('warning') . ' Simulation: paiements non exécutés');
                            $results['payments'] = '🔍';
                        }
                        break;

                    default:
                        $this->error($this->icon('error') . " Étape inconnue: {$step}");
                        $results[$step] = '✗';
                }

            } catch (\Exception $e) {
                $this->error($this->icon('error') . " Erreur: {$e->getMessage()}");
                $results[$step] = '✗';
                Log::error('Erreur étape traitement mensuel', [
                    'step' => $step,
                    'period' => $period->period,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info('=== RÉSUMÉ ===');
        
        $rows = [];
        foreach ($results as $step => $status) {
            $statusText = match($status) {
                '✓' => '<fg=green>OK</>',
                '✗' => '<fg=red>ÉCHEC</>',
                '⌛' => '<fg=yellow>EN ATTENTE</>',
                '🔍' => '<fg=yellow>SIMULATION</>',
                default => $status,
            };
            $rows[] = [ucfirst($step), $statusText];
        }
        $this->table(['Étape', 'Statut'], $rows);

        $period->refresh();
        $this->newLine();
        $this->info($this->icon('money') . " Statut final de la période: {$period->status_label}");
        $this->line("   Commissions: $" . number_format($period->total_commissions ?? 0, 2));
        $this->line("   Payé: $" . number_format($period->total_paid ?? 0, 2));

        if (!$isDryRun && !in_array('✗', $results)) {
            $this->info($this->icon('success') . ' Traitement mensuel terminé avec succès !');
        } elseif ($isDryRun) {
            $this->warn($this->icon('warning') . ' Simulation terminée');
        } else {
            $this->error($this->icon('error') . ' Des erreurs sont survenues. Vérifiez les logs.');
        }

        return 0;
    }
}