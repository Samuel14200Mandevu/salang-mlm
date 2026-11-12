<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GuichetWithdrawalRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $user = $this->withdrawal->user;
        $amount = number_format((float) $this->withdrawal->amount, 2);

        return [
            'type' => 'guichet_withdrawal_requested',
            'withdrawal_id' => $this->withdrawal->id,
            'user_id' => $this->withdrawal->user_id,
            'member_name' => $user?->name,
            'sponsor_id' => $user?->sponsor_id,
            'amount' => (float) $this->withdrawal->amount,
            'message' => ($user?->name ?? 'Membre')." demande un retrait guichet de \${$amount}",
            'url' => url('/cashier/guichet/'.$this->withdrawal->id),
        ];
    }
}
