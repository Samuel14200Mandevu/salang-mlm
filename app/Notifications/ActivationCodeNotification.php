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
        $magicUrl = route('activate.account', ['code' => $this->activationCode]);
        $activatePageUrl = route('activate.index');
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
            ->line('1. Cliquez sur le bouton ci-dessous pour activer immédiatement (lien magique).')
            ->line('2. Ou saisissez le code guichet sur la page d\'activation (après connexion).')
            ->line('3. Ou achetez un package en ligne / demandez l\'activation à l\'administration.')
            ->action('Activer mon compte', $magicUrl)
            ->line('Lien de secours (saisie manuelle) : '.$activatePageUrl)
            ->line('Packages en ligne : '.$packagesUrl)
            ->salutation('L\'équipe Salang MLM');

        return $message;
    }

    public function toArray($notifiable): array
    {
        $magicUrl = route('activate.account', ['code' => $this->activationCode]);
        $activatePageUrl = route('activate.index');
        $packagesUrl = route('subscriptions.index');

        $instructions = 'Votre compte est inactif. Activez-le via le lien magique, ou saisissez le code '
            .$this->activationCode.' sur la page d\'activation (valable 7 jours).';

        $data = [
            'type' => 'account_pending_activation',
            'title' => 'Compte en attente d\'activation',
            'message' => $instructions,
            'code' => $this->activationCode,
            'expires_in' => '7 jours',
            'activate_url' => $activatePageUrl,
            'magic_url' => $magicUrl,
            'packages_url' => $packagesUrl,
            'action_url' => $magicUrl,
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