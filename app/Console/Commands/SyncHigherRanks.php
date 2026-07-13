<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MLM\AdvancedRankCalculator;
use Illuminate\Console\Command;

class SyncHigherRanks extends Command
{
    protected $signature = 'higher-ranks:sync';
    protected $description = 'Synchroniser les grades supérieurs';

    public function handle(AdvancedRankCalculator $calculator): void
    {
        $this->info('Synchronisation des grades supérieurs...');
        
        $period = now()->format('Y-m');
        $users = User::where('is_active', true)->get();
        $updated = 0;

        foreach ($users as $user) {
            $eligibleRanks = $calculator->checkHigherRankEligibility($user, $period);
            
            foreach ($eligibleRanks as $rankData) {
                $user->higherRanks()->syncWithoutDetaching([
                    $rankData['id'] => [
                        'achieved_at' => now(),
                        'period' => $period,
                    ]
                ]);
                $updated++;
            }
        }

        $this->info("✅ {$updated} grades supérieurs synchronisés");
    }
}
