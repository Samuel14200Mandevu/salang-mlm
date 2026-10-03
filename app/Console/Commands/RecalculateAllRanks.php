<?php

namespace App\Console\Commands;


use App\Support\MlmPeriod;
use App\Models\User;
use App\Jobs\RecalculateAfterPVImport;
use Illuminate\Console\Command;

class RecalculateAllRanks extends Command
{
    protected $signature = 'mlm:recalculate-all-ranks 
                            {--chunk=100 : Nombre d\'users par batch}
                            {--sync : Exécuter en synchrone au lieu de la queue}';

    protected $description = 'Recalcule les grades de TOUS les utilisateurs actifs';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $sync = $this->option('sync');

        $total = User::where('is_active', true)->count();
        $this->info("Recalcul de {$total} utilisateurs...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;

        User::where('is_active', true)
            ->select('id')
            ->chunkById($chunkSize, function ($users) use ($sync, $bar, &$processed) {
                $userIds = $users->pluck('id')->toArray();

                if ($sync) {
                    foreach ($userIds as $userId) {
                        $user = User::find($userId);
                        if ($user) {
                            app(\App\Services\MLM\AdvancedRankCalculator::class)
                                ->recalculateUserRank($user, 'Bulk recalculate');
                        }
                        $bar->advance();
                        $processed++;
                    }
                } else {
                    RecalculateAfterPVImport::dispatch($userIds, MlmPeriod::current())
                        ->onQueue('rank-recalculation');
                    $bar->advance($userIds ? count($userIds) : 0);
                    $processed += count($userIds);
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$processed} utilisateurs traités.");

        if (!$sync) {
            $this->warn("⚠️  Les jobs sont en queue. Assurez-vous qu'un worker tourne :");
            $this->line("   php artisan queue:work --queue=rank-recalculation,default");
        }

        return self::SUCCESS;
    }
}