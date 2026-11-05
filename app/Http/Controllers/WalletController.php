<?php
// app/Http/Controllers/WalletController.php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Commission;
use App\Services\BinancePayService;
use App\Services\FlexPayService;
use App\Services\PaymentService;
use App\Services\WalletCommissionBreakdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct(
        protected WalletCommissionBreakdown $commissionBreakdown,
        protected PaymentService $paymentService,
        protected FlexPayService $flexPayService,
        protected BinancePayService $binancePayService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        $balance = $wallet ? $wallet->balance : 0;
        $pendingBalance = $wallet ? $wallet->pending_balance : 0;
        $totalWithdrawn = $wallet ? $wallet->total_withdrawn : 0;
        $totalDeposited = $wallet ? $wallet->total_deposited : 0;

        $activityPeriod = $request->query('period', 'week');
        if (! in_array($activityPeriod, ['week', 'month', 'all'], true)) {
            $activityPeriod = 'week';
        }
        $activitySince = match ($activityPeriod) {
            'month' => Carbon::now()->startOfMonth(),
            'week' => Carbon::now()->startOfWeek(),
            default => null,
        };
        $activityPeriodLabels = [
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'all' => 'Tout',
        ];
        $activityPeriodLabel = $activityPeriodLabels[$activityPeriod];

        // 🔥 EXCLURE LES TRANSACTIONS DE TYPE 'pv_credit' ET 'bv_credit'
        $transactions = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends(['period' => $activityPeriod]);

        $transactionStats = $this->transactionStatsForUser($user->id, null);
        $activityOverview = $this->buildActivityOverview($user->id, $activitySince);

        $monthlyCommissions = Commission::where('user_id', $user->id)
            ->where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        $bonusByType = $this->commissionBreakdown->paidByType($user->id);

        $pendingDepositCount = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'deposit')
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereIn('provider', ['orange', 'airtel', 'mpesa', 'flexpay', 'binance'])
                    ->orWhere('reference', 'like', 'FP-%')
                    ->orWhere('reference', 'like', 'BP%');
            })
            ->count();

        $autoRefreshPending = $pendingDepositCount > 0;

        return view('wallet.index', compact(
            'balance',
            'pendingBalance',
            'totalWithdrawn',
            'totalDeposited',
            'transactions',
            'transactionStats',
            'monthlyCommissions',
            'bonusByType',
            'activityOverview',
            'activityPeriod',
            'activityPeriodLabel',
            'pendingDepositCount',
            'autoRefreshPending'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function transactionStatsForUser(int $userId, ?Carbon $since): array
    {
        $applySince = function ($query) use ($since) {
            if ($since) {
                $query->where('created_at', '>=', $since);
            }

            return $query;
        };

        return [
            'total_commission' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'commission')
                    ->where('status', 'completed')
            )->sum('amount'),
            'total_deposit' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'deposit')
                    ->where('status', 'completed')
            )->sum('amount'),
            'total_withdrawal' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'withdrawal')
                    ->where('status', 'completed')
            )->sum('amount'),
            'count_commission' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'commission')
                    ->where('status', 'completed')
            )->count(),
            'count_deposit' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'deposit')
                    ->where('status', 'completed')
            )->count(),
            'count_withdrawal' => $applySince(
                Transaction::where('user_id', $userId)
                    ->where('type', 'withdrawal')
                    ->where('status', 'completed')
            )->count(),
        ];
    }

    /**
     * @return list<array{key: string, label: string, value: string, percent: int, icon: string}>
     */
    private function buildActivityOverview(int $userId, ?Carbon $since): array
    {
        $stats = $this->transactionStatsForUser($userId, $since);
        $operations = ($stats['count_commission'] ?? 0)
            + ($stats['count_deposit'] ?? 0)
            + ($stats['count_withdrawal'] ?? 0);

        $raw = [
            'commission' => abs((float) ($stats['total_commission'] ?? 0)),
            'deposit' => abs((float) ($stats['total_deposit'] ?? 0)),
            'withdrawal' => abs((float) ($stats['total_withdrawal'] ?? 0)),
            'operations' => (float) $operations,
        ];
        $max = max($raw) ?: 0.0;

        $cards = [
            [
                'key' => 'commission',
                'label' => 'Commissions',
                'value' => '$'.number_format($raw['commission'], 2),
                'icon' => 'coins',
            ],
            [
                'key' => 'deposit',
                'label' => 'Dépôts',
                'value' => '$'.number_format($raw['deposit'], 2),
                'icon' => 'wallet',
            ],
            [
                'key' => 'withdrawal',
                'label' => 'Retraits',
                'value' => '$'.number_format($raw['withdrawal'], 2),
                'icon' => 'withdraw',
            ],
            [
                'key' => 'operations',
                'label' => 'Opérations',
                'value' => (string) (int) $raw['operations'],
                'icon' => 'document',
            ],
        ];

        foreach ($cards as $i => $card) {
            $amount = $raw[$card['key']];
            $cards[$i]['percent'] = $max > 0 && $amount > 0
                ? (int) round($amount / $max * 100)
                : 0;
        }

        return $cards;
    }

    public function deposit()
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $balance = $wallet ? $wallet->balance : 0;

        $deposits = Transaction::where('user_id', $user->id)
            ->where('type', 'deposit')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $pendingDepositCount = $deposits->where('status', 'pending')->count();
        $autoRefreshPending = $pendingDepositCount > 0;

        return view('wallet.deposit', compact(
            'balance',
            'deposits',
            'pendingDepositCount',
            'autoRefreshPending'
        ));
    }

    public function storeDeposit(Request $request)
    {
        $rules = [
            'amount' => 'required|numeric|min:1|max:10000',
            'payment_method' => 'required|in:crypto,mobile_money,bank,card',
        ];

        if ($request->payment_method === 'mobile_money') {
            $rules['phone_number'] = 'required|string|min:9|max:15';
            $rules['provider'] = 'nullable|in:orange,airtel,mpesa';
        }

        $request->validate($rules);

        $user = Auth::user();
        $amount = (float) $request->amount;
        $paymentMethod = $request->payment_method;

        if ($paymentMethod === 'mobile_money') {
            $provider = $request->input('provider', 'flexpay');

            $result = $this->paymentService->processMobileMoneyPayment(
                $user->id,
                $amount,
                $provider,
                $request->phone_number
            );

            if (! ($result['success'] ?? false)) {
                return back()
                    ->withInput()
                    ->with('error', $result['error'] ?? $result['message'] ?? 'Erreur FlexPay');
            }

            Log::info('FlexPay deposit initiated', [
                'user_id' => $user->id,
                'amount' => $amount,
                'reference' => $result['reference'] ?? null,
                'order_number' => $result['order_number'] ?? null,
            ]);

            return redirect()->route('wallet.index')
                ->with(
                    'success',
                    'Demande Mobile Money de $'.number_format($amount, 2).
                    ' envoyée. Confirmez sur votre téléphone. Réf: '.($result['reference'] ?? '')
                );
        }

        if ($paymentMethod === 'crypto') {
            try {
                $result = $this->binancePayService->createOrder($user, $amount, [
                    'goods_name' => 'Depot portefeuille Salang',
                    'goods_detail' => 'Depot USDT via Binance Pay',
                    'currency_label' => 'USDT',
                    'note' => $request->crypto_address,
                ]);
            } catch (\Throwable $e) {
                Log::error('Binance Pay deposit error', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Erreur Binance Pay: '.$e->getMessage());
            }

            if (! ($result['success'] ?? false)) {
                return back()
                    ->withInput()
                    ->with('error', $result['error'] ?? $result['message'] ?? 'Erreur Binance Pay');
            }

            Log::info('Binance Pay deposit initiated', [
                'user_id' => $user->id,
                'amount' => $amount,
                'reference' => $result['reference'] ?? null,
                'prepay_id' => $result['prepay_id'] ?? null,
            ]);

            return redirect()->away($result['checkout_url']);
        }

        $wallet = $user->wallet;
        if (! $wallet) {
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'pending_balance' => 0,
                'total_withdrawn' => 0,
                'total_deposited' => 0,
                'currency' => 'USD',
                'is_active' => true,
            ]);
        }

        DB::beginTransaction();

        try {
            // Ne jamais créditer avant confirmation paiement (webhook / admin).
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'status' => 'pending',
                'reference' => 'DEP-'.strtoupper(uniqid()),
                'provider' => $paymentMethod,
                'description' => 'Deposit via '.ucfirst(str_replace('_', ' ', $paymentMethod)),
                'metadata' => [
                    'payment_method' => $paymentMethod,
                    'crypto_address' => $request->crypto_address,
                    'bank_details' => $request->bank_details,
                ],
            ]);

            DB::commit();

            Log::info('Deposit requested (pending payment)', [
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => $paymentMethod,
                'transaction_id' => $transaction->id,
            ]);

            return redirect()->route('wallet.index')
                ->with('success', 'Demande de dépôt de $'.number_format($amount, 2).' enregistrée. Le solde sera crédité après confirmation du paiement.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error depositing', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error depositing: '.$e->getMessage());
        }
    }

    public function transactions(Request $request)
    {
        $user = Auth::user();

        $query = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit']); // 🔥 EXCLURE PV/BV

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        $types = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit'])
            ->distinct()
            ->pluck('type');
            
        $statuses = ['pending', 'completed', 'failed', 'cancelled'];

        return view('wallet.transactions', compact('transactions', 'types', 'statuses'));
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit']) // 🔥 EXCLURE PV/BV
            ->when($request->filled('type'), function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('date_from'), function($query) use ($request) {
                return $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($query) use ($request) {
                return $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'transactions_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID', 'Type', 'Amount', 'Fee', 'Net',
                'Balance Before', 'Balance After', 'Status', 'Description', 'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->id,
                    ucfirst($t->type),
                    number_format($t->amount, 2),
                    number_format($t->fee, 2),
                    number_format($t->net_amount, 2),
                    number_format($t->balance_before, 2),
                    number_format($t->balance_after, 2),
                    ucfirst($t->status),
                    $t->description ?? '',
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function apiBalance()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return response()->json([
            'success' => true,
            'balance' => $wallet ? $wallet->balance : 0,
            'pending_balance' => $wallet ? $wallet->pending_balance : 0,
            'total_withdrawn' => $wallet ? $wallet->total_withdrawn : 0,
            'total_deposited' => $wallet ? $wallet->total_deposited : 0,
        ]);
    }

    public function apiTransactions(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereNotIn('type', ['pv_credit', 'bv_credit']) // EXCLURE PV/BV
            ->when($request->filled('type'), function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}