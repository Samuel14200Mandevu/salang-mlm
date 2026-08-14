<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixTeamPVHistorical extends Command
{
    protected $signature = 'team:fix-historical 
                            {--user= : ID du parrain specifique}
                            {--dry-run : Simulation sans modification}
                            {--force : Forcer sans confirmation}';
    
    protected $description = 'Corrige le team_pv des parrains pour les donnees historiques importees manuellement';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== CORRECTION DU TEAM_PV POUR DONNEES HISTORIQUES ===');
        $this->newLine();

        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification');
            $this->newLine();
        }

        // Construire la requête
        if ($userId) {
            $parrains = User::where('id', $userId)->where('is_active', true)->get();
            if ($parrains->isEmpty()) {
                $this->error("Utilisateur ID {$userId} non trouve ou inactif");
                return 1;
            }
            $this->info("Correction pour le parrain ID: {$userId}");
        } else {
            $parrains = User::where('is_active', true)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users as filleuls')
                        ->whereColumn('filleuls.parrain_id', 'users.id')
                        ->where('filleuls.is_active', true)
                        ->where('filleuls.pv_balance', '>', 0);
                })
                ->get();
            
            $this->info("Correction pour " . $parrains->count() . " parrains");
        }

        if ($parrains->isEmpty()) {
            $this->warn('Aucun parrain avec des filleuls ayant des PV');
            return 0;
        }

        $this->newLine();

        // Collecter les données
        $data = [];
        foreach ($parrains as $parrain) {
            $filleuls = User::where('parrain_id', $parrain->id)
                ->where('is_active', true)
                ->where('pv_balance', '>', 0)
                ->get();

            if ($filleuls->isEmpty()) {
                continue;
            }

            $totalTeamPV = $filleuls->sum('pv_balance');
            $totalTeamBV = $filleuls->sum('bv_balance');

            $data[] = [
                'parrain' => $parrain,
                'team_pv_actuel' => $parrain->team_pv ?? 0,
                'team_pv_a_ajouter' => $totalTeamPV,
                'team_bv_a_ajouter' => $totalTeamBV,
                'total_team' => $filleuls->count(),
                'filleuls' => $filleuls,
            ];
        }

        // Afficher le résumé
        $this->table(
            ['ID', 'Parrain', 'team_pv actuel', 'team_pv a ajouter', 'Filleuls', 'Grade actuel'],
            array_map(function ($item) {
                return [
                    $item['parrain']->id,
                    substr($item['parrain']->name, 0, 20),
                    number_format($item['team_pv_actuel'], 1),
                    number_format($item['team_pv_a_ajouter'], 1),
                    $item['total_team'],
                    $item['parrain']->rank ?? 'Distributeur',
                ];
            }, $data)
        );

        $this->newLine();

        if ($dryRun) {
            $totalPV = array_sum(array_column($data, 'team_pv_a_ajouter'));
            $this->info("Total PV d'equipe a distribuer: " . number_format($totalPV, 1));
            $this->warn('Simulation terminee. Aucune modification.');
            return 0;
        }

        $totalPV = array_sum(array_column($data, 'team_pv_a_ajouter'));
        $this->info("Total PV d'equipe a distribuer: " . number_format($totalPV, 1));

        if (!$force && !$this->confirm('Voulez-vous corriger le team_pv de ces parrains ?')) {
            $this->info('Operation annulee.');
            return 0;
        }

        // Appliquer les corrections
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        $updated = 0;
        $totalPVAdded = 0;

        foreach ($data as $item) {
            $parrain = $item['parrain'];
            $pvToAdd = $item['team_pv_a_ajouter'];
            $bvToAdd = $item['team_bv_a_ajouter'];

            if ($pvToAdd <= 0) {
                $bar->advance();
                continue;
            }

            DB::beginTransaction();
            try {
                $oldTeamPV = $parrain->team_pv;
                $oldRank = $parrain->rank ?? 'Distributeur';
                $oldRankLevel = $parrain->rank_level ?? 1;

                // Ajouter au team_pv
                $parrain->team_pv = ($parrain->team_pv ?? 0) + $pvToAdd;
                $parrain->team_bv = ($parrain->team_bv ?? 0) + $bvToAdd;
                $parrain->total_team = User::where('parrain_id', $parrain->id)
                    ->where('is_active', true)
                    ->count();
                $parrain->saveQuietly();

                // Recalculer le grade
                $this->rankCalculator->recalculateUserRank($parrain, 'Correction team_pv historique');

                DB::commit();

                $updated++;
                $totalPVAdded += $pvToAdd;

                $this->line("\nOK: {$parrain->name} + " . number_format($pvToAdd, 1) . " PV d'equipe");

                Log::info('team_pv corrige pour donnees historiques', [
                    'parrain_id' => $parrain->id,
                    'parrain_name' => $parrain->name,
                    'team_pv_added' => $pvToAdd,
                    'old_team_pv' => $oldTeamPV,
                    'new_team_pv' => $parrain->team_pv,
                    'old_rank' => $oldRank,
                    'new_rank' => $parrain->rank ?? 'Distributeur',
                    'old_level' => $oldRankLevel,
                    'new_level' => $parrain->rank_level ?? 1,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur correction team_pv', [
                    'parrain_id' => $parrain->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Erreur pour {$parrain->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('=== RESULTATS ===');
        $this->info("Parrains mis a jour: {$updated}");
        $this->info("Total PV d'equipe ajoutes: " . number_format($totalPVAdded, 1));
        $this->newLine();

        $this->info('=== FIN ===');
        return 0;
    }
}