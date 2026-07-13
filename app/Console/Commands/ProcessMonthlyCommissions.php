<?php
// app/Console/Commands/ProcessMonthlyCommissions.php

namespace App\Console\Commands;

use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessMonthlyCommissions extends Command
{
    protected $signature = 'mlm:process-monthly 
                            {--year= : Année (ex: 2024)}
                            {--month= : Mois (ex: 07)}
                            {--steps=all : Étapes à exécuter (all,pv,ranks,commissions,payments)}
                            {--dry-run : Simulation sans modifications}';
    
    protected $description = 'Traiter toutes les opérations mensuelles MLM';

    protected $monthlyCommissionService;

    public function __construct(MonthlyCommissionService $monthlyCommissionService)
    {
        parent::__construct();
        $this->monthlyCommissionService = $monthlyCommissionService;
    }

    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $month = $this->option('month') ?? Carbon::now()->subMonth()->month;
        $steps = $this->option('steps');
        $isDryRun = $this->option('dry-run');

        $this->info('🚀 Traitement mensuel MLM');
        $this->line("📅 Période: {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));
        $this->line("📌 Étapes: {$steps}");
        if ($isDryRun) {
            $this->warn('🔍 Mode simulation');
        }
        $this->newLine();

        $stepsToRun = $steps === 'all' 
            ? ['pv', 'ranks', 'commissions', 'payments']
            : explode(',', $steps);

        $period = $this->monthlyCommissionService->createMonthlyPeriod($year, $month);
        $this->info("✅ Période créée: {$period->period}");

        $results = [];

        foreach ($stepsToRun as $step) {
            $this->newLine();
            $this->info("📊 Étape: " . strtoupper($step));

            try {
                switch ($step) {
                    case 'pv':
                        $result = $this->monthlyCommissionService->calculateMonthlyPVBV($period->id);
                        $results['pv'] = $result ? '✅' : '❌';
                        break;

                    case 'ranks':
                        $result = $this->monthlyCommissionService->calculateMonthlyRanks($period->id);
                        $results['ranks'] = $result ? '✅' : '❌';
                        break;

                    case 'commissions':
                        $result = $this->monthlyCommissionService->calculateMonthlyCommissions($period->id);
                        $results['commissions'] = $result ? '✅' : '❌';
                        break;

                    case 'payments':
                        if (!$isDryRun) {
                            $result = $this->monthlyCommissionService->generatePayments($period->id);
                            $results['payments'] = $result ? '✅' : '❌';
                        } else {
                            $this->warn('🔍 Simulation: paiements non exécutés');
                            $results['payments'] = '🔍';
                        }
                        break;

                    default:
                        $this->error("❌ Étape inconnue: {$step}");
                        $results[$step] = '❌';
                }

                $this->line("Résultat: {$results[$step]}");

            } catch (\Exception $e) {
                $this->error("❌ Erreur: {$e->getMessage()}");
                $results[$step] = '❌';
            }
        }

        $this->newLine();
        $this->info('📊 RÉSUMÉ');
        $this->table(
            ['Étape', 'Statut'],
            collect($results)->map(fn($v, $k) => [$k, $v])->toArray()
        );

        if (!$isDryRun && !in_array('❌', $results)) {
            $this->info('✅ Traitement mensuel terminé avec succès !');
        } elseif ($isDryRun) {
            $this->warn('🔍 Simulation terminée');
        } else {
            $this->error('⚠️ Des erreurs sont survenues');
        }
    }
}