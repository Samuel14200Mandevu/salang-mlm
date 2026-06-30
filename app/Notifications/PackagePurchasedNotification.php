<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class PackagePurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $packageName;
    protected $price;
    protected $userId;

    public function __construct($packageName, $price, $userId)
    {
        $this->packageName = $packageName;
        $this->price = $price;
        $this->userId = $userId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🎉 Package acheté - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous avez acheté le package **' . $this->packageName . '** avec succès.')
            ->line('')
            ->line("**Package:** {$this->packageName}")
            ->line("**Prix:** \${$this->price}")
            ->line('')
            ->line('Vous pouvez maintenant commencer à gagner des commissions sur votre réseau.')
            ->line('Parrainez des membres et développez votre équipe !')
            ->action('Découvrir les packages', url('/packages'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'package_purchased',
            'package_name' => $this->packageName,
            'price' => $this->price,
            'message' => "Vous avez acheté le package {$this->packageName}",
        ];
    }
}