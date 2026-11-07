<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Services\ActivityRegisterService;
use Illuminate\Support\Facades\Log;

class ConsultationObserver
{
    public function created(Consultation $consultation): void
    {
        try {
            app(ActivityRegisterService::class)->markConsultation($consultation);
        } catch (\Throwable $e) {
            Log::warning('Activity register: markConsultation failed', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
