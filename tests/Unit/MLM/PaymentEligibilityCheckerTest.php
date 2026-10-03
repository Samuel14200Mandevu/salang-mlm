<?php

namespace Tests\Unit\MLM;

use App\Models\User;
use App\Services\MLM\PaymentEligibilityChecker;
use App\Services\MLM\PaymentEligibilityResult;
use Tests\TestCase;

class PaymentEligibilityCheckerTest extends TestCase
{
    private PaymentEligibilityChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = new PaymentEligibilityChecker;
    }

    public function test_credits_when_kyc_disabled_and_user_not_verified(): void
    {
        config([
            'commission.payment_validation.require_kyc' => false,
            'commission.payment_validation.require_monthly_pv' => false,
            'commission.payment_validation.require_rank' => false,
            'commission.min_payment' => 1,
        ]);

        $user = new User([
            'kyc_status' => 'not_submitted',
            'is_active' => true,
        ]);

        $result = $this->checker->evaluate($user, '2026-04', 50.0);

        $this->assertTrue($result->shouldCreditWallet());
    }

    public function test_defers_when_kyc_required_and_not_verified(): void
    {
        config(['commission.payment_validation.require_kyc' => true]);

        config([
            'commission.payment_validation.require_monthly_pv' => false,
            'commission.payment_validation.require_rank' => false,
        ]);

        $user = new User([
            'kyc_status' => 'pending',
            'is_active' => true,
        ]);

        $result = $this->checker->evaluate($user, '2026-04', 100.0);

        $this->assertSame(PaymentEligibilityResult::OUTCOME_DEFERRED, $result->outcome);
        $this->assertSame('kyc_pending', $result->reasonCode);
    }

    public function test_defers_inactive_account_instead_of_silent_skip(): void
    {
        config([
            'commission.payment_validation.require_kyc' => false,
            'commission.payment_validation.require_active_account' => true,
            'commission.payment_validation.require_monthly_pv' => false,
        ]);

        config(['commission.payment_validation.require_rank' => false]);

        $user = new User([
            'kyc_status' => 'verified',
            'is_active' => false,
        ]);

        $result = $this->checker->evaluate($user, '2026-04', 100.0);

        $this->assertTrue($result->shouldCreateDeferredPayment());
        $this->assertSame('account_inactive', $result->reasonCode);
    }

    public function test_defers_when_rank_missing_and_required(): void
    {
        config([
            'commission.payment_validation.require_kyc' => false,
            'commission.payment_validation.require_rank' => true,
            'commission.payment_validation.require_monthly_pv' => false,
        ]);

        $user = new User([
            'kyc_status' => 'verified',
            'is_active' => true,
        ]);
        $user->setRelation('rankObject', null);

        $result = $this->checker->evaluate($user, '2026-04', 100.0);

        $this->assertSame('no_rank', $result->reasonCode);
    }

    public function test_calculate_net_applies_tax_rate(): void
    {
        config(['commission.tax_rate' => 5]);

        $net = $this->checker->calculateNetAmount(100.0);

        $this->assertEqualsWithDelta(5.0, $net['tax_amount'], 0.001);
        $this->assertEqualsWithDelta(95.0, $net['net_amount'], 0.001);
    }
}
