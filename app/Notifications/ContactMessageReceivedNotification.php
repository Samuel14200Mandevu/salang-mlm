<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected ContactMessage $contactMessage) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $msg = $this->contactMessage;
        $preview = \Illuminate\Support\Str::limit(trim((string) $msg->message), 280);

        return (new MailMessage)
            ->subject('Nouveau message contact — '.$msg->subject)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Un visiteur a laissé un message sur le site officiel.')
            ->line('**Nom :** '.$msg->name)
            ->line('**Email :** '.$msg->email)
            ->line('**Téléphone :** '.($msg->phone ?: '—'))
            ->line('**Sujet :** '.$msg->subject)
            ->line('**Message :**')
            ->line($preview)
            ->action('Voir le message', route('site-management.contacts.show', $msg))
            ->salutation('L\'équipe Salang MLM');
    }

    public function toArray(object $notifiable): array
    {
        $msg = $this->contactMessage;
        $url = route('site-management.contacts.show', $msg);

        return [
            'type' => 'contact_message_received',
            'category' => 'other',
            'contact_message_id' => $msg->id,
            'visitor_name' => $msg->name,
            'visitor_email' => $msg->email,
            'subject' => $msg->subject,
            'title' => 'Nouveau message contact',
            'message' => $msg->name.' — '.$msg->subject,
            'url' => $url,
            'action_url' => $url,
        ];
    }
}
