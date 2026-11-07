<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearPvTransactions extends Command
{
    protected $signature = 'pv:clear-transactions
                            {--force : Exécuter sans confirmation}
                            {--dry-run : Afficher le nombre sans supprimer}';

    protected $description = 'Vide l’historique des transactions PV pour tous les membres';

    public function handle(): int
    {
        if (! Schema::hasTable('pv_transactions')) {
            $this->warn('Table pv_transactions absente.');

            return self::SUCCESS;
        }

        $count = (int) DB::table('pv_transactions')->count();
        $this->info("Transactions actuelles : {$count}");

        if ($count === 0) {
            $this->info('Déjà vide.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('DRY-RUN — aucune suppression.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Supprimer les {$count} transaction(s) PV ?", true)) {
            $this->warn('Annulé.');

            return self::FAILURE;
        }

        DB::table('pv_transactions')->delete();
        $this->info('Historique PV vidé pour tous les membres.');

        return self::SUCCESS;
    }
}
