<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuichetWithdrawalCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal,
        public string $reason = ''
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

        $mail = (new MailMessage)
            ->subject('Retrait guichet annulé — Salang')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Votre retrait guichet a été **annulé**.')
            ->line('')
            ->line("**Montant :** \${$amount}")
            ->line('Le montant a été recrédité sur votre portefeuille.');

        if (filled($this->reason)) {
            $mail->line('')
                ->line('**Motif :** '.$this->reason);
        }

        return $mail
            ->line('')
            ->action('Voir le retrait', $url)
            ->salutation("L'équipe Salang");
    }

    public function toArray($notifiable): array
    {
        $amount = number_format((float) $this->withdrawal->amount, 2);

        return [
            'type' => 'guichet_withdrawal_cancelled',
            'ui' => 'danger',
            'ui_type' => 'danger',
            'title' => 'Retrait guichet annulé',
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => (float) $this->withdrawal->amount,
            'reason' => $this->reason,
            'message' => "Votre retrait guichet de \${$amount} a été annulé. Le montant a été recrédité sur votre portefeuille.",
            'action_url' => url('/withdrawal/'.$this->withdrawal->id),
            'url' => url('/withdrawal/'.$this->withdrawal->id),
        ];
    }
}
