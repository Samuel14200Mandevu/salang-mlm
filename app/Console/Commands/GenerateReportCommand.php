<?php
// app/Console/Commands/GenerateReport.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateReport extends Command
{
    protected $signature = 'report:generate {--date=}';
    protected $description = 'Générer un rapport quotidien';

    public function handle()
    {
        $date = $this->option('date') ?? now()->format('Y-m-d');
        
        $this->info("Génération du rapport pour {$date}...");

        $stats = [
            'date' => $date,
            'total_users' => User::count(),
            'new_users' => User::whereDate('created_at', $date)->count(),
            'total_orders' => Order::count(),
            'total_commissions' => Commission::where('status', 'paid')->sum('amount'),
            'total_commissions_pending' => Commission::where('status', 'pending')->sum('amount'),
        ];

        // Sauvegarder le rapport
        $filename = "reports/report_{$date}.json";
        Storage::put($filename, json_encode($stats, JSON_PRETTY_PRINT));

        $this->table(['Métrique', 'Valeur'], [
            ['Utilisateurs', $stats['total_users']],
            ['Nouveaux utilisateurs', $stats['new_users']],
            ['Commandes totales', $stats['total_orders']],
            ['Commissions payées', '$' . number_format($stats['total_commissions'], 2)],
            ['Commissions en attente', '$' . number_format($stats['total_commissions_pending'], 2)],
        ]);

        $this->info("Rapport sauvegardé : {$filename}");

        return 0;
    }
}