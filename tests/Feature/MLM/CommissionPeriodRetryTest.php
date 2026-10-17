<?php

namespace Tests\Feature\MLM;

use App\Jobs\ProcessMonthlyCommissions;
use App\Models\CommissionPeriod;
use App\Services\MLM\MonthlyCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CommissionPeriodRetryTest extends TestCase
{
    use RefreshDatabase;

    private function createPeriod(string $periodValue, string $status, ?Carbon $updatedAt = null): CommissionPeriod
    {
        $record = CommissionPeriod::create([
            'period' => $periodValue,
            'start_date' => Carbon::parse($periodValue . '-08'),
            'end_date' => Carbon::parse($periodValue . '-08')->addMonth()->day(7),
            'status' => $status,
        ]);

        if ($updatedAt !== null) {
            $record->updated_at = $updatedAt;
            $record->saveQuietly();
        }

        return $record->fresh();
    }

    private function mockSuccessfulClosePipeline(): MonthlyCommissionService
    {
        $mock = Mockery::mock(MonthlyCommissionService::class);
        $mock->shouldReceive('calculateMonthlyPVBV')->once()->andReturn(true);
        $mock->shouldReceive('calculateMonthlyRanks')->once()->andReturn(true);
        $mock->shouldReceive('calculateMonthlyCommissions')->once()->andReturn(true);
        $mock->shouldReceive('generatePayments')->once()->andReturn(true);

        return $mock;
    }

    public function test_guard_blocks_recent_calculating_period(): void
    {
        $periodValue = '2025-06';
        $this->createPeriod($periodValue, 'calculating', now());

        $mock = Mockery::mock(MonthlyCommissionService::class);
        $mock->shouldNotReceive('calculateMonthlyPVBV');

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $job->handle($mock);

        $this->assertSame('calculating', CommissionPeriod::where('period', $periodValue)->value('status'));
    }

    public function test_guard_allows_stale_calculating_period(): void
    {
        $periodValue = '2025-07';
        $this->createPeriod($periodValue, 'calculating', now()->subHours(2));

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $job->handle($this->mockSuccessfulClosePipeline());
    }

    public function test_failed_callback_marks_period_as_failed(): void
    {
        $periodValue = '2025-08';
        $this->createPeriod($periodValue, 'calculating');

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $job->failed(new RuntimeException('Simulated queue failure'));

        $period = CommissionPeriod::where('period', $periodValue)->firstOrFail();
        $this->assertSame(CommissionPeriod::STATUS_FAILED, $period->status);
        $this->assertStringContainsString('Simulated queue failure', $period->notes ?? '');
    }

    public function test_retry_is_allowed_when_period_status_is_failed(): void
    {
        $periodValue = '2025-09';
        $this->createPeriod($periodValue, CommissionPeriod::STATUS_FAILED, now());

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $job->handle($this->mockSuccessfulClosePipeline());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
