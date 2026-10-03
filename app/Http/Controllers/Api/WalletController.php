<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\MLM\UserCommissionDisplayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    use ApiResponse;

    public function balance(UserCommissionDisplayService $commissionDisplay)
    {
        return $this->show($commissionDisplay);
    }

    public function show(UserCommissionDisplayService $commissionDisplay)
    {
        $user = Auth::user();
        $wallet = $user->wallet ?? Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'pending_balance' => 0,
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $resource = (new WalletResource($wallet))->resolve();
        $commissionSummary = $commissionDisplay->memberStatsPayload($user->id);

        return response()->json([
            'success' => true,
            'data' => array_merge($resource, [
                'commission_summary' => $commissionSummary,
            ]),
            'message' => null,
            'errors' => null,
            'balance' => $resource['balance'],
            'pending_balance' => $resource['pending_balance'],
            'commission_summary' => $commissionSummary,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit'])
            ->orderByDesc('created_at')
            ->paginate(min(50, (int) $request->get('per_page', 15)));

        return $this->success([
            'items' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    public function deposit(Request $request)
    {
        return $this->error('Wallet deposit via API is not implemented yet.', 501);
    }

    public function withdraw(Request $request)
    {
        return $this->error('Use POST /api/wallet/withdraw via WithdrawalController.', 501);
    }
}
