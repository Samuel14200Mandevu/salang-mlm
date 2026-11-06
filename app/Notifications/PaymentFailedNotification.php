<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected float|int|string $amount,
        protected string $method,
        protected int|string $transactionId,
        protected ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format((float) $this->amount, 2, '.', ' ');
        $mail = (new MailMessage)
            ->subject('Paiement non abouti — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre tentative de dépôt n\'a pas abouti.')
            ->line('**Montant :** $'.$amount)
            ->line('**Méthode :** '.$this->method);

        if ($this->reason) {
            $mail->line('**Motif :** '.$this->reason);
        }

        return $mail
            ->line('Aucun montant n\'a été débité de votre portefeuille Salang. Vous pouvez réessayer un dépôt.')
            ->action('Réessayer un dépôt', url('/wallet'))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        $amount = (float) $this->amount;
        $message = 'Votre paiement de $'.number_format($amount, 2, '.', ' ').' via '.$this->method.' n\'a pas abouti.';
        if ($this->reason) {
            $message .= ' Motif : '.$this->reason;
        }

        return [
            'type' => 'payment_failed',
            'title' => 'Paiement non abouti',
            'amount' => $amount,
            'method' => $this->method,
            'transaction_id' => $this->transactionId,
            'reason' => $this->reason,
            'message' => $message,
            'action_url' => url('/wallet'),
        ];
    }
}
