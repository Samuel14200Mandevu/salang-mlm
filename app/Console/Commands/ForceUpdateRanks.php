<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\RankUpdateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceUpdateRanks extends Command
{
    protected $signature = 'ranks:force-update 
                            {--user= : ID de l utilisateur specifique}
                            {--full : Recalcul complet (PV, TeamPV, etc.)}
                            {--force : Forcer meme si aucun changement}';
    
    protected $description = 'Force le calcul des grades pour tous les utilisateurs';

    public function handle(AdvancedRankCalculator $rankCalculator, RankUpdateService $rankService)
    {
        $this->info('Force Update Ranks');
        $this->newLine();

        $full = $this->option('full');
        $force = $this->option('force');
        $userId = $this->option('user');

        $query = User::where('is_active', true);

        if ($userId) {
            $query->where('id', $userId);
            $this->info("Specific user: ID {$userId}");
        } else {
            $this->info("All active users");
        }

        $users = $query->get();
        $this->info($users->count() . " users to process");
        $this->newLine();

        if ($full) {
            $this->warn('Full mode: Recalculating all PV, TeamPV and Ranks');
            $this->newLine();
        }

        $updated = 0;
        $errors = [];
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                if ($this->processUser($user, $rankCalculator, $rankService, $full, $force)) {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors[] = "User {$user->id} ({$user->name}): " . $e->getMessage();
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('SUMMARY');
        $this->line("   Processed: {$users->count()}");
        $this->line("   Updated: {$updated}");
        $this->line("   Errors: " . count($errors));

        if (!empty($errors) && count($errors) <= 5) {
            $this->newLine();
            $this->info('DETAILS');
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
        } elseif (!empty($errors)) {
            $this->newLine();
            $this->warn(count($errors) . " errors. Check logs for more details.");
        }

        return 0;
    }

    private function processUser(User $user, AdvancedRankCalculator $rankCalculator, RankUpdateService $rankService, bool $full, bool $force): bool
    {
        Cache::forget("user_rank_{$user->id}");
        Cache::forget("rank_calculation_{$user->id}");
        Cache::forget("descendants_{$user->id}");
        Cache::forget("descendants_count_{$user->id}");

        if ($full) {
            $this->recalculateFull($user);
        }

        $user->updateTeamPVOptimized();
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

            if (class_exists('App\Models\RankHistory')) {
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
                    // Table n'existe pas encore
                }
            }

            DB::commit();

            Cache::forget("user_rank_{$user->id}");

            if ($newRank->id != $oldRankId) {
                Log::info('Rank updated via force command', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                ]);
            }

            return true;
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
}