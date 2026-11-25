<?php

namespace App\Support;

use App\Models\CommissionPeriod;
use Carbon\Carbon;

/**
 * Période MLM courante (calendrier 8 → 7).
 */
class MlmPeriod
{
    public static function current(Carbon|string|null $date = null): string
    {
        return CommissionPeriod::getPeriodValueForDate($date ?? now());
    }

    public static function forDate(Carbon|string $date): string
    {
        return CommissionPeriod::getPeriodValueForDate($date);
    }

    public static function isCurrent(string $periodValue): bool
    {
        return $periodValue === self::current();
    }

    /**
     * Première période affichable (YYYY-MM). Les mois antérieurs restent
     * en base mais ne sont plus proposés / agrégés dans les écrans.
     */
    public static function displayFrom(): string
    {
        return (string) config('mlm.commissions_display_from', '2026-01');
    }

    public static function isDisplayable(?string $periodValue): bool
    {
        if ($periodValue === null || $periodValue === '') {
            return true;
        }

        return $periodValue >= self::displayFrom();
    }
}
