<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateMonthlyPV extends Command
{
    protected $signature = 'pv:update-monthly';
    protected $description = 'Mettre à jour les PV mensuels de tous les utilisateurs';

    public function handle(): void
    {
        $this->info('Mise à jour des PV mensuels...');
        $users = User::where('is_active', true)->get();
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $user->updateMonthlyPV();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ PV mensuels mis à jour pour ' . $users->count() . ' utilisateurs');
    }
}
