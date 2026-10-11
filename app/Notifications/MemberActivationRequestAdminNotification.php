<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberActivationRequestAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected User $member) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande d\'activation de compte — Salang MLM')
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un membre demande l\'activation manuelle de son compte.')
            ->line('**Nom :** '.$this->member->name)
            ->line('**Email :** '.$this->member->email)
            ->line('**Code parrain :** '.$this->member->sponsor_id)
            ->action('Gérer les activations', route('admin.activations.index'))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_activation_request',
            'member_id' => $this->member->id,
            'member_name' => $this->member->name,
            'member_email' => $this->member->email,
            'title' => 'Demande d\'activation',
            'message' => $this->member->name.' demande l\'activation de son compte.',
            'action_url' => route('admin.activations.index'),
        ];
    }
}
