<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BlockMigrateFreshProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! app()->runningInConsole()) {
            return;
        }

        $argv = $_SERVER['argv'] ?? [];
        $command = $argv[1] ?? '';

        $dangerous = ['migrate:fresh', 'migrate:refresh', 'db:wipe'];

        if (! in_array($command, $dangerous, true)) {
            return;
        }

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        $isProductionName = str_contains($database, 'salang_mlm')
            && ! str_contains($database, 'dusk')
            && ! str_contains($database, 'test');

        if ($isProductionName) {
            fwrite(STDERR, "\n🚨🚨🚨 COMMANDE BLOQUÉE 🚨🚨🚨\n");
            fwrite(STDERR, "Base « {$database} » = PRODUCTION (utilisateurs réels).\n\n");
            fwrite(STDERR, "Pour Dusk : php artisan migrate:fresh --env=dusk\n");
            fwrite(STDERR, "Pour forcer : DB_ALLOW_DESTRUCTIVE=yes php artisan {$command}\n\n");
            exit(1);
        }

        $isIsolatedTestDb = str_contains($database, 'dusk')
            || str_contains($database, '_test')
            || str_contains($database, 'salang_mlm_test')
            || $database === ':memory:';

        if (! $isIsolatedTestDb && ! env('DB_ALLOW_DESTRUCTIVE')) {
            fwrite(STDERR, "\n⚠️  Commande destructive ({$command}). Confirmer avec DB_ALLOW_DESTRUCTIVE=yes\n\n");
            exit(1);
        }
    }
}
