<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $method;
    protected $transactionId;

    public function __construct($amount, $method, $transactionId)
    {
        $this->amount = $amount;
        $this->method = $method;
        $this->transactionId = $transactionId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('💳 Paiement reçu - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous avez effectué un paiement avec succès.')
            ->line('')
            ->line("**Montant:** \${$this->amount}")
            ->line("**Méthode:** {$this->method}")
            ->line('')
            ->line('Votre portefeuille a été crédité du montant correspondant.')
            ->action('Voir mon portefeuille', url('/wallet'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_received',
            'amount' => $this->amount,
            'method' => $this->method,
            'transaction_id' => $this->transactionId,
            'message' => "Vous avez reçu un paiement de \${$this->amount} via {$this->method}",
        ];
    }
}