<?php
// app/Console/Commands/CheckMonthlyPVStatus.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\CommissionPeriod;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckMonthlyPVStatus extends Command
{
    protected $signature = 'pv:check-status {--user= : ID de l\'utilisateur spécifique}';
    protected $description = 'Vérifier l\'état des PV mensuels';

    private function icon(string $type): string
    {
        $icons = [
            'info' => '<fg=blue>ℹ</>',
            'success' => '<fg=green>✓</>',
            'warning' => '<fg=yellow>⚠</>',
            'database' => '<fg=magenta>◉</>',
            'trophy' => '<fg=yellow>★</>',
        ];
        return $icons[$type] ?? '';
    }

    public function handle()
    {
        $this->info($this->icon('database') . ' État des PV mensuels');
        $this->line('   Date: ' . now()->toDateString());
        $this->newLine();

        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error('✗ Utilisateur ID ' . $userId . ' non trouvé');
                return 1;
            }

            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['ID', $user->id],
                    ['Nom', $user->name],
                    ['PV mensuel', $user->monthly_pv ?? 0],
                    ['BV mensuel', $user->monthly_bv ?? 0],
                    ['PV cumulé', $user->pv_balance ?? 0],
                    ['BV cumulé', $user->bv_balance ?? 0],
                    ['Grade', $user->rank_name ?? 'Distributeur'],
                    ['Niveau', $user->rank_level ?? 1],
                ]
            );
            return 0;
        }

        $totalUsers = User::where('is_active', true)->count();
        $usersWithPV = User::where('monthly_pv', '>', 0)->count();
        $totalPV = User::sum('monthly_pv');
        $totalBV = User::sum('monthly_bv');
        $currentPeriod = date('Y-m');

        $period = CommissionPeriod::where('period', $currentPeriod)->first();

        $topUsers = User::where('monthly_pv', '>', 0)
            ->orderBy('monthly_pv', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'monthly_pv', 'rank_id']);

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Période', $currentPeriod],
                ['Statut période', $period ? $period->status_label : 'Aucune'],
                ['Total utilisateurs actifs', $totalUsers],
                ['Utilisateurs avec PV', $usersWithPV],
                ['Total PV mensuel', number_format($totalPV, 0)],
                ['Total BV mensuel', number_format($totalBV, 0)],
                ['PV moyen', $usersWithPV > 0 ? number_format($totalPV / $usersWithPV, 0) : 0],
                ['Prochain reset', Carbon::now()->startOfMonth()->addDays(7)->toDateString()],
                ['Jours avant reset', Carbon::now()->diffInDays(Carbon::now()->startOfMonth()->addDays(7))],
            ]
        );

        if ($topUsers->isNotEmpty()) {
            $this->newLine();
            $this->info($this->icon('trophy') . ' Top 10 des utilisateurs avec le plus de PV mensuel:');
            
            $this->table(
                ['#', 'ID', 'Nom', 'PV', 'Grade'],
                $topUsers->map(function ($user, $index) {
                    return [
                        $index + 1,
                        $user->id,
                        $user->name,
                        number_format($user->monthly_pv, 0),
                        $user->rank_name ?? 'Distributeur',
                    ];
                })->toArray()
            );
        }

        return 0;
    }
}