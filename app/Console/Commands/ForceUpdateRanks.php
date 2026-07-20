<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceUpdateRanks extends Command
{
    protected $signature = 'ranks:force-update 
                            {--user= : ID de l\'utilisateur spécifique}
                            {--full : Recalcul complet (PV, TeamPV, etc.)}
                            {--force : Forcer même si aucun changement}';
    
    protected $description = 'Force le calcul des grades pour tous les utilisateurs';

    public function handle(AdvancedRankCalculator $rankCalculator)
    {
        $this->output->writeln('[INFO] Force Update Ranks');
        $this->output->writeln('');

        $full = $this->option('full');
        $force = $this->option('force');
        $userId = $this->option('user');

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->output->writeln("[USER] Specific user: ID {$userId}");
        } else {
            $this->output->writeln("[USER] All active users");
        }

        $users = $query->get();
        $this->output->writeln("[COUNT] {$users->count()} users to process");
        $this->output->writeln('');

        if ($full) {
            $this->output->writeln('[WARN] Full mode: Recalculating all PV, TeamPV and Ranks');
            $this->output->writeln('');
        }

        $updated = 0;
        $errors = [];
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                if ($this->processUser($user, $rankCalculator, $full, $force)) {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "User {$user->id} ({$user->name}): " . $e->getMessage();
                $this->output->writeln("\n[ERROR] For {$user->name}: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->output->writeln('');
        $this->output->writeln('');

        $this->output->writeln('[SUMMARY]');
        $this->output->writeln("   [OK] Processed: {$users->count()}");
        $this->output->writeln("   [OK] Updated: {$updated}");
        $this->output->writeln("   [ERROR] Errors: " . count($errors));

        if (!empty($errors) && count($errors) <= 5) {
            $this->output->writeln('');
            $this->output->writeln('[DETAILS]');
            foreach ($errors as $error) {
                $this->output->writeln("   - {$error}");
            }
        } elseif (!empty($errors)) {
            $this->output->writeln('');
            $this->output->writeln("[DETAILS] " . count($errors) . " errors. Check logs for more details.");
        }

        return 0;
    }

    private function processUser(User $user, AdvancedRankCalculator $rankCalculator, bool $full, bool $force): bool
    {
        Cache::forget("user_rank_{$user->id}");
        Cache::forget("rank_calculation_{$user->id}");
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");

        if ($full) {
            $this->recalculateFull($user);
        }

        $user->updateTeamPVWithoutEvents();
        $user->updateAllAncestorsWithoutEvents();
        $user->updateMonthlyPV();

        $oldRankId = $user->rank_id;
        $oldRankName = $user->rank ?? 'Distributeur';
        $oldRankLevel = $user->rank_level ?? 1;

        $newRank = $rankCalculator->calculateAdvancedRank($user);

        if (!$newRank) {
            throw new \Exception('No rank found');
        }

        if ($newRank->id != $oldRankId || $force) {
            DB::beginTransaction();

            $user->rank_id = $newRank->id;
            $user->rank = $newRank->name;
            $user->rank_level = $newRank->level;
            $user->last_rank_update = now();
            $user->saveQuietly();

            // Créer l'historique si la table existe
            if (class_exists('App\Models\RankHistory') && (new \App\Models\RankHistory())->getTable()) {
                try {
                    RankHistory::create([
                        'user_id' => $user->id,
                        'old_rank_id' => $oldRankId,
                        'new_rank_id' => $newRank->id,
                        'old_rank_name' => $oldRankName,
                        'new_rank_name' => $newRank->name,
                        'old_rank_level' => $oldRankLevel,
                        'new_rank_level' => $newRank->level,
                        'pv_at_time' => $user->pv_balance,
                        'bv_at_time' => $user->bv_balance,
                        'monthly_pv_at_time' => $user->monthly_pv,
                        'notes' => 'Forced update - ' . now()->format('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                    // La table n'existe pas encore, on ignore
                }
            }

            DB::commit();

            Cache::forget("user_rank_{$user->id}");

            $this->updateQualifiedBranches($user);
            $this->updateHigherRanks($user);

            if ($newRank->id != $oldRankId) {
                $this->updateAncestors($user, $rankCalculator);
            }

            if ($newRank->id != $oldRankId) {
                Log::info('Rank updated via force command', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                    'old_level' => $oldRankLevel,
                    'new_level' => $newRank->level,
                ]);
            }

            return true;
        }

        if ($force && $newRank->id == $oldRankId) {
            Log::info('Rank forced update (no change)', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'rank' => $newRank->name,
            ]);
        }

        return false;
    }

    private function recalculateFull(User $user): void
    {
        $totalPV = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.payment_status', 'completed')
            ->sum('order_items.pv_value');

        $totalBV = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->where('orders.payment_status', 'completed')
            ->sum('order_items.bv_value');

        $user->pv_balance = (int) $totalPV;
        $user->bv_balance = (int) $totalBV;
        $user->saveQuietly();

        $user->updateMonthlyPV();

        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");
    }

    private function updateQualifiedBranches(User $user): void
    {
        try {
            $calculator = app(\App\Services\MLM\AdvancedRankCalculator::class);
            $period = date('Y-m');
            $calculator->calculateQualifiedBranches($user, $period);
        } catch (\Exception $e) {
            // Non bloquant
        }
    }

    private function updateHigherRanks(User $user): void
    {
        try {
            if (method_exists($user, 'higherRanks')) {
                $calculator = app(\App\Services\MLM\AdvancedRankCalculator::class);
                $period = date('Y-m');
                $calculator->checkHigherRankEligibility($user, $period);
            }
        } catch (\Exception $e) {
            // Non bloquant
        }
    }

    private function updateAncestors(User $user, AdvancedRankCalculator $rankCalculator): void
    {
        $current = $user->parrain;
        $depth = 0;
        $maxDepth = 5;
        $processed = [];

        while ($current && $depth < $maxDepth && !in_array($current->id, $processed)) {
            $processed[] = $current->id;

            try {
                Cache::forget("user_rank_{$current->id}");
                Cache::forget("rank_calculation_{$current->id}");

                $current->updateTeamPVWithoutEvents();

                $newRank = $rankCalculator->calculateAdvancedRank($current);

                if ($newRank && $newRank->id != $current->rank_id) {
                    $oldRankName = $current->rank ?? 'Distributeur';
                    
                    $current->rank_id = $newRank->id;
                    $current->rank = $newRank->name;
                    $current->rank_level = $newRank->level;
                    $current->last_rank_update = now();
                    $current->saveQuietly();

                    // Créer l'historique si la table existe
                    if (class_exists('App\Models\RankHistory')) {
                        try {
                            RankHistory::create([
                                'user_id' => $current->id,
                                'old_rank_id' => $current->getOriginal('rank_id'),
                                'new_rank_id' => $newRank->id,
                                'old_rank_name' => $oldRankName,
                                'new_rank_name' => $newRank->name,
                                'notes' => 'Updated via ancestor propagation - ' . now()->format('Y-m-d H:i:s'),
                            ]);
                        } catch (\Exception $e) {
                            // La table n'existe pas encore, on ignore
                        }
                    }

                    Log::info('Ancestor rank updated via force command', [
                        'ancestor_id' => $current->id,
                        'ancestor_name' => $current->name,
                        'old_rank' => $oldRankName,
                        'new_rank' => $newRank->name,
                    ]);
                }

                Cache::forget("user_rank_{$current->id}");

            } catch (\Exception $e) {
                Log::warning('Error updating ancestor', [
                    'ancestor_id' => $current->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $current = $current->parrain;
            $depth++;
        }
    }
}