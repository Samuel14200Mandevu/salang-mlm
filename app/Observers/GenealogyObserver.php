<?php

namespace App\Observers;

use App\Models\Genealogy;
use App\Jobs\RecalculateAfterPVImport;
use Illuminate\Support\Facades\Log;

class GenealogyObserver
{
    public function created(Genealogy $genealogy): void
    {
        try {
            $userIds = [];

            if ($genealogy->user_id) {
                $userIds[] = $genealogy->user_id;
            }

            if ($genealogy->sponsor_id) {
                $userIds[] = $genealogy->sponsor_id;
            }

            if (!empty($userIds)) {
                RecalculateAfterPVImport::dispatch($userIds, date('Y-m'))
                    ->onQueue('rank-recalculation');
            }
        } catch (\Exception $e) {
            Log::error('Error in GenealogyObserver', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}