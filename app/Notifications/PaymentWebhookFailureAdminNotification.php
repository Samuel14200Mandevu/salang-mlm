<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentWebhookFailureAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $provider,
        protected int $failureCount,
        protected string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Échecs webhook paiement — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Des échecs répétés ont été détectés sur un webhook de paiement.')
            ->line('**Provider :** '.$this->provider)
            ->line('**Échecs (1 h) :** '.$this->failureCount)
            ->line('**Dernier motif :** '.$this->reason)
            ->action('Voir les portefeuilles', url('/admin/wallets'))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_webhook_failure_admin',
            'title' => 'Échecs webhook paiement',
            'provider' => $this->provider,
            'failure_count' => $this->failureCount,
            'reason' => $this->reason,
            'message' => $this->provider.' : '.$this->failureCount.' échec(s) webhook en 1 h. '.$this->reason,
            'action_url' => url('/admin/wallets'),
        ];
    }
}
