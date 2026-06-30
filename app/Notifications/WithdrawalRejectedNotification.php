<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class WithdrawalRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $reason;
    protected $withdrawalId;

    public function __construct($amount, $reason, $withdrawalId)
    {
        $this->amount = $amount;
        $this->reason = $reason;
        $this->withdrawalId = $withdrawalId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('❌ Retrait rejeté - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre demande de retrait a été **rejetée**.')
            ->line('')
            ->line("**Montant:** \${$this->amount}")
            ->line('')
            ->line("**Motif:** {$this->reason}")
            ->line('')
            ->line('Votre solde a été recrédité du montant demandé.')
            ->line('Vous pouvez faire une nouvelle demande de retrait.')
            ->action('Faire une demande', url('/withdrawal'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'withdrawal_rejected',
            'amount' => $this->amount,
            'reason' => $this->reason,
            'withdrawal_id' => $this->withdrawalId,
            'message' => "Votre retrait de \${$this->amount} a été rejeté",
        ];
    }
}