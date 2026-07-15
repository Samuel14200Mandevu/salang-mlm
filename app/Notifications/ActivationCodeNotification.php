<?php
// app/Notifications/ActivationCodeNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $activationCode;

    public function __construct(string $activationCode)
    {
        $this->activationCode = $activationCode;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Activation de votre compte - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Votre compte a été créé avec succès. Pour l\'activer, vous devez :')
            ->line('1. Acheter un package en ligne')
            ->line('2. Ou utiliser le code d\'activation ci-dessous si vous avez déjà payé votre package au guichet.')
            ->line('')
            ->line('**Votre code d\'activation :** ' . $this->activationCode)
            ->line('Ce code est valable 7 jours.')
            ->line('')
            ->action('Activer mon compte', route('activate.account', ['code' => $this->activationCode]))
            ->line('')
            ->line('Si vous avez déjà payé votre package au guichet, entrez ce code dans la section activation.')
            ->line('')
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Code d\'activation de compte',
            'message' => 'Votre code d\'activation est : ' . $this->activationCode,
            'code' => $this->activationCode,
            'expires_in' => '7 jours',
            'action_url' => route('activate.account', ['code' => $this->activationCode]),
        ];
    }
}