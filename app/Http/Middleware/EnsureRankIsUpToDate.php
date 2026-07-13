<?php
// app/Http/Middleware/EnsureRankIsUpToDate.php

namespace App\Http\Middleware;

use App\Jobs\UpdateRanks;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureRankIsUpToDate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            // Vérifier si le grade doit être mis à jour
            // (ex: une fois par jour ou si jamais mis à jour)
            $shouldUpdate = false;

            if (!$user->last_rank_update) {
                $shouldUpdate = true;
            } elseif ($user->last_rank_update->diffInDays(now()) > 1) {
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                dispatch(new UpdateRanks($user->id))->delay(now()->addSeconds(2));
                
                Log::info('Rank update déclenché via middleware', [
                    'user_id' => $user->id,
                    'last_update' => $user->last_rank_update,
                ]);
            }
        }

        return $next($request);
    }
}