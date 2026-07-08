<?php
// app/Console/Commands/CleanTestData.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Models\Order;
use Illuminate\Console\Command;

class CleanTestData extends Command
{
    protected $signature = 'clean:test-data {--force}';
    protected $description = 'Nettoyer les données de test';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Supprimer toutes les données de test ?')) {
                return 0;
            }
        }

        // Supprimer les utilisateurs de test
        $count = User::where('email', 'like', '%test%')->delete();
        $this->info(" {$count} utilisateurs de test supprimés");

        // Supprimer les commissions de test
        $count = Commission::where('description', 'like', '%test%')->delete();
        $this->info(" {$count} commissions de test supprimées");

        return 0;
    }
}