<?php
// app/Notifications/WelcomeNotification.php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $sponsorName;

    public function __construct($sponsorName = null)
    {
        $this->sponsorName = $sponsorName;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Bienvenue sur Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous sommes ravis de vous accueillir au sein de la communauté Salang.')
            ->line('');

        if ($this->sponsorName) {
            $mail->line("Vous avez été parrainé par **{$this->sponsorName}**.");
            $mail->line('');
        }

        $mail->line('Pour commencer votre activité, voici les étapes à suivre :')
            ->line('')
            ->line('1. **Achetez un package** pour activer votre compte et accéder aux commissions.')
            ->line('2. **Partagez votre lien de parrainage** pour inviter de nouveaux membres.')
            ->line('3. **Développez votre réseau** pour augmenter vos gains.')
            ->line('')
            ->action('Découvrir les packages', url('/packages'))
            ->line('')
            ->line('Notre équipe reste à votre disposition pour toute question.')
            ->line('')
            ->line('Cordialement,')
            ->salutation("L'équipe Salang");

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'welcome',
            'sponsor_name' => $this->sponsorName,
            'message' => NotificationHelper::getNotificationMessage([
                'type' => 'welcome',
                'sponsor_name' => $this->sponsorName,
            ]),
        ];
    }
}