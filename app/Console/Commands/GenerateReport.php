<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Order;
use App\Models\Commission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateReport extends Command
{
    protected $signature = 'report:generate {--date= : Date du rapport}';
    protected $description = 'Générer le rapport quotidien';

    public function handle(): void
    {
        $date = $this->option('date') ?? now()->format('Y-m-d');
        $this->info("Génération du rapport pour le {$date}...");

        $data = [
            'date' => $date,
            'users' => User::whereDate('created_at', $date)->count(),
            'orders' => Order::whereDate('created_at', $date)->count(),
            'revenue' => Order::whereDate('created_at', $date)->sum('total'),
            'commissions' => Commission::whereDate('created_at', $date)->sum('amount'),
        ];

        $path = storage_path('app/reports/');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        Storage::put("reports/report-{$date}.json", json_encode($data, JSON_PRETTY_PRINT));
        $this->info("✅ Rapport généré: reports/report-{$date}.json");
    }
}
