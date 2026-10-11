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
        $activateUrl = route('activate.index');
        $packagesUrl = route('subscriptions.index');

        $message = (new MailMessage)
            ->subject('Activez votre compte — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.' !')
            ->line('Votre compte a été créé et reste **inactif** tant que vous n\'avez pas finalisé l\'activation.')
            ->line('')
            ->line('**Code guichet (7 jours) :** '.$this->activationCode)
            ->line('');

        if ($this->package) {
            $message->line('Package associé : **'.$this->package->name.'**')
                ->line('Valeur : **$'.number_format($this->package->price, 2).'**')
                ->line('PV : '.$this->package->pv_value.' | BV : '.$this->package->bv_value)
                ->line('');
        }

        $message->line('**Comment activer votre compte :**')
            ->line('1. **Achat d\'un package en ligne** — souscrivez depuis votre espace membre.')
            ->line('2. **Paiement au guichet** — saisissez le code ci-dessus sur la page d\'activation.')
            ->line('3. **Demande à l\'administration** — depuis la page d\'activation, envoyez une demande ; l\'équipe pourra activer votre compte manuellement.')
            ->action('Page d\'activation', $activateUrl)
            ->line('Packages en ligne : '.$packagesUrl)
            ->salutation('L\'équipe Salang MLM');

        return $message;
    }

    public function toArray($notifiable): array
    {
        $activateUrl = route('activate.index');
        $packagesUrl = route('subscriptions.index');

        $instructions = 'Votre compte est inactif. Pour l\'activer : (1) achetez un package en ligne ; '
            .'(2) payez au guichet et saisissez votre code '.$this->activationCode.' ; '
            .'(3) demandez l\'activation à l\'administration depuis la page d\'activation. '
            .'Code valable 7 jours.';

        $data = [
            'type' => 'account_pending_activation',
            'title' => 'Compte en attente d\'activation',
            'message' => $instructions,
            'code' => $this->activationCode,
            'expires_in' => '7 jours',
            'activate_url' => $activateUrl,
            'packages_url' => $packagesUrl,
            'action_url' => $activateUrl,
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