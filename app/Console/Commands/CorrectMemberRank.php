<?php

namespace App\Console\Commands;

use App\Models\RankHistory;
use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Correction ciblée / idempotente d'un membre (sponsor_id).
 *
 * Cas typique : rank4_grandfathered=true alors que les critères niv.4
 * ne sont plus (ou n'étaient pas) remplis → retirer le flag puis recalculer.
 */
class CorrectMemberRank extends Command
{
    protected $signature = 'mlm:correct-member-rank
                            {sponsor_id : Code membre (users.sponsor_id)}
                            {--clear-rank4-grandfathered : Retire rank4_grandfathered}
                            {--recalculate : Recalcule le grade via AdvancedRankCalculator}
                            {--dry-run : Affiche sans écrire}';

    protected $description = 'Corrige le grade d’un membre (idempotent) : optionnellement retire rank4_grandfathered puis recalcule';

    public function handle(AdvancedRankCalculator $rankCalculator): int
    {
        $sponsorId = (string) $this->argument('sponsor_id');
        $dryRun = (bool) $this->option('dry-run');
        $clearGf = (bool) $this->option('clear-rank4-grandfathered');
        $recalculate = (bool) $this->option('recalculate');

        if (! $clearGf && ! $recalculate) {
            $this->error('Indiquer au moins --clear-rank4-grandfathered et/ou --recalculate.');

            return self::FAILURE;
        }

        $user = User::query()->where('sponsor_id', $sponsorId)->first();
        if (! $user) {
            $this->error("Membre introuvable pour sponsor_id={$sponsorId}");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Membre %s (#%d) — grade actuel L%d (%s), rank4_grandfathered=%s, pv=%s, team_pv=%s',
            $user->sponsor_id,
            $user->id,
            (int) $user->rank_level,
            $user->rank ?? '—',
            $user->rank4_grandfathered ? 'true' : 'false',
            $user->pv_balance,
            $user->team_pv
        ));

        $changed = false;

        if ($clearGf && $user->rank4_grandfathered) {
            $this->warn('→ clear rank4_grandfathered');
            if (! $dryRun) {
                $user->rank4_grandfathered = false;
                $user->saveQuietly();
            }
            $changed = true;
        } elseif ($clearGf) {
            $this->line('rank4_grandfathered déjà false — noop');
        }

        if ($recalculate) {
            $user->refresh();
            // En dry-run, simuler sans GF si on allait le clear.
            if ($dryRun && $clearGf) {
                $user->rank4_grandfathered = false;
            }

            Cache::forget("rank_calculation_{$user->id}");
            Cache::forget("user_rank_{$user->id}");
            $rankCalculator->clearCache();

            $newRank = $rankCalculator->calculateAdvancedRank($user);
            if (! $newRank) {
                $this->error('Recalcul impossible (utilisateur inactif ?).');

                return self::FAILURE;
            }

            $needsUpdate = (
                (int) $newRank->id !== (int) $user->rank_id
                || (string) $newRank->name !== (string) ($user->rank ?? '')
                || (int) $newRank->level !== (int) ($user->rank_level ?? 0)
            );

            $this->info(sprintf(
                '→ recalcul → L%d (%s) %s',
                (int) $newRank->level,
                $newRank->name,
                $needsUpdate ? '[changement]' : '[déjà à jour]'
            ));

            if ($needsUpdate) {
                $changed = true;
                if (! $dryRun) {
                    DB::transaction(function () use ($user, $newRank) {
                        $oldRankId = $user->rank_id;
                        $oldRankName = $user->rank ?? 'Distributeur';
                        $oldRankLevel = $user->rank_level ?? 1;

                        $user->rank_id = $newRank->id;
                        $user->rank = $newRank->name;
                        $user->rank_level = $newRank->level;
                        $user->last_rank_update = now();
                        $user->saveQuietly();

                        RankHistory::create([
                            'user_id' => $user->id,
                            'old_rank_id' => $oldRankId,
                            'new_rank_id' => $newRank->id,
                            'old_rank_name' => $oldRankName,
                            'new_rank_name' => $newRank->name,
                            'pv_at_time' => $user->pv_balance ?? 0,
                            'bv_at_time' => $user->bv_balance ?? 0,
                            'notes' => sprintf(
                                'mlm:correct-member-rank (L%d -> L%d)',
                                (int) $oldRankLevel,
                                (int) $newRank->level
                            ),
                        ]);
                    });
                }
            }
        }

        if ($dryRun) {
            $this->warn('Dry-run : aucune écriture.');
        } elseif (! $changed) {
            $this->info('Déjà conforme — aucune écriture.');
        } else {
            $user->refresh();
            $this->info(sprintf(
                'OK — grade final L%d (%s), rank4_grandfathered=%s',
                (int) $user->rank_level,
                $user->rank ?? '—',
                $user->rank4_grandfathered ? 'true' : 'false'
            ));
        }

        return self::SUCCESS;
    }
}
