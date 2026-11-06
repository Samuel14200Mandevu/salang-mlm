<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LargeDepositAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected User $member,
        protected float $amount,
        protected string $method,
        protected int|string $transactionId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->amount, 2, '.', ' ');

        return (new MailMessage)
            ->subject('Dépôt important — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un dépôt important a été crédité.')
            ->line('**Membre :** '.$this->member->name)
            ->line('**Email :** '.$this->member->email)
            ->line('**Code parrain :** '.($this->member->sponsor_id ?? '—'))
            ->line('**Montant :** $'.$amount)
            ->line('**Méthode :** '.$this->method)
            ->line('**Transaction :** #'.$this->transactionId)
            ->action('Voir le portefeuille admin', url('/admin/wallets'))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->amount, 2, '.', ' ');

        return [
            'type' => 'large_deposit_admin',
            'title' => 'Dépôt important',
            'member_id' => $this->member->id,
            'member_name' => $this->member->name,
            'member_email' => $this->member->email,
            'amount' => $this->amount,
            'method' => $this->method,
            'transaction_id' => $this->transactionId,
            'message' => $this->member->name.' a déposé $'.$amount.' via '.$this->method.'.',
            'action_url' => url('/admin/wallets'),
        ];
    }
}
