<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPVStatus extends Command
{
    protected $signature = 'pv:check-status {--user= : ID de l utilisateur specifique}';
    protected $description = 'Verifier l etat des PV mensuels';

    public function handle()
    {
        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error('Utilisateur ID ' . $userId . ' non trouve');
                return 1;
            }

            $this->table(
                ['Metrique', 'Valeur'],
                [
                    ['ID', $user->id],
                    ['Nom', $user->name],
                    ['Type', $user->user_type ?? 'member'],
                    ['PV mensuel', $user->monthly_pv ?? 0],
                    ['BV mensuel', $user->monthly_bv ?? 0],
                    ['PV cumule', $user->pv_balance ?? 0],
                    ['BV cumule', $user->bv_balance ?? 0],
                    ['PV d equipe', $user->team_pv ?? 0],
                    ['Grade', $user->rank_name ?? 'Distributeur'],
                    ['Niveau', $user->rank_level ?? 1],
                ]
            );
            return 0;
        }

        $totalUsers = User::where('is_active', true)->count();
        $usersWithPV = User::where('monthly_pv', '>', 0)->count();
        $members = User::where('user_type', 'member')->where('is_active', true)->count();
        $clients = User::where('user_type', 'client')->where('is_active', true)->count();
        $membersWithPV = User::where('user_type', 'member')->where('monthly_pv', '>', 0)->count();
        $clientsWithPV = User::where('user_type', 'client')->where('monthly_pv', '>', 0)->count();
        $totalPV = User::sum('monthly_pv');
        $totalBV = User::sum('monthly_bv');
        $currentPeriod = date('Y-m');

        $topUsers = User::where('monthly_pv', '>', 0)
            ->orderBy('monthly_pv', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'monthly_pv', 'rank_id', 'user_type']);

        $this->table(
            ['Metrique', 'Valeur'],
            [
                ['Periode', $currentPeriod],
                ['Total utilisateurs actifs', $totalUsers],
                ['  Membres MLM', $members],
                ['  Clients POS', $clients],
                ['Utilisateurs avec PV', $usersWithPV],
                ['  Membres avec PV', $membersWithPV],
                ['  Clients avec PV', $clientsWithPV],
                ['Total PV mensuel', number_format($totalPV, 0)],
                ['Total BV mensuel', number_format($totalBV, 0)],
                ['PV moyen (membres)', $membersWithPV > 0 ? number_format(User::where('user_type', 'member')->sum('monthly_pv') / $membersWithPV, 0) : 0],
                ['PV moyen (clients)', $clientsWithPV > 0 ? number_format(User::where('user_type', 'client')->sum('monthly_pv') / $clientsWithPV, 0) : 0],
            ]
        );

        if ($topUsers->isNotEmpty()) {
            $this->newLine();
            $this->info('Top 10 des utilisateurs avec le plus de PV mensuel:');
            
            $this->table(
                ['#', 'ID', 'Nom', 'Type', 'PV', 'Grade'],
                $topUsers->map(function ($user, $index) {
                    return [
                        $index + 1,
                        $user->id,
                        $user->name,
                        $user->user_type ?? 'member',
                        number_format($user->monthly_pv, 0),
                        $user->rank_name ?? 'Distributeur',
                    ];
                })->toArray()
            );
        }

        return 0;
    }
}