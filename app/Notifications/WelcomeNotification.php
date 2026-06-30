<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

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
            ->subject('👋 Bienvenue sur Salang MLM !')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Nous sommes ravis de vous accueillir dans la communauté Salang.')
            ->line('')
            ->line('Votre parcours vers la liberté financière commence ici.');

        if ($this->sponsorName) {
            $mail->line("Vous avez été parrainé par **{$this->sponsorName}**.");
        }

        $mail->line('')
            ->line('🚀 **Pour commencer :**')
            ->line('1. Achetez un package pour activer votre compte')
            ->line('2. Partagez votre lien de parrainage')
            ->line('3. Gagnez des commissions sur votre réseau')
            ->line('')
            ->action('Découvrir les packages', url('/packages'))
            ->line('')
            ->line('Nous sommes à votre disposition pour toute question.')
            ->line('Bienvenue dans la famille Salang !')
            ->salutation('L\'équipe Salang');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'welcome',
            'sponsor_name' => $this->sponsorName,
            'message' => 'Bienvenue sur Salang MLM !',
        ];
    }
}