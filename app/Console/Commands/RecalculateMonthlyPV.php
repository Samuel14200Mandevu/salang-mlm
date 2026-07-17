<?php
// app/Console/Commands/RecalculateMonthlyPV.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RecalculateMonthlyPV extends Command
{
    protected $signature = 'monthly:recalculate {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Recalculer les PV mensuels pour tous les utilisateurs';

    public function handle()
    {
        $query = User::where('is_active', true);
        
        if ($this->option('user')) {
            $query->where('id', $this->option('user'));
        }
        
        $users = $query->get();
        $this->info("Recalcul des PV mensuels pour {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        foreach ($users as $user) {
            $user->updateMonthlyPV();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Recalcul terminé !');
    }
}