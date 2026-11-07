<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige les lignes pv_transactions « Migration initiale » qui ont
 * additionné pv_balance + monthly_pv (double comptage d’audit).
 * Idempotent. Ne modifie pas users.pv_balance.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pv_transactions')) {
            return;
        }

        $rows = DB::table('pv_transactions')
            ->where(function ($q) {
                $q->where('source', 'migration')
                    ->orWhere('description', 'like', '%Migration initiale%')
                    ->orWhere('description', 'like', '%pv_balance:%');
            })
            ->orderBy('id')
            ->get(['id', 'pv_amount', 'bv_amount', 'description', 'metadata']);

        foreach ($rows as $row) {
            $description = (string) ($row->description ?? '');
            if (! preg_match('/pv_balance:\s*([0-9]+(?:\.[0-9]+)?)/i', $description, $mPv)) {
                continue;
            }
            if (! preg_match('/monthly_pv:\s*([0-9]+(?:\.[0-9]+)?)/i', $description, $mMo)) {
                continue;
            }

            $pvBalance = (float) $mPv[1];
            $monthlyPv = (float) $mMo[1];
            if ($monthlyPv <= 0) {
                continue;
            }

            $currentPv = (float) $row->pv_amount;
            $sum = $pvBalance + $monthlyPv;
            if (abs($currentPv - $sum) > 0.01) {
                continue;
            }

            $currentBv = (float) ($row->bv_amount ?? 0);
            $correctBv = $currentBv;
            if (abs($currentBv - $sum) < 0.01 || $currentBv <= 0) {
                $correctBv = $pvBalance;
            }

            $meta = [];
            if (! empty($row->metadata)) {
                $decoded = json_decode((string) $row->metadata, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }

            // Déjà corrigé par une exécution précédente.
            if (! empty($meta['migration_fix']['fixed_at'])) {
                continue;
            }

            $meta['migration_fix'] = [
                'fixed_at' => now()->toIso8601String(),
                'old_pv_amount' => $currentPv,
                'old_bv_amount' => $currentBv,
                'parsed_pv_balance' => $pvBalance,
                'parsed_monthly_pv' => $monthlyPv,
                'reason' => 'Double comptage pv_balance + monthly_pv à l’import',
                'via' => 'migration:2026_11_07_154500',
            ];

            $newDescription = sprintf(
                'Migration initiale des PV personnels (%s PV). Note : monthly_pv=%s était un compteur de période, pas un crédit supplémentaire.',
                rtrim(rtrim(number_format($pvBalance, 1, '.', ''), '0'), '.'),
                rtrim(rtrim(number_format($monthlyPv, 1, '.', ''), '0'), '.')
            );

            DB::table('pv_transactions')->where('id', $row->id)->update([
                'pv_amount' => $pvBalance,
                'bv_amount' => $correctBv,
                'description' => $newDescription,
                'metadata' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('pv_transactions')) {
            return;
        }

        $rows = DB::table('pv_transactions')
            ->whereNotNull('metadata')
            ->where('metadata', 'like', '%migration_fix%')
            ->orderBy('id')
            ->get(['id', 'metadata']);

        foreach ($rows as $row) {
            $meta = json_decode((string) $row->metadata, true);
            if (! is_array($meta) || empty($meta['migration_fix']['old_pv_amount'])) {
                continue;
            }

            $fix = $meta['migration_fix'];
            $oldPv = $fix['old_pv_amount'];
            $oldBv = $fix['old_bv_amount'] ?? 0;
            $pvBalance = $fix['parsed_pv_balance'] ?? null;
            $monthlyPv = $fix['parsed_monthly_pv'] ?? null;

            $description = $row->id;
            if ($pvBalance !== null && $monthlyPv !== null) {
                $description = sprintf(
                    'Migration initiale des PV (pv_balance: %s, monthly_pv: %s)',
                    number_format((float) $pvBalance, 1, '.', ''),
                    number_format((float) $monthlyPv, 1, '.', '')
                );
            }

            unset($meta['migration_fix']);

            DB::table('pv_transactions')->where('id', $row->id)->update([
                'pv_amount' => $oldPv,
                'bv_amount' => $oldBv,
                'description' => $description,
                'metadata' => empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }
};
