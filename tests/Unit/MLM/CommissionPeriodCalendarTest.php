<?php

namespace Tests\Unit\MLM;

use App\Models\CommissionPeriod;
use PHPUnit\Framework\TestCase;

class CommissionPeriodCalendarTest extends TestCase
{
    public function test_period_value_for_dates(): void
    {
        $this->assertSame('2026-09', CommissionPeriod::getPeriodValueForDate('2026-10-07'));
        $this->assertSame('2026-10', CommissionPeriod::getPeriodValueForDate('2026-10-08'));
        $this->assertSame('2026-10', CommissionPeriod::getPeriodValueForDate('2026-11-07'));
        $this->assertSame('2026-11', CommissionPeriod::getPeriodValueForDate('2026-11-08'));
    }

    public function test_closed_period_on_eighth(): void
    {
        $this->assertSame('2026-10', CommissionPeriod::getClosedPeriodValue('2026-11-08'));
    }

    public function test_payment_period_on_fifteenth(): void
    {
        $this->assertSame('2026-10', CommissionPeriod::getPaymentPeriodValue('2026-11-15'));
    }
}
