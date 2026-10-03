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
}
