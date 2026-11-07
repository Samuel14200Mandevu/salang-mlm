<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StaffAnnouncementNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'staff_announcement',
            'tone' => 'info',
            'category' => 'announcement',
            'action' => 'info',
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'ui_type' => 'info',
        ];
    }
}
