<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\LargeDepositAdminNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\PaymentWebhookFailureAdminNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentNotificationService
{
    /**
     * @param  array{notify?: string|null, transaction_id?: int|null, message?: string|null}  $result
     */
    public function dispatchFromCallbackResult(array $result, string $methodLabel): void
    {
        $notify = $result['notify'] ?? null;
        $transactionId = $result['transaction_id'] ?? null;
        if (! $notify || ! $transactionId) {
            return;
        }

        $transaction = Transaction::find($transactionId);
        if (! $transaction) {
            return;
        }

        if ($notify === 'completed') {
            $this->notifyDepositSucceeded($transaction, $methodLabel);
        } elseif ($notify === 'failed') {
            $this->notifyDepositFailed(
                $transaction,
                $methodLabel,
                $result['message'] ?? null
            );
        }
    }

    /**
     * Notifie le membre (et éventuellement les admins) après un dépôt réussi.
     * À appeler uniquement lors de la première transition pending → completed.
     */
    public function notifyDepositSucceeded(Transaction $transaction, string $method): void
    {
        $user = User::find($transaction->user_id);
        if (! $user) {
            return;
        }

        $amount = (float) $transaction->amount;
        $transactionId = (int) $transaction->id;

        try {
            $user->notify(new PaymentReceivedNotification($amount, $method, $transactionId));
        } catch (\Throwable $e) {
            Log::error('PaymentNotificationService: succès membre échoué', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
        }

        $threshold = (float) config('payment.alerts.large_deposit_threshold', 500);
        if ($amount >= $threshold) {
            $this->notifyAdminsLargeDeposit($user, $amount, $method, $transactionId);
        }
    }

    /**
     * Notifie le membre après un échec / annulation définitive.
     * À appeler uniquement lors de la première transition pending → failed.
     */
    public function notifyDepositFailed(
        Transaction $transaction,
        string $method,
        ?string $reason = null,
    ): void {
        $user = User::find($transaction->user_id);
        if (! $user) {
            return;
        }

        $amount = (float) $transaction->amount;
        $transactionId = (int) $transaction->id;

        try {
            $user->notify(new PaymentFailedNotification($amount, $method, $transactionId, $reason));
        } catch (\Throwable $e) {
            Log::error('PaymentNotificationService: échec membre échoué', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Compte les échecs webhook et alerte les admins au seuil.
     */
    public function recordWebhookFailure(string $provider, string $reason): void
    {
        $key = 'payment_webhook_failures:'.$provider;
        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addHour());

        $threshold = (int) config('payment.alerts.webhook_failure_threshold', 5);
        if ($count < $threshold) {
            return;
        }

        $alertKey = 'payment_webhook_failures_alerted:'.$provider;
        if (! Cache::add($alertKey, true, now()->addHour())) {
            return;
        }

        foreach ($this->adminRecipients() as $admin) {
            try {
                $admin->notify(new PaymentWebhookFailureAdminNotification(
                    $provider,
                    $count,
                    $reason,
                ));
            } catch (\Throwable $e) {
                Log::error('PaymentNotificationService: alerte webhook admin échouée', [
                    'admin_id' => $admin->id,
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::warning('PaymentNotificationService: seuil échecs webhook atteint', [
            'provider' => $provider,
            'count' => $count,
            'reason' => $reason,
        ]);
    }

    protected function notifyAdminsLargeDeposit(
        User $member,
        float $amount,
        string $method,
        int $transactionId,
    ): void {
        foreach ($this->adminRecipients() as $admin) {
            try {
                $admin->notify(new LargeDepositAdminNotification(
                    $member,
                    $amount,
                    $method,
                    $transactionId,
                ));
            } catch (\Throwable $e) {
                Log::error('PaymentNotificationService: dépôt important admin échoué', [
                    'admin_id' => $admin->id,
                    'member_id' => $member->id,
                    'transaction_id' => $transactionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function adminRecipients()
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'it_manager']);
            })
            ->get();
    }
}
