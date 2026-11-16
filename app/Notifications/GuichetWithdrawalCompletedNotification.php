<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le membre après remise espèces au guichet (synchrone : pas de worker requis).
 */
class GuichetWithdrawalCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal,
        public bool $withMail = true
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($this->withMail && filled($notifiable->email ?? null)) {
            array_unshift($channels, 'mail');
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format((float) $this->withdrawal->amount, 2);
        $url = url('/withdrawal/'.$this->withdrawal->id);
        $receipt = $this->withdrawal->receipt_number;
        $cashier = $this->withdrawal->cashier?->name;

        $mail = (new MailMessage)
            ->subject('Paiement réussi — retrait guichet Salang')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Votre retrait guichet a été remis avec succès au comptoir.')
            ->line('')
            ->line('Montant remis : $'.$amount);

        if (filled($receipt)) {
            $mail->line('N° reçu : '.$receipt);
        }

        if (filled($cashier)) {
            $mail->line('Caissier : '.$cashier);
        }

        return $mail
            ->line('')
            ->action('Voir le retrait', $url)
            ->line('Merci de votre confiance.')
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
