<?php

namespace Tests\Feature\Payment;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\LargeDepositAdminNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Services\PaymentNotificationService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PaymentNotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    public function test_dispatch_completed_notifies_member_once_and_admin_on_large_deposit(): void
    {
        Notification::fake();
        config(['payment.alerts.large_deposit_threshold' => 100]);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $member = User::factory()->create(['is_active' => true]);
        $wallet = Wallet::create([
            'user_id' => $member->id,
            'balance' => 0,
            'pending_balance' => 0,
            'total_withdrawn' => 0,
            'total_deposited' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'user_id' => $member->id,
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => 250,
            'fee' => 0,
            'net_amount' => 250,
            'balance_before' => 0,
            'balance_after' => 250,
            'status' => 'completed',
            'reference' => 'TEST-DEP-1',
            'provider' => 'flexpay',
        ]);

        $service = app(PaymentNotificationService::class);
        $service->dispatchFromCallbackResult([
            'notify' => 'completed',
            'transaction_id' => $tx->id,
        ], 'FlexPay');

        // Doublon simulé (2e webhook)
        $service->dispatchFromCallbackResult([
            'notify' => null,
            'transaction_id' => $tx->id,
        ], 'FlexPay');

        Notification::assertSentTo($member, PaymentReceivedNotification::class);
        Notification::assertSentTo($admin, LargeDepositAdminNotification::class);
        Notification::assertSentToTimes($member, PaymentReceivedNotification::class, 1);
    }

    public function test_dispatch_failed_notifies_member(): void
    {
        Notification::fake();

        $member = User::factory()->create(['is_active' => true]);
        $wallet = Wallet::create([
            'user_id' => $member->id,
            'balance' => 0,
            'pending_balance' => 0,
            'total_withdrawn' => 0,
            'total_deposited' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $tx = Transaction::create([
            'user_id' => $member->id,
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => 50,
            'fee' => 0,
            'net_amount' => 50,
            'balance_before' => 0,
            'balance_after' => 0,
            'status' => 'failed',
            'reference' => 'TEST-DEP-FAIL',
            'provider' => 'binance',
        ]);

        app(PaymentNotificationService::class)->dispatchFromCallbackResult([
            'notify' => 'failed',
            'transaction_id' => $tx->id,
            'message' => 'PAY_CLOSED',
        ], 'Binance Pay');

        Notification::assertSentTo($member, PaymentFailedNotification::class);
        Notification::assertNotSentTo($member, PaymentReceivedNotification::class);
    }

    public function test_payment_received_payload_has_title_and_action_url(): void
    {
        $notification = new PaymentReceivedNotification(12.5, 'FlexPay', 99);
        $data = $notification->toArray(User::factory()->make());

        $this->assertSame('payment_received', $data['type']);
        $this->assertSame('Dépôt reçu', $data['title']);
        $this->assertSame(url('/wallet'), $data['action_url']);
    }
}
