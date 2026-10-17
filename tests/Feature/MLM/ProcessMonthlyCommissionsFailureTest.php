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

class ProcessMonthlyCommissionsFailureTest extends TestCase
{
    use RefreshDatabase;

    private function createCalculatedPeriod(string $periodValue = '2025-11'): CommissionPeriod
    {
        return CommissionPeriod::create([
            'period' => $periodValue,
            'start_date' => Carbon::parse($periodValue . '-08'),
            'end_date' => Carbon::parse($periodValue . '-08')->addMonth()->day(7),
            'status' => 'calculated',
        ]);
    }

    private function mockPipelineWithPaymentResult(bool $paymentResult): MonthlyCommissionService
    {
        $mock = Mockery::mock(MonthlyCommissionService::class);
        $mock->shouldReceive('calculateMonthlyPVBV')->once()->andReturn(true);
        $mock->shouldReceive('calculateMonthlyRanks')->once()->andReturn(true);
        $mock->shouldReceive('calculateMonthlyCommissions')->once()->andReturn(true);
        $mock->shouldReceive('generatePayments')->once()->andReturn($paymentResult);

        return $mock;
    }

    public function test_generate_payments_false_throws_and_marks_period_failed(): void
    {
        $periodValue = '2025-11';
        $period = $this->createCalculatedPeriod($periodValue);

        $job = new ProcessMonthlyCommissions($periodValue, false);

        try {
            $job->handle($this->mockPipelineWithPaymentResult(false));
            $this->fail('Expected RuntimeException when generatePayments returns false');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Failed to generate payments', $exception->getMessage());
        }

        $period->refresh();
        $this->assertSame(CommissionPeriod::STATUS_FAILED, $period->status);
        $this->assertStringContainsString('Failed to generate payments', $period->notes ?? '');
    }

    public function test_generate_payments_true_completes_without_marking_failed(): void
    {
        $periodValue = '2025-12';
        $period = $this->createCalculatedPeriod($periodValue);

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $job->handle($this->mockPipelineWithPaymentResult(true));

        $period->refresh();
        $this->assertNotSame(CommissionPeriod::STATUS_FAILED, $period->status);
    }

    public function test_failed_callback_records_payment_failure_message(): void
    {
        $periodValue = '2025-10';
        CommissionPeriod::create([
            'period' => $periodValue,
            'start_date' => Carbon::parse($periodValue . '-08'),
            'end_date' => Carbon::parse($periodValue . '-08')->addMonth()->day(7),
            'status' => 'paying',
        ]);

        $job = new ProcessMonthlyCommissions($periodValue, false);
        $message = 'Failed to generate payments for period ' . $periodValue;
        $job->failed(new RuntimeException($message));

        $period = CommissionPeriod::where('period', $periodValue)->firstOrFail();
        $this->assertSame(CommissionPeriod::STATUS_FAILED, $period->status);
        $this->assertStringContainsString('Failed to generate payments', $period->notes ?? '');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
