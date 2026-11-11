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

    public function __construct(
        protected ?string $sponsorName = null,
        protected bool $pendingActivation = false,
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        if ($this->pendingActivation) {
            return $this->pendingActivationMail($notifiable);
        }

        $mail = (new MailMessage)
            ->subject('Bienvenue sur Salang Group')
            ->greeting('Bonjour '.$notifiable->name.',')
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

    protected function pendingActivationMail($notifiable): MailMessage
    {
        $activateUrl = route('activate.index');
        $packagesUrl = route('subscriptions.index');

        $mail = (new MailMessage)
            ->subject('Compte créé avec succès — Bienvenue chez Salang Group')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre compte a été créé avec succès. Bienvenue chez **Salang Group**.')
            ->line('');

        if ($this->sponsorName) {
            $mail->line("Vous avez été parrainé par **{$this->sponsorName}**.");
            $mail->line('');
        }

        $mail->line('Votre compte est **en attente d’activation**. Pour l’activer, choisissez l’une des options suivantes :')
            ->line('')
            ->line('1. **Souscrire un package en ligne** depuis votre espace membre.')
            ->line('2. **Saisir le code d’activation** reçu au guichet (paiement en personne).')
            ->line('3. **Demander l’activation à l’administration** si vous avez déjà réglé votre adhésion.')
            ->line('')
            ->action('Activer mon compte', $activateUrl)
            ->line('')
            ->line("Vous pouvez aussi souscrire directement ici : {$packagesUrl}")
            ->line('')
            ->line('Une fois le compte activé, vous pourrez accéder aux commissions et développer votre réseau.')
            ->line('')
            ->line('Cordialement,')
            ->salutation("L'équipe Salang");

        return $mail;
    }

    public function toArray($notifiable)
    {
        if ($this->pendingActivation) {
            return [
                'type' => 'welcome',
                'sponsor_name' => $this->sponsorName,
                'pending_activation' => true,
                'title' => 'Compte créé — Bienvenue chez Salang Group',
                'message' => 'Votre compte a été créé avec succès. Activez-le en souscrivant un package en ligne, en saisissant votre code guichet, ou en demandant l’activation à l’administration.',
                'action_url' => route('activate.index'),
            ];
        }

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
