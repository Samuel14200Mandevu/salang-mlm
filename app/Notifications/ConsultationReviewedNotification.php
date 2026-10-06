<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConsultationReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public Consultation $consultation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->consultation->status_label ?? $this->consultation->status;

        return [
            'type' => 'info',
            'category' => 'consultation',
            'title' => 'Consultation mise à jour',
            'message' => 'La fiche de ' . $this->consultation->nom_complet . ' est passée au statut : ' . $statusLabel . '.',
            'url' => route('cashier.consultations.show', $this->consultation),
            'consultation_id' => $this->consultation->id,
        ];
    }
}
