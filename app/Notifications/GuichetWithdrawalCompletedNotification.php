<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuichetWithdrawalCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format((float) $this->withdrawal->amount, 2);
        $url = url('/withdrawal/'.$this->withdrawal->id);
        $receipt = $this->withdrawal->receipt_number;

        $mail = (new MailMessage)
            ->subject('Paiement réussi — retrait guichet Salang')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Votre retrait guichet a été **remis avec succès**.')
            ->line('')
            ->line("**Montant :** \${$amount}");

        if (filled($receipt)) {
            $mail->line("**Reçu :** {$receipt}");
        }

        return $mail
            ->line('')
            ->action('Voir le retrait', $url)
            ->salutation("L'équipe Salang");
    }

    public function toArray($notifiable): array
    {
        $amount = number_format((float) $this->withdrawal->amount, 2);
        $url = url('/withdrawal/'.$this->withdrawal->id);

        return [
            'type' => 'guichet_withdrawal_completed',
            'ui' => 'success',
            'ui_type' => 'success',
            'title' => 'Paiement réussi !',
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => (float) $this->withdrawal->amount,
            'receipt_number' => $this->withdrawal->receipt_number,
            'message' => "Votre retrait guichet de \${$amount} a été remis avec succès.",
            'action_url' => $url,
            'url' => $url,
        ];
    }
}
