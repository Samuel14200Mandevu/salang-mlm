<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixSponsorPV extends Command
{
    protected $signature = 'sponsor:fix-pv 
                            {--user= : ID du parrain specifique}
                            {--dry-run : Simulation sans modification}
                            {--force : Forcer sans confirmation}';
    
    protected $description = 'Corrige les PV des parrains pour les donnees historiques (ajoute au team_pv)';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== CORRECTION DES PV DES PARRAINS (HISTORIQUE) ===');
        $this->newLine();

        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification');
            $this->newLine();
        }

        // Trouver le parrain
        if ($userId) {
            $parrain = User::where('id', $userId)->where('is_active', true)->first();
            if (!$parrain) {
                $this->error("Utilisateur ID {$userId} non trouve ou inactif");
                return 1;
            }
            $parrains = collect([$parrain]);
            $this->info("Correction pour le parrain: {$parrain->name} (ID: {$parrain->id})");
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
            $this->warn('Aucun parrain a corriger');
            return 0;
        }

        // Afficher les détails
        $this->newLine();
        $this->table(
            ['ID', 'Parrain', 'PV Personnel', 'team_pv actuel', 'Filleuls avec PV', 'Grade'],
            $parrains->map(function ($p) {
                $filleulsCount = User::where('parrain_id', $p->id)
                    ->where('is_active', true)
                    ->where('pv_balance', '>', 0)
                    ->count();
                return [
                    $p->id,
                    substr($p->name, 0, 20),
                    number_format($p->pv_balance ?? 0, 1),
                    number_format($p->team_pv ?? 0, 1),
                    $filleulsCount,
                    $p->rank ?? 'Distributeur',
                ];
            })->toArray()
        );

        $this->newLine();

        if ($dryRun) {
            // Calculer le total à ajouter
            $totalPV = 0;
            foreach ($parrains as $p) {
                $filleulsPV = User::where('parrain_id', $p->id)
                    ->where('is_active', true)
                    ->where('pv_balance', '>', 0)
                    ->sum('pv_balance');
                $totalPV += $filleulsPV;
            }
            $this->info("Total PV d'equipe a ajouter: " . number_format($totalPV, 1));
            $this->warn('Simulation terminee. Aucune modification.');
            return 0;
        }

        if (!$force && !$this->confirm('Voulez-vous corriger ces parrains ?')) {
            $this->info('Operation annulee.');
            return 0;
        }

        $bar = $this->output->createProgressBar($parrains->count());
        $bar->start();

        $updated = 0;
        $totalPVAdded = 0;

        foreach ($parrains as $parrain) {
            $filleulsPV = User::where('parrain_id', $parrain->id)
                ->where('is_active', true)
                ->where('pv_balance', '>', 0)
                ->sum('pv_balance');

            if ($filleulsPV <= 0) {
                $bar->advance();
                continue;
            }

            DB::beginTransaction();
            try {
                $oldTeamPV = $parrain->team_pv;
                $oldRank = $parrain->rank ?? 'Distributeur';

                $parrain->team_pv = ($parrain->team_pv ?? 0) + $filleulsPV;
                $parrain->team_bv = ($parrain->team_bv ?? 0) + ($filleulsPV * 0.8);
                $parrain->total_team = User::where('parrain_id', $parrain->id)
                    ->where('is_active', true)
                    ->count();
                $parrain->saveQuietly();

                $this->rankCalculator->recalculateUserRank($parrain, 'Correction PV historique');

                DB::commit();

                $updated++;
                $totalPVAdded += $filleulsPV;

                Log::info('PV parrain corriges (historique)', [
                    'parrain_id' => $parrain->id,
                    'parrain_name' => $parrain->name,
                    'team_pv_added' => $filleulsPV,
                    'old_team_pv' => $oldTeamPV,
                    'new_team_pv' => $parrain->team_pv,
                    'old_rank' => $oldRank,
                    'new_rank' => $parrain->rank ?? 'Distributeur',
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur correction PV parrain', [
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
        $this->info('=== FIN ===');

        return 0;
    }
}