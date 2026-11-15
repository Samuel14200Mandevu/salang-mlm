<?php

namespace App\Console\Commands;

use App\Notifications\PublicationPublishedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneExpiredPublicationNotifications extends Command
{
    protected $signature = 'notifications:prune-expired-publications
                            {--dry-run : Afficher le nombre sans supprimer}';

    protected $description = 'Supprime les notifications de publication expirées (durée de vie 24 h)';

    public function handle(): int
    {
        $cutoffTs = now()->subHours(PublicationPublishedNotification::TTL_HOURS)->getTimestamp();
        $type = PublicationPublishedNotification::class;
        $toDelete = [];

        foreach (DB::table('notifications')->where('type', $type)->orderBy('id')->cursor() as $row) {
            $data = json_decode($row->data ?? '{}', true) ?: [];
            $expiresAt = isset($data['expires_at']) ? strtotime((string) $data['expires_at']) : false;
            $createdAt = strtotime((string) $row->created_at);

            $expired = $expiresAt !== false
                ? $expiresAt <= time()
                : ($createdAt !== false && $createdAt < $cutoffTs);

            if ($expired) {
                $toDelete[] = $row->id;
            }
        }

        $count = count($toDelete);

        if ($this->option('dry-run')) {
            $this->info("{$count} notification(s) publication expirée(s) (dry-run).");

            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('Aucune notification publication expirée.');

            return self::SUCCESS;
        }

        foreach (array_chunk($toDelete, 500) as $chunk) {
            DB::table('notifications')->whereIn('id', $chunk)->delete();
        }

        $this->info("{$count} notification(s) publication expirée(s) supprimée(s).");

        return self::SUCCESS;
    }
}
