<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Services\MLM\PaymentEligibilityChecker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateCommissionPayments extends Command
{
    protected $signature = 'commissions:simulate-payments
                            {--period= : Période YYYY-MM (sinon toutes les périodes calculated)}
                            {--respect-config : Utilise payment_validation + taxe + min (défaut)}';

    protected $description = 'Dry-run generatePayments (lecture seule, aucune écriture)';

    public function handle(PaymentEligibilityChecker $checker): int
    {
        $periodFilter = $this->option('period');

        $periods = CommissionPeriod::query()
            ->when($periodFilter, fn ($q) => $q->where('period', $periodFilter))
            ->when(! $periodFilter, fn ($q) => $q->where('status', 'calculated'))
            ->orderBy('period')
            ->get();

        if ($periods->isEmpty()) {
            $this->error('Aucune commission_period calculated trouvée.');

            return self::FAILURE;
        }

        $grand = [
            'credit' => ['users' => 0, 'gross' => 0.0, 'net' => 0.0],
            'deferred' => ['users' => 0, 'gross' => 0.0],
        ];
        $byReason = [];

        foreach ($periods as $period) {
            $this->info("Période {$period->period} (status={$period->status})");

            $aggregates = Commission::query()
                ->where('commission_period_id', $period->id)
                ->where('status', 'pending')
                ->select('user_id', DB::raw('SUM(amount) as total'))
                ->groupBy('user_id')
                ->get();

            if ($aggregates->isEmpty()) {
                $this->warn('  Aucune commission pending — generatePayments marquerait paid à 0 (piège).');
                continue;
            }

            $periodCredit = 0;
            $periodNet = 0.0;
            $periodDeferred = 0;

            foreach ($aggregates as $row) {
                $user = User::find($row->user_id);
                if (! $user) {
                    continue;
                }

                $gross = (float) $row->total;
                $result = $checker->evaluate($user, $period->period, $gross);

                if ($result->shouldCreditWallet()) {
                    $periodCredit++;
                    $net = $checker->calculateNetAmount($gross);
                    $periodNet += $net['net_amount'];
                    $grand['credit']['gross'] += $gross;
                    $grand['credit']['net'] += $net['net_amount'];
                } else {
                    $periodDeferred++;
                    $code = $result->reasonCode ?? 'unknown';
                    $byReason[$code] = ($byReason[$code] ?? 0) + 1;
                    $grand['deferred']['gross'] += $gross;
                }
            }

            $grand['credit']['users'] += $periodCredit;
            $grand['deferred']['users'] += $periodDeferred;

            $this->line(sprintf(
                '  → crédit wallet: %d users, net ~%s USD | deferred: %d users',
                $periodCredit,
                number_format($periodNet, 2),
                $periodDeferred
            ));
        }

        $this->newLine();
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Users crédités (total)', $grand['credit']['users']],
                ['Brut crédité', '$'.number_format($grand['credit']['gross'], 2)],
                ['Net wallet (après taxe)', '$'.number_format($grand['credit']['net'], 2)],
                ['Users deferred', $grand['deferred']['users']],
                ['Brut deferred', '$'.number_format($grand['deferred']['gross'], 2)],
            ]
        );

        if ($byReason !== []) {
            $this->info('Deferred par raison :');
            foreach ($byReason as $code => $count) {
                $this->line("  - {$code}: {$count} users (agrégats période)");
            }
        }

        return self::SUCCESS;
    }
}
