<?php
// app/Console/Commands/FixAllTeamPV.php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAllTeamPV extends Command
{
    protected $signature = 'team:fix-all 
                            {--dry-run : Simulation sans modification}
                            {--chunk=100 : Nombre d\'utilisateurs par lot}';
    
    protected $description = 'Recalcule le team_pv de TOUS les utilisateurs avec leurs descendants';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== RECALCUL DU TEAM_PV POUR TOUS LES UTILISATEURS ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $chunkSize = (int) $this->option('chunk');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification');
            $this->newLine();
        }

        // Récupérer tous les utilisateurs actifs
        $totalUsers = User::where('is_active', true)->count();
        $this->info("Total utilisateurs actifs: {$totalUsers}");
        $this->newLine();

        if ($totalUsers === 0) {
            $this->warn('Aucun utilisateur actif trouve');
            return 0;
        }

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $updated = 0;
        $totalPV = 0;
        $errors = [];

        // Traiter par lots
        User::where('is_active', true)
            ->chunk($chunkSize, function ($users) use (&$updated, &$totalPV, &$errors, $dryRun, $bar) {
                foreach ($users as $user) {
                    try {
                        // Calculer le team_pv avec TOUS les descendants
                        $teamData = $this->calculateTeamPVRecursive($user);
                        
                        if (!$dryRun) {
                            $oldTeamPV = $user->team_pv;
                            
                            $user->team_pv = $teamData['pv'];
                            $user->team_bv = $teamData['bv'];
                            $user->total_team = $teamData['total'];
                            $user->saveQuietly();
                            
                            // Recalculer le grade
                            $this->rankCalculator->recalculateUserRank($user, 'Correction team_pv globale');
                            
                            if ($oldTeamPV != $teamData['pv']) {
                                $updated++;
                                $totalPV += ($teamData['pv'] - $oldTeamPV);
                            }
                        } else {
                            // Mode simulation
                            if ($user->team_pv != $teamData['pv']) {
                                $updated++;
                                $totalPV += ($teamData['pv'] - ($user->team_pv ?? 0));
                            }
                        }

                    } catch (\Exception $e) {
                        $errors[] = "Erreur pour {$user->name} (ID: {$user->id}): " . $e->getMessage();
                        Log::error('Erreur correction team_pv', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                    
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Résultats
        $this->info('=== RESULTATS ===');
        $this->line("   Total utilisateurs traites: {$totalUsers}");
        $this->line("   Utilisateurs mis a jour: {$updated}");
        $this->line("   Total PV d'equipe ajoutes: " . number_format($totalPV, 1));

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('=== ERREURS ===');
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
            $this->newLine();
            $this->warn('Consultez les logs pour plus de details: storage/logs/laravel.log');
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('Simulation terminee. Aucune modification.');
        }

        $this->newLine();
        $this->info('=== FIN ===');

        return 0;
    }

    /**
     * Calcul récursif du team_pv avec TOUS les descendants
     */
    private function calculateTeamPVRecursive(User $user): array
    {
        $totalPV = $user->pv_balance ?? 0;
        $totalBV = $user->bv_balance ?? 0;
        $totalCount = 0;

        $filleuls = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($filleuls as $filleul) {
            $childData = $this->calculateTeamPVRecursive($filleul);
            $totalPV += $childData['pv'];
            $totalBV += $childData['bv'];
            $totalCount += 1 + $childData['total'];
        }

        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }
}