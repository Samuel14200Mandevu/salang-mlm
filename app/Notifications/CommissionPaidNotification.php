<?php
// app/Notifications/CommissionPaidNotification.php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        $typeLabel = NotificationHelper::getCommissionTypeLabel($this->type);
        $amount = NotificationHelper::formatAmount($this->amount);

        return (new MailMessage)
            ->subject(NotificationHelper::getNotificationSubject('commission_paid', [
                'amount' => $this->amount
            ]))
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous informons qu\'une nouvelle commission a été créditée sur votre compte.')
            ->line('')
            ->line('Détails de la commission :')
            ->line("- Type : {$typeLabel}")
            ->line("- Montant : {$amount}")
            ->line('')
            ->line('Cette commission a été automatiquement ajoutée à votre portefeuille.')
            ->action('Consulter mon portefeuille', url('/wallet'))
            ->line('')
            ->line('Cordialement,')
            ->salutation("L'équipe Salang");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'commission_paid',
            'amount' => $this->amount,
            'commission_type' => $this->type,
            'commission_id' => $this->commissionId,
            'message' => NotificationHelper::getNotificationMessage([
                'type' => 'commission_paid',
                'amount' => $this->amount,
                'commission_type' => $this->type,
            ]),
        ];
    }
}