<?php
// app/Console/Commands/GenerateCommissionPayments.php

namespace App\Console\Commands;

use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Console\Command;

class GenerateCommissionPayments extends Command
{
    protected $signature = 'commissions:generate-payments
                            {period? : Période YYYY-MM (défaut : mois précédent)}';

    protected $description = 'Génère les paiements (le 15 du mois M+1)';

    public function handle(MonthlyCommissionService $service): int
    {
        $periodValue = $this->argument('period');

        if (!$periodValue) {
            $periodValue = CommissionPeriod::getPaymentPeriodValue();
        }

        $period = CommissionPeriod::where('period', $periodValue)->first();

        if (!$period) {
            $this->error("❌ Période {$periodValue} introuvable");
            return self::FAILURE;
        }

        $blockReason = $period->paymentBlockReason();
        if ($blockReason === 'historical_offline') {
            $this->error('❌ Période historique (payée offline) — non payable via le système.');

            return self::FAILURE;
        }

        if ($blockReason === 'hidden_period') {
            $this->error('❌ Période masquée (bande C) — non payable tant qu’elle est hidden.');

            return self::FAILURE;
        }

        if ($blockReason === 'invalid_status') {
            $this->error("❌ La période doit être 'calculated' (actuel : {$period->status})");

            return self::FAILURE;
        }

        $this->info("💸 Génération des paiements pour {$period->period}...");

        if (!$service->generatePayments($period->id)) {
            $this->error('❌ Erreur lors de la génération');
            return self::FAILURE;
        }

        $period->refresh();

        $this->newLine();
        $this->info('✅ Paiements générés');
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Période',       $period->period],
                ['Statut',        $period->status],
                ['Total payé',    '$' . number_format($period->total_paid, 2)],
                ['Date paiement', $period->payment_date?->format('d/m/Y H:i') ?? '-'],
                ['Notes',         $period->notes ?? '-'],
            ]
        );

        return self::SUCCESS;
    }
}
