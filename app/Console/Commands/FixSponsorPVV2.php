<?php
// app/Console/Commands/FixSponsorPVV2.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Package;
use App\Models\PVHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixSponsorPVV2 extends Command
{
    protected $signature = 'sponsor:fix-pv-v2 
                            {--user= : ID de l\'utilisateur specifique (parrain)}
                            {--dry-run : Simulation sans modification}
                            {--all : Corriger tous les parrains}';
    
    protected $description = 'Corrige les PV manquants des parrains (version corrigee)';

    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        parent::__construct();
        $this->rankCalculator = $rankCalculator;
    }

    public function handle()
    {
        $this->info('=== CORRECTION DES PV DES PARRAINS (V2) ===');
        $this->newLine();

        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $all = $this->option('all');

        if ($dryRun) {
            $this->warn('MODE SIMULATION - Aucune modification ne sera effectuee');
            $this->newLine();
        }

        // Construire la requête
        if ($userId) {
            $parrains = User::where('id', $userId)->where('is_active', true)->get();
            if ($parrains->isEmpty()) {
                $this->error("Utilisateur ID {$userId} non trouve ou inactif");
                return 1;
            }
            $this->info("Correction pour un parrain specifique: ID {$userId}");
        } elseif ($all) {
            $parrains = User::where('is_active', true)->get();
            $this->info("Correction pour tous les parrains actifs (" . $parrains->count() . " utilisateurs)");
        } else {
            $parrains = User::where('is_active', true)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users as filleuls')
                        ->whereColumn('filleuls.parrain_id', 'users.id')
                        ->where('filleuls.is_active', true);
                })
                ->get();
            $this->info("Correction pour les parrains ayant des filleuls (" . $parrains->count() . " parrains)");
        }

        if ($parrains->isEmpty()) {
            $this->warn('Aucun parrain a corriger');
            return 0;
        }

        $this->newLine();

        $bar = $this->output->createProgressBar($parrains->count());
        $bar->start();

        $totalFixed = 0;
        $totalPV = 0;
        $results = [];
        $errors = [];

        foreach ($parrains as $parrain) {
            try {
                // Récupérer les filleuls actifs de ce parrain
                $filleuls = User::where('parrain_id', $parrain->id)
                    ->where('is_active', true)
                    ->whereNotNull('activation_package_id')
                    ->get();

                if ($filleuls->isEmpty()) {
                    $bar->advance();
                    continue;
                }

                $pvToAdd = 0;
                $bvToAdd = 0;
                $filleulsDetails = [];

                foreach ($filleuls as $filleul) {
                    $package = Package::find($filleul->activation_package_id);
                    if ($package) {
                        $pv = $package->pv_value * 0.10; // 10%
                        $bv = $package->bv_value * 0.10;
                        
                        // NE PAS vérifier l'historique - on ajoute tout
                        $pvToAdd += $pv;
                        $bvToAdd += $bv;
                        $filleulsDetails[] = [
                            'id' => $filleul->id,
                            'name' => $filleul->name,
                            'package' => $package->name,
                            'pv' => $pv,
                        ];
                    }
                }

                if ($pvToAdd > 0) {
                    if (!$dryRun) {
                        DB::beginTransaction();
                        try {
                            $oldPV = $parrain->pv_balance;
                            $oldRank = $parrain->rank ?? 'Distributeur';

                            // Ajouter les PV
                            $parrain->pv_balance += $pvToAdd;
                            $parrain->monthly_pv += $pvToAdd;
                            $parrain->bv_balance += $bvToAdd;
                            $parrain->monthly_bv += $bvToAdd;
                            $parrain->saveQuietly();

                            // Créer l'historique
                            foreach ($filleulsDetails as $detail) {
                                PVHistory::create([
                                    'user_id' => $parrain->id,
                                    'amount' => $detail['pv'],
                                    'date' => now(),
                                    'period' => date('Y-m'),
                                    'type' => 'sponsor_commission',
                                    'notes' => "Correction: PV parrainage de {$detail['name']} (ID: {$detail['id']}) - Package: {$detail['package']}",
                                    'created_by' => $detail['id'],
                                ]);
                            }

                            // Recalculer le grade du parrain
                            $this->rankCalculator->recalculateUserRank($parrain, 'Correction PV parrain');

                            DB::commit();

                            $totalFixed++;
                            $totalPV += $pvToAdd;

                            $results[] = [
                                'parrain_id' => $parrain->id,
                                'parrain_name' => $parrain->name,
                                'old_pv' => $oldPV,
                                'new_pv' => $parrain->pv_balance,
                                'pv_added' => $pvToAdd,
                                'old_rank' => $oldRank,
                                'new_rank' => $parrain->rank ?? 'Distributeur',
                                'filleuls_count' => count($filleulsDetails),
                            ];

                            Log::info('PV parrain corriges (V2)', [
                                'parrain_id' => $parrain->id,
                                'parrain_name' => $parrain->name,
                                'pv_added' => $pvToAdd,
                                'filleuls_count' => count($filleulsDetails),
                            ]);

                        } catch (\Exception $e) {
                            DB::rollBack();
                            $errors[] = "Erreur pour {$parrain->name} (ID: {$parrain->id}): " . $e->getMessage();
                            Log::error('Erreur correction PV parrain', [
                                'parrain_id' => $parrain->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        // Mode simulation
                        $results[] = [
                            'parrain_id' => $parrain->id,
                            'parrain_name' => $parrain->name,
                            'old_pv' => $parrain->pv_balance,
                            'new_pv' => $parrain->pv_balance + $pvToAdd,
                            'pv_added' => $pvToAdd,
                            'old_rank' => $parrain->rank ?? 'Distributeur',
                            'new_rank' => 'A calculer',
                            'filleuls_count' => count($filleulsDetails),
                        ];
                        $totalPV += $pvToAdd;
                    }
                }

                $bar->advance();

            } catch (\Exception $e) {
                $errors[] = "Erreur pour {$parrain->name} (ID: {$parrain->id}): " . $e->getMessage();
                Log::error('Erreur dans la boucle de correction', [
                    'parrain_id' => $parrain->id,
                    'error' => $e->getMessage(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        if (!empty($results)) {
            $this->info('=== RESULTATS ===');
            $this->newLine();

            $headers = ['ID', 'Parrain', 'PV Avant', 'PV Ajoutes', 'PV Apres', 'Filleuls', 'Ancien Grade', 'Nouveau Grade'];
            $rows = [];
            
            foreach ($results as $result) {
                $rows[] = [
                    $result['parrain_id'],
                    substr($result['parrain_name'], 0, 20),
                    number_format($result['old_pv'] ?? 0, 1),
                    number_format($result['pv_added'], 1),
                    number_format($result['new_pv'] ?? $result['old_pv'] ?? 0, 1),
                    $result['filleuls_count'],
                    $result['old_rank'] ?? 'Distributeur',
                    $result['new_rank'] ?? 'Distributeur',
                ];
            }

            $this->table($headers, $rows);

            $this->newLine();
            $this->info("Total PV distribues: " . number_format($totalPV, 1));

            if ($dryRun) {
                $this->warn("Simulation terminee. " . count($results) . " parrains seraient mis a jour.");
            } else {
                $this->info("Correction terminee. {$totalFixed} parrains mis a jour sur " . count($results) . " detectes.");
            }
        } else {
            $this->info('Aucun parrain a corriger');
        }

        if (!empty($errors)) {
            $this->newLine();
            $this->warn('=== ERREURS ===');
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
        }

        $this->newLine();
        $this->info('=== FIN ===');

        return 0;
    }
}