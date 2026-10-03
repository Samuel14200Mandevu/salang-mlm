<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Jobs\RecalculateAfterPVImport;
use App\Support\MlmPeriod;

trait DispatchesMlmRecalculation
{
    protected function dispatchMlmRecalculation(User|int|null $user): void
    {
        if ($user === null) {
            return;
        }

        $user = $user instanceof User ? $user : User::find($user);
        if (!$user) {
            return;
        }

        $userIds = array_values(array_unique(array_filter([
            $user->id,
            $user->parrain_id,
        ])));

        if ($userIds === []) {
            return;
        }

        RecalculateAfterPVImport::dispatch($userIds, MlmPeriod::current())
            ->onQueue('rank-recalculation');
    }
}
