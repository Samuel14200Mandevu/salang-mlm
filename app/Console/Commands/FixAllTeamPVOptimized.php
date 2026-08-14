<?php
// app/Console/Commands/FixAllTeamPVOptimized.php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAllTeamPVOptimized extends Command
{
    protected $signature = 'team:fix-optimized 
                            {--dry-run : Simulation sans modification}
                            {--user= : ID de l\'utilisateur specifique}';
    
    protected $description = 'Recalcule le team_pv de TOUS les utilisateurs (version SQL optimisee)';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== RECALCUL OPTIMISE DU TEAM_PV ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification');
            $this->newLine();
        }

        // Construire la requête
        $query = User::where('is_active', true);
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        $totalUsers = $users->count();
        $this->info("Total utilisateurs a traiter: {$totalUsers}");
        $this->newLine();

        if ($totalUsers === 0) {
            $this->warn('Aucun utilisateur trouve');
            return 0;
        }

        // Utiliser une approche par niveau pour éviter la récursivité
        $this->info('Construction de l\'arbre genealogique...');
        
        // Récupérer toutes les relations parent-enfant en une seule requête
        $relations = DB::table('users')
            ->where('is_active', true)
            ->whereNotNull('parrain_id')
            ->select('id', 'parrain_id', 'pv_balance', 'bv_balance')
            ->get()
            ->groupBy('parrain_id');

        $this->info('Relations recuperees. Calcul en cours...');
        $this->newLine();

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        $updated = 0;
        $totalPV = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                // Calculer le team_pv en utilisant la relation pré-chargée
                $teamData = $this->calculateTeamPVFromRelations($user->id, $relations);
                
                if (!$dryRun) {
                    $oldTeamPV = $user->team_pv ?? 0;
                    
                    if ($oldTeamPV != $teamData['pv']) {
                        $user->team_pv = $teamData['pv'];
                        $user->team_bv = $teamData['bv'];
                        $user->total_team = $teamData['total'];
                        $user->saveQuietly();
                        
                        // Recalculer le grade (uniquement si le team_pv a changé)
                        $this->rankCalculator->recalculateUserRank($user, 'Correction team_pv optimisee');
                        
                        $updated++;
                        $totalPV += ($teamData['pv'] - $oldTeamPV);
                    }
                } else {
                    // Mode simulation
                    if (($user->team_pv ?? 0) != $teamData['pv']) {
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
     * Calcul du team_pv à partir des relations pré-chargées (itérative, non récursive)
     */
    private function calculateTeamPVFromRelations(int $userId, $relations): array
    {
        $totalPV = 0;
        $totalBV = 0;
        $totalCount = 0;
        
        $stack = [$userId];
        $visited = [];
        
        while (!empty($stack)) {
            $currentId = array_pop($stack);
            
            if (in_array($currentId, $visited)) {
                continue;
            }
            $visited[] = $currentId;
            
            // Récupérer les enfants depuis les relations pré-chargées
            if (isset($relations[$currentId])) {
                foreach ($relations[$currentId] as $child) {
                    $totalPV += $child->pv_balance ?? 0;
                    $totalBV += $child->bv_balance ?? 0;
                    $totalCount++;
                    $stack[] = $child->id;
                }
            }
        }
        
        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }
}