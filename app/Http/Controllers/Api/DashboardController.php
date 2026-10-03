<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\MLM\UserCommissionDisplayService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ApiResponse;

    public function stats(UserCommissionDisplayService $commissionDisplay)
    {
        $user = Auth::user()->load(['rank', 'wallet']);
        $userRank = $user->relationLoaded('rank') && $user->rank instanceof Rank
            ? $user->rank
            : Rank::find($user->rank_id);

        if (!$userRank && is_string($user->getAttributes()['rank'] ?? null)) {
            $userRank = Rank::where('name', $user->getAttributes()['rank'])->first();
        }

        $commissionStats = $commissionDisplay->memberStatsPayload($user->id);

        return $this->success(array_merge($commissionStats, [
            'total_withdrawn' => (float) Withdrawal::where('user_id', $user->id)->where('status', 'completed')->sum('amount'),
            'total_downlines' => User::where('parrain_id', $user->id)->where('is_active', true)->count(),
            'wallet_balance' => (float) ($user->wallet?->balance ?? 0),
            'rank' => $userRank?->name ?? $user->rank ?? 'Distributeur',
            'rank_level' => (int) ($userRank?->level ?? $user->rank_level ?? 1),
            'pv_personnel' => (float) ($user->pv_balance ?? 0),
            'pv_cumul' => (float) ($user->team_pv ?? 0),
            'monthly_pv' => (float) ($user->monthly_pv ?? 0),
            'is_active' => (bool) $user->is_active,
        ]));
    }

    public function chart()
    {
        return $this->error('Chart data API is not implemented yet.', 501);
    }
}
