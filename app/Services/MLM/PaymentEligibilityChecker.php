<?php

namespace App\Services\MLM;

use App\Models\PVHistory;
use App\Models\User;
use App\Models\UserMonthlyRank;

class PaymentEligibilityChecker
{
    public function evaluate(User $user, string $periodValue, float $gross): PaymentEligibilityResult
    {
        $validation = config('commission.payment_validation', []);

        if (($validation['require_kyc'] ?? true) && $user->kyc_status !== 'verified') {
            return PaymentEligibilityResult::deferred(
                'kyc_pending',
                (string) config('commission.payment_status_messages.kyc_pending', 'KYC non vérifié')
            );
        }

        if (($validation['require_active_account'] ?? true) && ! $user->is_active) {
            return PaymentEligibilityResult::deferred(
                'account_inactive',
                (string) config('commission.payment_status_messages.account_inactive', 'Compte inactif')
            );
        }

        $userRank = $user->rankObject;
        if (($validation['require_rank'] ?? true) && ! $userRank) {
            return PaymentEligibilityResult::deferred(
                'no_rank',
                (string) config('commission.payment_status_messages.no_rank', 'Aucun grade trouvé')
            );
        }

        if (($validation['require_monthly_pv'] ?? true)) {
            $monthlyPvRequired = (float) ($userRank->monthly_pv_required ?? 0);
            $periodMonthlyPv = $this->resolvePeriodMonthlyPv($user->id, $periodValue);

            if ($periodMonthlyPv < $monthlyPvRequired) {
                $template = (string) config(
                    'commission.payment_status_messages.monthly_pv_insufficient',
                    'PV mensuel insuffisant ({pv}/{required})'
                );

                return PaymentEligibilityResult::deferred(
                    'monthly_pv_insufficient',
                    str_replace(
                        ['{pv}', '{required}'],
                        [(string) $periodMonthlyPv, (string) $monthlyPvRequired],
                        $template
                    )
                );
            }
        }

        $minPayment = (float) config('commission.min_payment', 1);
        if ($gross < $minPayment) {
            $template = (string) config(
                'commission.payment_status_messages.amount_too_small',
                'Montant inférieur au minimum ({amount} < {min})'
            );

            return PaymentEligibilityResult::deferred(
                'amount_too_small',
                str_replace(
                    ['{amount}', '{min}'],
                    [(string) $gross, (string) $minPayment],
                    $template
                )
            );
        }

        return PaymentEligibilityResult::credit();
    }

    public function resolvePeriodMonthlyPv(int $userId, string $periodValue): float
    {
        $fromRank = UserMonthlyRank::query()
            ->where('user_id', $userId)
            ->where('period', $periodValue)
            ->value('pv_monthly');

        if ($fromRank !== null) {
            return (float) $fromRank;
        }

        return (float) PVHistory::query()
            ->where('user_id', $userId)
            ->where('period', $periodValue)
            ->sum('amount');
    }

    /**
     * @return array{tax_rate: float, tax_amount: float, net_amount: float}
     */
    public function calculateNetAmount(float $gross): array
    {
        $taxRate = (float) config('commission.tax_rate', 5);
        $taxAmount = $gross * ($taxRate / 100);
        $netAmount = $gross - $taxAmount;

        return [
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'net_amount' => $netAmount,
        ];
    }
}
