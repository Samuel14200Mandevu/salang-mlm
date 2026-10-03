<?php

namespace App\Services\MLM;

final class PaymentEligibilityResult
{
    public const OUTCOME_CREDIT = 'credit';

    public const OUTCOME_DEFERRED = 'deferred';

    public function __construct(
        public readonly string $outcome,
        public readonly ?string $reasonCode = null,
        public readonly ?string $message = null,
    ) {}

    public function shouldCreditWallet(): bool
    {
        return $this->outcome === self::OUTCOME_CREDIT;
    }

    public function shouldCreateDeferredPayment(): bool
    {
        return $this->outcome === self::OUTCOME_DEFERRED;
    }

    public static function credit(): self
    {
        return new self(self::OUTCOME_CREDIT);
    }

    public static function deferred(string $reasonCode, string $message): self
    {
        return new self(self::OUTCOME_DEFERRED, $reasonCode, $message);
    }
}
