<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Notifie le membre après remise espèces au guichet (synchrone : pas de worker requis).
 * Joint le reçu PDF dans l’e-mail.
 */
class GuichetWithdrawalCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Withdrawal $withdrawal,
        public bool $withMail = true
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($this->withMail && filled($notifiable->email ?? null)) {
            array_unshift($channels, 'mail');
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $this->withdrawal->loadMissing(['user', 'cashier']);

        $amount = number_format((float) $this->withdrawal->amount, 2);
        $url = url('/withdrawal/'.$this->withdrawal->id);
        $receipt = $this->withdrawal->receipt_number;
        $cashier = $this->withdrawal->cashier?->name;
        $phaseLabel = $this->bonusPhaseLabel();

        $mail = (new MailMessage)
            ->subject('Votre reçu — retrait guichet Salang')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Votre retrait guichet a été remis avec succès au comptoir.')
            ->line('')
            ->line('Montant remis : $'.$amount);

        if ($phaseLabel) {
            $mail->line('Type de paiement : '.$phaseLabel);
        }

        if (filled($receipt)) {
            $mail->line('N° reçu : '.$receipt);
        }

        if (filled($cashier)) {
            $mail->line('Caissier : '.$cashier);
        }

        $mail = $mail
            ->line('')
            ->line('Le reçu PDF est joint à cet e-mail.')
            ->action('Voir le retrait', $url)
            ->line('Merci de votre confiance.')
            ->salutation("L'équipe Salang");

        $pdfBinary = $this->buildReceiptPdf();
        if ($pdfBinary !== null) {
            $filename = 'recu-retrait-'.($receipt ?: $this->withdrawal->id).'.pdf';
            $mail->attachData($pdfBinary, $filename, [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        $amount = number_format((float) $this->withdrawal->amount, 2);
        $url = url('/withdrawal/'.$this->withdrawal->id);

        return [
            'type' => 'guichet_withdrawal_completed',
            'ui' => 'success',
            'ui_type' => 'success',
            'title' => 'Paiement réussi !',
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => (float) $this->withdrawal->amount,
            'receipt_number' => $this->withdrawal->receipt_number,
            'message' => "Votre retrait guichet de \${$amount} a été remis avec succès. Le reçu a été envoyé par e-mail.",
            'action_url' => $url,
            'url' => $url,
        ];
    }

    protected function bonusPhaseLabel(): ?string
    {
        $notes = $this->withdrawal->notes;
        if (! is_string($notes) || ! preg_match('/\[bonus_phase=(direct|il)\]/', $notes, $m)) {
            return null;
        }

        return $m[1] === 'il' ? 'Indirect & Leadership' : 'Direct';
    }

    protected function buildReceiptPdf(): ?string
    {
        try {
            set_time_limit(120);
            @ini_set('memory_limit', '256M');

            $pdf = Pdf::loadView('emails.guichet-receipt-pdf', [
                'withdrawal' => $this->withdrawal,
            ])
                ->setPaper('a5', 'portrait')
                ->setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'isFontSubsettingEnabled' => true,
                ]);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::warning('Guichet receipt PDF attach failed', [
                'withdrawal_id' => $this->withdrawal->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
