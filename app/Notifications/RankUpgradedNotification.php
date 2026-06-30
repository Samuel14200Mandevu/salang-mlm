<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class RankUpgradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $oldRank;
    protected $newRank;
    protected $pv;

    public function __construct($oldRank, $newRank, $pv)
    {
        $this->oldRank = $oldRank;
        $this->newRank = $newRank;
        $this->pv = $pv;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🏅 Promotion de rang - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Félicitations ! Vous venez d\'être **promu** à un nouveau rang.')
            ->line('')
            ->line("**Ancien rang:** {$this->oldRank}")
            ->line("**Nouveau rang:** 🎉 **{$this->newRank}**")
            ->line("**PV actuel:** {$this->pv}")
            ->line('')
            ->line('Avec ce nouveau rang, vous bénéficiez de bonus supplémentaires sur vos commissions.')
            ->line('Continuez à développer votre réseau pour atteindre les rangs supérieurs !')
            ->action('Suivre ma progression', url('/rank'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'rank_upgraded',
            'old_rank' => $this->oldRank,
            'new_rank' => $this->newRank,
            'pv' => $this->pv,
            'message' => "Félicitations ! Vous êtes maintenant {$this->newRank}",
        ];
    }
}