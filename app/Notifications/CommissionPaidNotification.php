<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommissionPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $type;
    protected $commissionId;

    public function __construct($amount, $type, $commissionId)
    {
        $this->amount = $amount;
        $this->type = $type;
        $this->commissionId = $commissionId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $typeLabels = [
            'direct' => 'Directe',
            'indirect' => 'Indirecte',
            'leadership' => 'Leadership',
            'retail' => 'Retail',
            'bonus' => 'Bonus'
        ];

        $typeLabel = $typeLabels[$this->type] ?? ucfirst($this->type);

        return (new MailMessage)
            ->subject('💰 Nouvelle commission reçue - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line("Vous avez reçu une nouvelle commission de type **{$typeLabel}**.")
            ->line("")
            ->line("**Montant:** \${$this->amount}")
            ->line("")
            ->line('Cette commission a été automatiquement créditée sur votre portefeuille.')
            ->action('Voir mon portefeuille', url('/wallet'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'commission_paid',
            'amount' => $this->amount,
            'commission_type' => $this->type,
            'commission_id' => $this->commissionId,
            'message' => "Vous avez reçu \${$this->amount} de commission " . ucfirst($this->type),
        ];
    }
}