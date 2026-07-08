<?php
// app/Notifications/KycRejectedNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class KycRejectedNotification extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('KYC Rejeté - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre vérification KYC a été **rejetée**.')
            ->line('')
            ->line("**Motif:** {$this->reason}")
            ->line('')
            ->line('Veuillez soumettre à nouveau vos documents en corrigeant le problème.')
            ->action('Soumettre mes documents', url('/kyc/create'))
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'kyc_rejected',
            'reason' => $this->reason,
            'message' => 'Votre KYC a été rejeté',
        ];
    }
}