<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envoyé en synchrone après adhésion caissier pour que l’e-mail (identifiants)
 * parte immédiatement, sans dépendre d’un worker de file.
 */
class CashierMemberRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $plainPassword,
        protected string $memberCode,
        protected ?string $sponsorName = null,
        protected ?string $packageName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url('/login');

        $mail = (new MailMessage)
            ->subject('Bienvenue — vos accès Salang Group')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Votre adhésion Salang Group a été enregistrée avec succès.')
            ->line('');

        if ($this->sponsorName) {
            $mail->line("Parrain : **{$this->sponsorName}**.");
        }

        if ($this->packageName) {
            $mail->line("Package : **{$this->packageName}**.");
        }

        $mail->line('')
            ->line('Voici vos identifiants de connexion :')
            ->line('**Email :** '.$notifiable->email)
            ->line('**Mot de passe :** '.$this->plainPassword)
            ->line('**Code membre :** '.$this->memberCode)
            ->line('')
            ->line('Conservez ce mot de passe en lieu sûr. Vous pourrez le modifier après connexion.')
            ->action('Se connecter', $loginUrl)
            ->line('')
            ->line('Cordialement,')
            ->salutation("L'équipe Salang");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'cashier_member_registered',
            'member_code' => $this->memberCode,
            'sponsor_name' => $this->sponsorName,
            'package_name' => $this->packageName,
            'message' => NotificationHelper::getNotificationMessage([
                'type' => 'welcome',
                'sponsor_name' => $this->sponsorName,
            ]) ?: 'Votre compte membre Salang a été créé. Consultez votre e-mail pour vos identifiants.',
        ];
    }
}
