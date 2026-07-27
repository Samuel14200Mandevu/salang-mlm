<?php
// app/Console/Commands/CheckPVStatus.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Rank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPVStatus extends Command
{
    protected $signature = 'pv:check-status';
    protected $description = 'Vérifier l\'état des PV mensuels des utilisateurs';

    public function handle()
    {
        $this->info('📊 Vérification de l\'état des PV mensuels...');

        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'users_with_pv' => User::where('is_active', true)->where('monthly_pv', '>', 0)->count(),
            'users_with_zero_pv' => User::where('is_active', true)->where('monthly_pv', 0)->count(),
            'total_pv' => User::where('is_active', true)->sum('monthly_pv'),
            'date' => now()->format('Y-m-d H:i:s'),
            'month' => now()->format('Y-m'),
        ];

        $this->info("📊 Statistiques du mois de {$stats['month']}:");
        $this->line("   👥 Total utilisateurs actifs: {$stats['total_users']}");
        $this->line("   📈 Avec PV > 0: {$stats['users_with_pv']}");
        $this->line("   📉 Avec PV = 0: {$stats['users_with_zero_pv']}");
        $this->line("   💰 Total PV cumulé: {$stats['total_pv']}");

        // Top 10 avec rank_id
        $topUsers = User::where('is_active', true)
            ->where('monthly_pv', '>', 0)
            ->orderBy('monthly_pv', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'monthly_pv', 'rank_id']);

        if ($topUsers->isNotEmpty()) {
            $this->newLine();
            $this->info('🏆 Top 10 utilisateurs par PV mensuel:');
            foreach ($topUsers as $index => $user) {
                // Récupérer le nom du grade via rank_id
                $rankName = 'Distributeur';
                if ($user->rank_id) {
                    $rank = Rank::find($user->rank_id);
                    if ($rank) {
                        $rankName = $rank->name;
                    }
                }
                $this->line("   " . ($index + 1) . ". {$user->name} - {$user->monthly_pv} PV - Grade: {$rankName}");
            }
        }

        // Répartition des grades avec rank_id
        $this->newLine();
        $this->info('📊 Répartition des grades:');
        
        // Récupérer tous les utilisateurs groupés par rank_id
        $rankStats = User::where('is_active', true)
            ->select('rank_id', DB::raw('count(*) as total'))
            ->groupBy('rank_id')
            ->orderBy('rank_id')
            ->get();

        if ($rankStats->isNotEmpty()) {
            foreach ($rankStats as $stat) {
                $rankName = 'Distributeur';
                if ($stat->rank_id) {
                    $rank = Rank::find($stat->rank_id);
                    if ($rank) {
                        $rankName = $rank->name;
                    }
                }
                $this->line("   - {$rankName}: {$stat->total} utilisateurs");
            }
        } else {
            $this->line("   Aucun grade attribué");
        }

        // Détail des utilisateurs avec leurs grades
        $this->newLine();
        $this->info('📋 Détail des utilisateurs:');
        $users = User::where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'monthly_pv', 'rank_id', 'pv_balance', 'team_pv']);

        foreach ($users as $user) {
            $rankName = 'Distributeur';
            if ($user->rank_id) {
                $rank = Rank::find($user->rank_id);
                if ($rank) {
                    $rankName = $rank->name;
                }
            }
            $this->line("   ID: {$user->id} - {$user->name} - PV Mensuel: {$user->monthly_pv} - PV Cumulé: {$user->pv_balance} - Team PV: {$user->team_pv} - Grade: {$rankName}");
        }

        Log::info('État des PV mensuels', $stats);

        return 0;
    }
}