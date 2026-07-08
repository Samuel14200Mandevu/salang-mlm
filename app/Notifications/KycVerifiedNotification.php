<?php
// app/Notifications/KycVerifiedNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class KycVerifiedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('KYC Vérifié - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Félicitations ! Votre vérification KYC a été **approuvée**.')
            ->line('')
            ->line('Vous pouvez maintenant :')
            ->line('- Effectuer des retraits plus rapidement')
            ->line('- Accéder à toutes les fonctionnalités')
            ->line('- Gagner des commissions plus élevées')
            ->line('')
            ->action('Voir mon statut KYC', url('/kyc'))
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'kyc_verified',
            'message' => 'Votre KYC a été vérifié avec succès',
        ];
    }
}