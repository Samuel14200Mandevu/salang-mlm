<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\TeamPVCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTeamPVInconsistencies extends Command
{
    protected $signature = 'mlm:fix-team-pv 
                            {--dry-run : Simulation uniquement}
                            {--skip-cycle-check : Ignorer la vérification des cycles}
                            {--fix-cycles : Corriger automatiquement les cycles avant}';

    protected $description = 'Répare les incohérences de team_pv / rank / rank_level';

    public function handle(TeamPVCalculator $calculator): int
    {
        $dryRun = $this->option('dry-run');
        $skipCycleCheck = $this->option('skip-cycle-check');
        $fixCycles = $this->option('fix-cycles');

        $this->info('🔍 Analyse des incohérences...');
        $this->newLine();

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 0 : DÉTECTION DES CYCLES ET ORPHELINS
        // ═══════════════════════════════════════════════════════════
        if (!$skipCycleCheck) {
            $cycles = $calculator->detectCycles();
            $orphans = $calculator->detectOrphans();

            if (!empty($cycles)) {
                $this->error("⚠️  " . count($cycles) . " CYCLE(S) DÉTECTÉ(S) dans la hiérarchie :");
                $this->table(
                    ['User ID', 'Nom', 'Parrain ID', 'Parrain nom'],
                    array_map(fn($c) => [
                        $c['user_id'],
                        $c['user_name'],
                        $c['parrain_id'],
                        $c['parrain_name'],
                    ], $cycles)
                );

                if ($fixCycles && !$dryRun) {
                    $this->warn("🔧 Correction automatique des cycles...");
                    $fixed = 0;
                    foreach ($cycles as $cycle) {
                        DB::table('users')
                            ->where('id', $cycle['user_id'])
                            ->update(['parrain_id' => null]);
                        $fixed++;
                    }
                    $this->info("✅ {$fixed} cycle(s) cassé(s)");
                } elseif (!$dryRun) {
                    $this->error("🛑 Arrêt : corrigez les cycles d'abord ou utilisez --fix-cycles");
                    return Command::FAILURE;
                }
            } else {
                $this->info("✅ Aucun cycle détecté");
            }

            if (!empty($orphans)) {
                $this->warn("⚠️  " . count($orphans) . " ORPHELIN(S) détecté(s) :");
                $this->table(
                    ['User ID', 'Nom', 'Parrain ID'],
                    array_map(fn($o) => [
                        $o['user_id'],
                        $o['user_name'],
                        $o['parrain_id'],
                    ], $orphans)
                );

                if ($fixCycles && !$dryRun) {
                    DB::table('users')
                        ->whereIn('id', array_column($orphans, 'user_id'))
                        ->update(['parrain_id' => null]);
                    $this->info("✅ " . count($orphans) . " orphelin(s) rendu(s) racine(s)");
                }
            } else {
                $this->info("✅ Aucun orphelin détecté");
            }

            $this->newLine();
        }

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 1 : RANK INCOHÉRENTS
        // ═══════════════════════════════════════════════════════════
        $incoherentRanks = DB::table('users')
            ->join('ranks', 'users.rank_id', '=', 'ranks.id')
            ->whereColumn('users.rank_level', '!=', 'ranks.level')
            ->select(
                'users.id',
                'users.name',
                'users.rank',
                'users.rank_id',
                'users.rank_level',
                'ranks.level as real_level'
            )
            ->get();

        if ($incoherentRanks->isNotEmpty()) {
            $this->warn("⚠️  " . $incoherentRanks->count() . " utilisateurs avec rank_id/rank_level incohérents :");
            $this->table(
                ['ID', 'Nom', 'Rank', 'rank_id', 'rank_level', 'Niveau réel'],
                $incoherentRanks->take(20)->map(fn($u) => [
                    $u->id, $u->name, $u->rank, $u->rank_id, $u->rank_level, $u->real_level,
                ])->toArray()
            );
            if ($incoherentRanks->count() > 20) {
                $this->info("... et " . ($incoherentRanks->count() - 20) . " autres");
            }

            if (!$dryRun) {
                foreach ($incoherentRanks as $u) {
                    DB::table('users')->where('id', $u->id)->update([
                        'rank_level' => $u->real_level,
                        'updated_at' => now(),
                    ]);
                }
                $this->info("✅ rank_level corrigés (" . $incoherentRanks->count() . " utilisateurs)");
            }
        } else {
            $this->info("✅ Aucune incohérence rank_id/rank_level détectée");
        }

        // ═══════════════════════════════════════════════════════════
        // ÉTAPE 2 : RECALCUL DU TEAM_PV
        // ═══════════════════════════════════════════════════════════
        $this->newLine();
        $this->info('🔄 Recalcul de team_pv pour tous les users actifs...');

        $total = User::where('is_active', true)->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $fixed = 0;
        $errors = 0;

        User::where('is_active', true)
            ->chunk(100, function ($users) use ($calculator, $dryRun, &$fixed, &$errors, $bar) {
                foreach ($users as $user) {
                    try {
                        $expected = $calculator->calculateForUser($user);

                        $needsFix = (
                            abs(($user->team_pv ?? 0) - $expected['pv']) > 0.01 ||
                            abs(($user->team_bv ?? 0) - $expected['bv']) > 0.01 ||
                            ($user->total_team ?? 0) !== $expected['total']
                        );

                        if ($needsFix) {
                            if (!$dryRun) {
                                $user->team_pv = $expected['pv'];
                                $user->team_bv = $expected['bv'];
                                $user->total_team = $expected['total'];
                                $user->saveQuietly();
                            }
                            $fixed++;
                        }
                        $bar->advance();
                    } catch (\Exception $e) {
                        $errors++;
                        $bar->advance();
                    }
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$fixed} utilisateurs " . ($dryRun ? 'à corriger' : 'corrigés'));
        $this->info("Total scanné : {$total}");
        if ($errors > 0) {
            $this->warn("⚠️  {$errors} erreurs (voir logs)");
        }

        if ($dryRun) {
            $this->warn("🔒 Mode dry-run : aucune modification en base");
            $this->info("Relancez sans --dry-run pour appliquer");
        }

        return Command::SUCCESS;
    }
}