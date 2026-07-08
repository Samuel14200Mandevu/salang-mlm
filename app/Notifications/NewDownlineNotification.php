<?php
// app/Notifications/NewDownlineNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewDownlineNotification extends Notification
{
    use Queueable;

    protected $downlineName;
    protected $downlineEmail;
    protected $level;

    public function __construct($downlineName, $downlineEmail, $level)
    {
        $this->downlineName = $downlineName;
        $this->downlineEmail = $downlineEmail;
        $this->level = $level;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('👤 Nouveau membre dans votre réseau - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line("Un nouveau membre a rejoint votre réseau !")
            ->line('')
            ->line("**Nom:** {$this->downlineName}")
            ->line("**Email:** {$this->downlineEmail}")
            ->line("**Niveau:** Niveau {$this->level}")
            ->line('')
            ->line('Continuez à développer votre réseau pour gagner plus de commissions !')
            ->action('Voir mon réseau', url('/network'))
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_downline',
            'downline_name' => $this->downlineName,
            'downline_email' => $this->downlineEmail,
            'level' => $this->level,
            'message' => "{$this->downlineName} a rejoint votre réseau (Niveau {$this->level})",
        ];
    }
}