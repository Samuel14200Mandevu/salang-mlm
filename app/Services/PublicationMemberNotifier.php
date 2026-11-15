<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\User;
use App\Notifications\PublicationPublishedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class PublicationMemberNotifier
{
    public function notifyAllMembers(Publication $publication): int
    {
        if (! $publication->is_published) {
            return 0;
        }

        $sent = 0;
        $notification = new PublicationPublishedNotification($publication);

        try {
            set_time_limit(300);

            $this->membersQuery()
                ->select(['id'])
                ->orderBy('id')
                ->chunkById(150, function ($members) use ($notification, &$sent) {
                    Notification::send($members, $notification);
                    $sent += $members->count();
                });
        } catch (\Throwable $e) {
            Log::error('Notification publication aux membres interrompue', [
                'publication_id' => $publication->id,
                'sent' => $sent,
                'error' => $e->getMessage(),
            ]);
        }

        return $sent;
    }

    protected function membersQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->where('user_type', 'member')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'it_manager', 'cashier']));
    }
}
