<?php
// app/Console/Commands/TestMlmCalendar.php

namespace App\Console\Commands;

use App\Models\CommissionPeriod;
use Illuminate\Console\Command;

class TestMlmCalendar extends Command
{
    protected $signature = 'mlm:test-calendar';
    protected $description = 'Teste le calendrier MLM (8 → 7)';

    public function handle(): int
    {
        $this->info('🧪 Test du calendrier MLM (8 → 7)');
        $this->newLine();

        $dates = [
            '2026-10-01',
            '2026-10-07',
            '2026-10-08',
            '2026-10-15',
            '2026-10-31',
            '2026-11-01',
            '2026-11-07',
            '2026-11-08',
        ];

        $rows = [];
        foreach ($dates as $date) {
            $periodValue = CommissionPeriod::getPeriodValueForDate($date);
            $periodDates = CommissionPeriod::getPeriodDates($periodValue);

            $rows[] = [
                $date,
                $periodValue,
                $periodDates['start']->format('d/m/Y'),
                $periodDates['end']->format('d/m/Y'),
                $periodDates['payment']->format('d/m/Y'),
            ];
        }

        $this->table(
            ['Date testée', 'Période MLM', 'Début', 'Fin', 'Paiement'],
            $rows
        );

        $this->newLine();

        // Détail de la période courante
        $current = CommissionPeriod::getCurrentPeriod();
        if ($current) {
            $this->info('📅 Période MLM courante :');
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['Period',      $current->period],
                    ['Début',       $current->start_date?->format('d/m/Y') ?? '-'],
                    ['Fin',         $current->end_date?->format('d/m/Y') ?? '-'],
                    ['Calcul',      $current->calculation_date?->format('d/m/Y H:i') ?? '-'],
                    ['Paiement',    $current->payment_date?->format('d/m/Y H:i') ?? '-'],
                    ['Statut',      $current->status],
                ]
            );
        } else {
            $this->warn('⚠️  Aucune période MLM courante en base');
        }

        return self::SUCCESS;
    }
}
