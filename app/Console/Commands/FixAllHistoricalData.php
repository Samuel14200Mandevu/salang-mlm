<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAllHistoricalData extends Command
{
    protected $signature = 'historical:fix-all 
                            {--dry-run : Simulation sans modification}
                            {--force : Forcer sans confirmation}';
    
    protected $description = 'Corrige toutes les donnees historiques (team_pv et grades)';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== CORRECTION COMPLETE DES DONNEES HISTORIQUES ===');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification');
            $this->newLine();
        }

        // 1. Trouver tous les utilisateurs qui ont des filleuls
        $parrains = User::where('is_active', true)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users as filleuls')
                    ->whereColumn('filleuls.parrain_id', 'users.id')
                    ->where('filleuls.is_active', true);
            })
            ->get();

        if ($parrains->isEmpty()) {
            $this->warn('Aucun parrain trouve');
            return 0;
        }

        $this->info("Parrains trouves: " . $parrains->count());
        $this->newLine();

        // 2. Collecter les données
        $data = [];
        $totalPV = 0;

        foreach ($parrains as $parrain) {
            $filleuls = User::where('parrain_id', $parrain->id)
                ->where('is_active', true)
                ->get();

            if ($filleuls->isEmpty()) {
                continue;
            }

            $teamPV = $filleuls->sum('pv_balance');
            $teamBV = $filleuls->sum('bv_balance');
            $totalTeam = $filleuls->count();

            $data[] = [
                'parrain' => $parrain,
                'team_pv_actuel' => $parrain->team_pv ?? 0,
                'team_pv_calcule' => $teamPV,
                'team_pv_a_ajouter' => $teamPV - ($parrain->team_pv ?? 0),
                'team_bv_a_ajouter' => $teamBV - ($parrain->team_bv ?? 0),
                'total_team' => $totalTeam,
            ];

            $totalPV += max(0, $teamPV - ($parrain->team_pv ?? 0));
        }

        // Filtrer ceux qui ont des PV à ajouter
        $data = array_filter($data, function ($item) {
            return $item['team_pv_a_ajouter'] > 0;
        });

        if (empty($data)) {
            $this->info('Toutes les donnees sont deja correctes.');
            return 0;
        }

        $this->info("Parrains a corriger: " . count($data));
        $this->info("Total PV d'equipe a ajouter: " . number_format($totalPV, 1));
        $this->newLine();

        // Afficher le résumé
        $this->table(
            ['ID', 'Parrain', 'team_pv actuel', 'team_pv calcule', 'A ajouter', 'Filleuls', 'Grade'],
            array_map(function ($item) {
                return [
                    $item['parrain']->id,
                    substr($item['parrain']->name, 0, 20),
                    number_format($item['team_pv_actuel'], 1),
                    number_format($item['team_pv_calcule'], 1),
                    number_format($item['team_pv_a_ajouter'], 1),
                    $item['total_team'],
                    $item['parrain']->rank ?? 'Distributeur',
                ];
            }, $data)
        );

        $this->newLine();

        if ($dryRun) {
            $this->warn('Simulation terminee. Aucune modification.');
            return 0;
        }

        if (!$force && !$this->confirm('Voulez-vous corriger ces ' . count($data) . ' parrains ?')) {
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

            DB::beginTransaction();
            try {
                $oldTeamPV = $parrain->team_pv;
                $oldRank = $parrain->rank ?? 'Distributeur';

                $parrain->team_pv = $item['team_pv_calcule'];
                $parrain->team_bv = $item['team_pv_calcule'] * 0.8;
                $parrain->total_team = $item['total_team'];
                $parrain->saveQuietly();

                $this->rankCalculator->recalculateUserRank($parrain, 'Correction historique complete');

                DB::commit();

                $updated++;
                $totalPVAdded += $pvToAdd;

                Log::info('Donnees historiques corrigees', [
                    'parrain_id' => $parrain->id,
                    'parrain_name' => $parrain->name,
                    'team_pv_added' => $pvToAdd,
                    'old_team_pv' => $oldTeamPV,
                    'new_team_pv' => $parrain->team_pv,
                    'old_rank' => $oldRank,
                    'new_rank' => $parrain->rank ?? 'Distributeur',
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur correction historique complete', [
                    'parrain_id' => $parrain->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('=== RESULTATS ===');
        $this->info("Parrains mis a jour: {$updated}");
        $this->info("Total PV d'equipe ajoutes: " . number_format($totalPVAdded, 1));
        $this->newLine();

        // Afficher le résumé final
        $this->table(
            ['ID', 'Parrain', 'Nouveau team_pv', 'Nouveau grade', 'Niveau'],
            array_map(function ($item) {
                return [
                    $item['parrain']->id,
                    substr($item['parrain']->name, 0, 20),
                    number_format($item['parrain']->team_pv, 1),
                    $item['parrain']->rank ?? 'Distributeur',
                    $item['parrain']->rank_level ?? 1,
                ];
            }, $data)
        );

        $this->newLine();
        $this->info('=== FIN ===');

        return 0;
    }
}