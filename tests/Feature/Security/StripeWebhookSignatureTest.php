<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class StripeWebhookSignatureTest extends TestCase
{
    private const WEBHOOK_SECRET = 'whsec_test_secret';

    public function test_valid_stripe_signature_is_accepted(): void
    {
        config(['cashier.webhook.secret' => self::WEBHOOK_SECRET]);

        $payload = json_encode(['type' => 'invoice.payment_succeeded', 'data' => ['object' => []]]);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", self::WEBHOOK_SECRET);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $response->assertSuccessful();
    }

    public function test_invalid_stripe_signature_is_rejected(): void
    {
        config(['cashier.webhook.secret' => self::WEBHOOK_SECRET]);

        $payload = json_encode(['type' => 'invoice.payment_succeeded']);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=1234567890,v1=invalid',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $response->assertStatus(403);
    }

    public function test_missing_stripe_signature_header_is_rejected(): void
    {
        config(['cashier.webhook.secret' => self::WEBHOOK_SECRET]);

        $payload = json_encode(['type' => 'invoice.payment_succeeded']);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $response->assertStatus(403);
    }

    public function test_documents_unverified_webhook_when_secret_is_absent(): void
    {
        $this->markTestSkipped(
            'C1 — Quand cashier.webhook.secret = null, VerifyWebhookSignature '
            . 'N\'est PAS appliqué (WebhookController.php:27-31). '
            . 'Mitigation : STRIPE_WEBHOOK_SECRET configuré en prod (AUDIT.md §D10).'
        );
    }
}
