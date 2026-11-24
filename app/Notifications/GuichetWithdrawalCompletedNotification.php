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
        $paidOnLabel = $this->paidOnLabel();
        $isBackdated = $this->isBackdatedPayment();

        $mail = (new MailMessage)
            ->subject('Votre reçu — retrait guichet Salang')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line(
                $isBackdated
                    ? 'Paiement reçu depuis le '.$paidOnLabel.' : votre retrait guichet a bien été remis au comptoir.'
                    : 'Votre retrait guichet a été remis avec succès au comptoir.'
            );

        if ($isBackdated) {
            $mail->line('Désolé pour le retard de cette notification.');
        }

        $mail = $mail
            ->line('')
            ->line('Montant remis : $'.$amount)
            ->line('Date du paiement : '.$paidOnLabel);

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
        $paidOnLabel = $this->paidOnLabel();
        $isBackdated = $this->isBackdatedPayment();

        $message = $isBackdated
            ? "Paiement reçu depuis le {$paidOnLabel} : retrait guichet de \${$amount} remis au comptoir. Désolé pour le retard de cette notification."
            : "Votre retrait guichet de \${$amount} a été remis avec succès le {$paidOnLabel}. Le reçu a été envoyé par e-mail.";

        return [
            'type' => 'guichet_withdrawal_completed',
            'ui' => 'success',
            'ui_type' => 'success',
            'title' => 'Paiement réussi !',
            'withdrawal_id' => $this->withdrawal->id,
            'amount' => (float) $this->withdrawal->amount,
            'receipt_number' => $this->withdrawal->receipt_number,
            'paid_on' => $this->paidOnDate()?->toDateString(),
            'message' => $message,
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

    protected function paidOnDate(): ?\Carbon\Carbon
    {
        if ($this->withdrawal->completed_at) {
            return $this->withdrawal->completed_at->copy()->startOfDay();
        }

        if ($this->withdrawal->processed_at) {
            return $this->withdrawal->processed_at->copy()->startOfDay();
        }

        $notes = $this->withdrawal->notes;
        if (is_string($notes) && preg_match('/\[paid_on=(\d{4}-\d{2}-\d{2})\]/', $notes, $m)) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $m[1])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return $this->withdrawal->created_at?->copy()->startOfDay();
    }

    protected function paidOnLabel(): string
    {
        return $this->paidOnDate()?->format('d/m/Y') ?? now()->format('d/m/Y');
    }

    protected function isBackdatedPayment(): bool
    {
        $paidOn = $this->paidOnDate();

        return $paidOn !== null && $paidOn->lt(today());
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
