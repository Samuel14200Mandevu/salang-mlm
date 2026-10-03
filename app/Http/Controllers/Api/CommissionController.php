<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommissionResource;
use App\Models\Commission;
use App\Services\MLM\UserCommissionDisplayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->with('period')
            ->orderByDesc('created_at')
            ->paginate(min(50, (int) $request->get('per_page', 20)));

        $payload = CommissionResource::collection($commissions)->response()->getData(true);

        return response()->json([
            'success' => true,
            'data' => $payload['data'] ?? [],
            'message' => null,
            'errors' => null,
            'pagination' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'per_page' => $commissions->perPage(),
                'total' => $commissions->total(),
            ],
        ]);
    }

    public function stats(Request $request, UserCommissionDisplayService $commissionDisplay)
    {
        $user = Auth::user();
        $bands = $commissionDisplay->summarize($user->id);

        $stats = [
            'total' => (float) $bands['total_visible'],
            'pending' => (float) $bands['pending_payable'],
            'paid' => (float) $bands['paid_total_visible'],
            'total_count' => (int) $bands['total_visible_count'],
            'pending_count' => (int) $bands['pending_payable_count'],
            'paid_count' => (int) $bands['paid_visible_count'],
            'paid_offline_historical' => (float) $bands['paid_offline_historical'],
            'paid_system' => (float) $bands['paid_system'],
            'bands' => [
                'historical_paid' => (float) $bands['paid_offline_historical'],
                'system_paid' => (float) $bands['paid_system'],
                'payable_pending' => (float) $bands['pending_payable'],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => ['stats' => $stats],
            'stats' => $stats,
            'message' => null,
            'errors' => null,
        ]);
    }

    public function show(int $id)
    {
        $user = Auth::user();
        $commission = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->with('period')
            ->findOrFail($id);

        return $this->success(new CommissionResource($commission));
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $commissions = Commission::where('user_id', $user->id)
            ->visibleToMember()
            ->with('period')
            ->orderByDesc('created_at')
            ->get();

        if ($request->get('format') === 'csv') {
            $lines = ['id,amount,type,status,period,created_at'];
            foreach ($commissions as $c) {
                $lines[] = implode(',', [
                    $c->id,
                    $c->amount,
                    $c->type,
                    $c->status,
                    $c->period ?? '',
                    $c->created_at?->toIso8601String() ?? '',
                ]);
            }

            return response(implode("\n", $lines), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="commissions.csv"',
            ]);
        }

        return $this->success(CommissionResource::collection($commissions));
    }
}
