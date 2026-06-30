<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class WithdrawalApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $method;
    protected $netAmount;
    protected $withdrawalId;

    public function __construct($amount, $method, $netAmount, $withdrawalId)
    {
        $this->amount = $amount;
        $this->method = $method;
        $this->netAmount = $netAmount;
        $this->withdrawalId = $withdrawalId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $methodLabels = [
            'crypto' => 'Cryptomonnaie',
            'mobile_money' => 'Mobile Money',
            'bank' => 'Virement bancaire'
        ];

        $methodLabel = $methodLabels[$this->method] ?? ucfirst($this->method);

        return (new MailMessage)
            ->subject('✅ Retrait approuvé - Salang MLM')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre demande de retrait a été **approuvée** avec succès.')
            ->line('')
            ->line("**Montant demandé:** \${$this->amount}")
            ->line("**Frais (2.5%):** \$" . number_format($this->amount - $this->netAmount, 2))
            ->line("**Montant net:** \${$this->netAmount}")
            ->line("**Méthode:** {$methodLabel}")
            ->line('')
            ->line('Le transfert sera effectué dans les plus brefs délais.')
            ->action('Suivre mes retraits', url('/withdrawal'))
            ->line('')
            ->line('Merci de faire partie de la communauté Salang !')
            ->salutation('L\'équipe Salang');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'withdrawal_approved',
            'amount' => $this->amount,
            'net_amount' => $this->netAmount,
            'method' => $this->method,
            'withdrawal_id' => $this->withdrawalId,
            'message' => "Votre retrait de \${$this->netAmount} a été approuvé",
        ];
    }
}