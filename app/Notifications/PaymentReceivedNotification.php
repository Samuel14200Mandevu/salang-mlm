<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected float|int|string $amount,
        protected string $method,
        protected int|string $transactionId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format((float) $this->amount, 2, '.', ' ');

        return (new MailMessage)
            ->subject('Dépôt reçu — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre dépôt a été crédité avec succès.')
            ->line('**Montant :** $'.$amount)
            ->line('**Méthode :** '.$this->method)
            ->action('Voir mon portefeuille', url('/wallet'))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        $amount = (float) $this->amount;

        return [
            'type' => 'payment_received',
            'title' => 'Dépôt reçu',
            'amount' => $amount,
            'method' => $this->method,
            'transaction_id' => $this->transactionId,
            'message' => 'Votre portefeuille a été crédité de $'.number_format($amount, 2, '.', ' ').' via '.$this->method.'.',
            'action_url' => url('/wallet'),
        ];
    }
}
