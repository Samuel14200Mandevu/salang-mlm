<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Vérification d'e-mail via Resend — mise en file (ShouldQueue)
 * pour passer par le worker Laravel Cloud comme ActivationCodeNotification.
 */
class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Vérifiez votre adresse e-mail — Salang MLM')
            ->greeting('Bonjour !')
            ->line('Merci de confirmer votre adresse e-mail en cliquant sur le bouton ci-dessous.')
            ->action('Vérifier mon e-mail', $url)
            ->line('Si vous n’avez pas créé de compte, aucune action n’est requise.');
    }
}
