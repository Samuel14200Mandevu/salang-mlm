<?php

namespace Tests\Unit\MLM;

use App\Support\MlmPeriod;
use PHPUnit\Framework\TestCase;

class MlmPeriodTest extends TestCase
{
    public function test_between_first_and_seventh_uses_previous_mlm_month(): void
    {
        $this->assertSame('2026-09', MlmPeriod::current('2026-10-01'));
        $this->assertSame('2026-09', MlmPeriod::current('2026-10-07'));
        $this->assertSame('2026-10', MlmPeriod::current('2026-10-08'));
    }

    public function test_is_current_for_fixed_date(): void
    {
        $this->assertTrue(MlmPeriod::forDate('2026-10-15') === '2026-10');
        $this->assertTrue(MlmPeriod::forDate('2026-11-03') === '2026-10');
    }
}
