<?php
// app/Notifications/ActivationCodeNotification.php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $activationCode;
    protected ?Package $package;

    public function __construct(string $activationCode, ?Package $package = null)
    {
        $this->activationCode = $activationCode;
        $this->package = $package;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Code d\'activation - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Voici votre code d\'activation :')
            ->line('')
            ->line('**Code :** ' . $this->activationCode)
            ->line('');

        if ($this->package) {
            $message->line('Package associe : **' . $this->package->name . '**')
                    ->line('Valeur : **$' . number_format($this->package->price, 2) . '**')
                    ->line('PV : ' . $this->package->pv_value . ' | BV : ' . $this->package->bv_value)
                    ->line('');
        }

        $message->line('Ce code est valable 7 jours.')
                ->action('Activer mon compte', route('activate.account', ['code' => $this->activationCode]))
                ->line('')
                ->line('Si vous avez déjà payé votre package au guichet, entrez ce code dans la section activation.')
                ->line('')
                ->salutation('L\'équipe Salang MLM');

        return $message;
    }

    public function toArray($notifiable): array
    {
        $data = [
            'title' => 'Code d\'activation de compte',
            'message' => 'Votre code d\'activation est : ' . $this->activationCode,
            'code' => $this->activationCode,
            'expires_in' => '7 jours',
            'action_url' => route('activate.account', ['code' => $this->activationCode]),
        ];

        if ($this->package) {
            $data['package'] = [
                'name' => $this->package->name,
                'price' => $this->package->price,
                'pv_value' => $this->package->pv_value,
                'bv_value' => $this->package->bv_value,
            ];
        }

        return $data;
    }
}