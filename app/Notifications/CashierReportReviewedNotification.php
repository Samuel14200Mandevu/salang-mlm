<?php

namespace App\Notifications;

use App\Models\CashierReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CashierReportReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(public CashierReport $report)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->report->status === 'approved' ? 'approuvé' : 'rejeté';

        return [
            'type' => $this->report->status === 'approved' ? 'success' : 'warning',
            'category' => 'cashier_report',
            'title' => 'Rapport journalier ' . $label,
            'message' => 'Votre rapport ' . $this->report->report_number . ' a été ' . $label . ' par l\'administration.',
            'url' => route('cashier.reports.show', $this->report->id),
            'report_id' => $this->report->id,
        ];
    }
}
