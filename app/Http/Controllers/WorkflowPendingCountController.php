<?php

namespace App\Http\Controllers;

use App\Models\CashierReport;
use App\Models\Consultation;
use App\Notifications\CashierReportReviewedNotification;
use App\Notifications\ConsultationReviewedNotification;
use Illuminate\Http\JsonResponse;

class WorkflowPendingCountController extends Controller
{
    public function admin(): JsonResponse
    {
        $consultations = Consultation::where('status', 'pending')->count();
        $reports = CashierReport::where('status', 'submitted')->count();

        return response()->json([
            'consultations' => $consultations,
            'reports' => $reports,
            'total' => $consultations + $reports,
        ]);
    }

    public function cashier(): JsonResponse
    {
        $user = auth()->user();

        $consultations = $user->unreadNotifications()
            ->where('type', ConsultationReviewedNotification::class)
            ->count();

        $reports = $user->unreadNotifications()
            ->where('type', CashierReportReviewedNotification::class)
            ->count();

        return response()->json([
            'consultations' => $consultations,
            'reports' => $reports,
            'total' => $consultations + $reports,
        ]);
    }
}
