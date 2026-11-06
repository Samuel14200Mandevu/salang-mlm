<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityRegisterService;
use Illuminate\Support\Facades\Log;

/**
 * UserObserver — MLM recalcul géré dans User::booted().
 * Ici : synchronisation du registre d’activité IT (adhésion).
 */
class UserObserver
{
    public function created(User $user): void
    {
        if (! $user->parrain_id || ($user->user_type ?? null) === 'client') {
            return;
        }

        try {
            app(ActivityRegisterService::class)->markAdhesion($user);
        } catch (\Throwable $e) {
            Log::warning('Activity register: markAdhesion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updated(User $user): void
    {
        // Géré par User::booted()
    }

    public function saved(User $user): void
    {
        // Géré par User::booted()
    }

    public function deleted(User $user): void
    {
        // Géré par User::booted()
    }
}