<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivationRequestReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $activateUrl = route('activate.index');
        $packagesUrl = route('subscriptions.index');

        return [
            'type' => 'activation_request_received',
            'title' => 'Demande d\'activation envoyée',
            'message' => 'Votre demande a été transmise à l\'administration. En attendant, vous pouvez aussi activer votre compte en souscrivant un package en ligne ('.$packagesUrl.') ou en saisissant votre code guichet sur la page d\'activation ('.$activateUrl.').',
            'action_url' => $activateUrl,
        ];
    }
}
