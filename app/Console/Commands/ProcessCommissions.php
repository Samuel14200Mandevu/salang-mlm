<?php
// app/Console/Commands/ProcessCommissions.php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Commission;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCommissions extends Command
{
    protected $signature = 'commissions:process 
                            {--period= : Période au format YYYY-MM}
                            {--user= : ID de l\'utilisateur spécifique}
                            {--all : Traiter pour tous les utilisateurs}';
    
    protected $description = 'Traiter toutes les commissions en attente';

    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Traitement des commissions en attente...');

        // Cas 1: Utilisateur spécifique
        if ($this->option('user')) {
            $user = User::find($this->option('user'));
            if (!$user) {
                $this->error('❌ Utilisateur non trouvé');
                return 1;
            }
            $this->processUserCommissions($user);
            return 0;
        }

        // Cas 2: Tous les utilisateurs
        if ($this->option('all')) {
            $this->processAllUsersCommissions();
            return 0;
        }

        // Cas 3: Par période (défaut)
        $period = $this->option('period') ?? date('Y-m');
        $this->processCommissionsByPeriod($period);
        return 0;
    }

    /**
     * Traiter les commissions d'un utilisateur spécifique
     */
    private function processUserCommissions(User $user): void
    {
        $this->info("📊 Traitement des commissions pour {$user->name}...");

        $pendingCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        if ($pendingCommissions->isEmpty()) {
            $this->line("⏸️ Aucune commission en attente pour {$user->name}");
            return;
        }

        $processed = 0;
        $totalAmount = 0;

        foreach ($pendingCommissions as $commission) {
            if ($this->processSingleCommission($commission)) {
                $processed++;
                $totalAmount += $commission->amount;
            }
        }

        $this->info("✅ {$processed} commissions traitées pour un total de {$totalAmount} USD");
    }

    /**
     * Traiter toutes les commissions pour tous les utilisateurs
     */
    private function processAllUsersCommissions(): void
    {
        $users = User::whereHas('commissions', function($query) {
            $query->where('status', 'pending');
        })->get();

        if ($users->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente');
            return;
        }

        $this->info("📊 Traitement pour {$users->count()} utilisateurs...");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $totalProcessed = 0;
        $totalAmount = 0;

        foreach ($users as $user) {
            $pendingCommissions = Commission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();

            foreach ($pendingCommissions as $commission) {
                if ($this->processSingleCommission($commission)) {
                    $totalProcessed++;
                    $totalAmount += $commission->amount;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("📊 RÉSULTATS:");
        $this->line("   ✅ Commissions traitées: {$totalProcessed}");
        $this->line("   💰 Montant total: {$totalAmount} USD");
    }

    /**
     * Traiter les commissions par période
     */
    private function processCommissionsByPeriod(string $period): void
    {
        $this->info("📊 Traitement des commissions pour la période {$period}...");

        $pendingCommissions = Commission::where('period', $period)
            ->where('status', 'pending')
            ->get();

        if ($pendingCommissions->isEmpty()) {
            $this->info('⏸️ Aucune commission en attente pour cette période');
            return;
        }

        $this->info("📊 {$pendingCommissions->count()} commissions à traiter...");

        $processed = 0;
        $totalAmount = 0;
        $bar = $this->output->createProgressBar($pendingCommissions->count());
        $bar->start();

        foreach ($pendingCommissions as $commission) {
            if ($this->processSingleCommission($commission)) {
                $processed++;
                $totalAmount += $commission->amount;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("📊 RÉSULTATS:");
        $this->line("   ✅ Commissions traitées: {$processed}");
        $this->line("   💰 Montant total: {$totalAmount} USD");
    }

    /**
     * Traiter une commission individuelle
     */
    private function processSingleCommission(Commission $commission): bool
    {
        try {
            DB::beginTransaction();

            $user = $commission->user;
            
            if (!$user) {
                $commission->status = 'failed';
                $commission->notes = 'Utilisateur non trouvé';
                $commission->save();
                DB::rollBack();
                return false;
            }

            // Vérifier le wallet
            $wallet = $user->wallet;
            
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'pending_balance' => 0,
                    'total_withdrawn' => 0,
                    'total_deposited' => 0,
                    'currency' => 'USD',
                    'is_active' => true,
                ]);
                $this->warn("   ⚠️ Wallet créé pour {$user->name}");
            }

            $balanceBefore = $wallet->balance;
            $wallet->balance += $commission->amount;
            $wallet->total_deposited += $commission->amount;
            $wallet->save();

            // Créer la transaction
            Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'commission',
                'amount' => $commission->amount,
                'fee' => $commission->fee ?? 0,
                'net_amount' => $commission->amount - ($commission->fee ?? 0),
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'status' => 'completed',
                'reference' => 'COMM-' . $commission->id . '-' . time(),
                'description' => $commission->description ?? 'Commission automatique',
                'metadata' => json_encode([
                    'commission_id' => $commission->id,
                    'type' => $commission->type,
                    'period' => $commission->period,
                    'from_user_id' => $commission->from_user_id,
                ]),
                'completed_at' => now(),
            ]);

            // Marquer la commission comme payée
            $commission->status = 'paid';
            $commission->paid_at = now();
            $commission->save();

            // Mettre à jour le total des gains de l'utilisateur
            $user->total_earnings += $commission->amount;
            $user->saveQuietly();

            DB::commit();
            
            $this->line("   ✅ Commission #{$commission->id}: {$commission->amount} USD pour {$user->name}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur traitement commission', [
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error("   ❌ Erreur commission #{$commission->id}: {$e->getMessage()}");
            return false;
        }
    }
}