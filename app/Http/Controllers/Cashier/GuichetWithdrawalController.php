<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\GuichetWithdrawalCancelledNotification;
use App\Notifications\GuichetWithdrawalCompletedNotification;
use App\Notifications\GuichetWithdrawalRequestedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GuichetWithdrawalController extends Controller
{
    use GeneratesPdfSafely;

    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasRole('cashier') && ! auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = trim((string) $request->input('search', ''));

        $query = Withdrawal::query()
            ->with(['user:id,name,sponsor_id,phone', 'wallet'])
            ->where('method', 'guichet')
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->whereHas('user', function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('sponsor_id', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $withdrawals = $query->paginate(30)->withQueryString();

        $stats = [
            'pending' => Withdrawal::where('method', 'guichet')->where('status', 'pending')->count(),
            'completed' => Withdrawal::where('method', 'guichet')->where('status', 'completed')->count(),
            'completed_today' => Withdrawal::where('method', 'guichet')
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
            'pending_amount' => (float) Withdrawal::where('method', 'guichet')
                ->where('status', 'pending')
                ->sum('amount'),
            'all' => Withdrawal::where('method', 'guichet')->count(),
        ];

        return view('cashier.guichet.index', compact('withdrawals', 'stats', 'status'));
    }

    public function show(int $id)
    {
        $withdrawal = Withdrawal::query()
            ->with(['user', 'wallet'])
            ->where('method', 'guichet')
            ->findOrFail($id);

        // Marquer la notif liée comme lue
        auth()->user()->unreadNotifications
            ->where('type', GuichetWithdrawalRequestedNotification::class)
            ->filter(fn ($n) => ($n->data['withdrawal_id'] ?? null) == $withdrawal->id)
            ->each->markAsRead();

        return view('cashier.guichet.show', compact('withdrawal'));
    }

    /**
     * Caissier remet l'argent + imprime le reçu : finalise le retrait.
     */
    public function fulfill(Request $request, int $id)
    {
        $withdrawal = Withdrawal::query()
            ->where('method', 'guichet')
            ->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Ce retrait n’est plus en attente.');
        }

        DB::beginTransaction();
        try {
            $wallet = Wallet::whereKey($withdrawal->wallet_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new \RuntimeException('Portefeuille introuvable.');
            }

            if ((float) $wallet->pending_balance < (float) $withdrawal->amount) {
                throw new \RuntimeException('Montant en attente insuffisant sur le portefeuille.');
            }

            $receiptNumber = 'GUICH-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

            $balanceBefore = (float) $wallet->balance;
            $wallet->pending_balance = max(0, (float) $wallet->pending_balance - (float) $withdrawal->amount);
            $wallet->total_withdrawn = (float) $wallet->total_withdrawn + (float) $withdrawal->amount;
            $wallet->save();

            Transaction::query()
                ->where('user_id', $withdrawal->user_id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->where('amount', -1 * (float) $withdrawal->amount)
                ->latest('id')
                ->limit(1)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'description' => 'Retrait guichet remis en espèces #'.$receiptNumber,
                ]);

            $withdrawal->status = 'completed';
            $withdrawal->processed_at = now();
            $withdrawal->completed_at = now();
            $withdrawal->cashier_id = auth()->id();
            $withdrawal->receipt_number = $receiptNumber;
            $withdrawal->notes = trim(($withdrawal->notes ? $withdrawal->notes.' | ' : '').
                'Remis au guichet par '.auth()->user()->name);
            $withdrawal->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Guichet fulfill failed', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }

        $withdrawal->loadMissing('user');
        if ($withdrawal->user) {
            $withdrawal->user->notifyNow(new GuichetWithdrawalCompletedNotification(
                $withdrawal->fresh()
            ));
        }

        return redirect()
            ->route('cashier.guichet.show', $withdrawal->id)
            ->with('success', 'Retrait confirmé. Imprimez le reçu et remettez l’argent au membre.')
            ->with('print_receipt', true);
    }

    /**
     * Caissier annule le retrait guichet : montant recrédité au portefeuille + notif membre.
     */
    public function cancel(Request $request, int $id)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $withdrawal = Withdrawal::query()
            ->with('user')
            ->where('method', 'guichet')
            ->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Ce retrait n’est plus en attente.');
        }

        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Annulé au guichet par '.auth()->user()->name.' — montant recrédité au portefeuille.';
        }

        $amount = (float) $withdrawal->amount;

        DB::beginTransaction();
        try {
            $wallet = Wallet::whereKey($withdrawal->wallet_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new \RuntimeException('Portefeuille introuvable.');
            }

            $balanceBefore = (float) $wallet->balance;
            $wallet->balance = $balanceBefore + $amount;
            $wallet->pending_balance = max(0, (float) $wallet->pending_balance - $amount);
            $wallet->save();

            Transaction::query()
                ->where('user_id', $withdrawal->user_id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->where('amount', -1 * $amount)
                ->latest('id')
                ->limit(1)
                ->update([
                    'status' => 'cancelled',
                    'description' => 'Retrait guichet annulé #'.$withdrawal->id,
                    'completed_at' => now(),
                ]);

            Transaction::create([
                'user_id' => $withdrawal->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'refund',
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => (float) $wallet->balance,
                'status' => 'completed',
                'description' => 'Retour de fonds — retrait guichet annulé #'.$withdrawal->id,
                'completed_at' => now(),
            ]);

            $withdrawal->status = 'cancelled';
            $withdrawal->processed_at = now();
            $withdrawal->cashier_id = auth()->id();
            $withdrawal->notes = trim(($withdrawal->notes ? $withdrawal->notes.' | ' : '').$reason);
            $withdrawal->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Guichet cancel failed', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }

        if ($withdrawal->user) {
            // Immédiat + database only : évite l’échec mail/queue qui bloquait la notif membre.
            $withdrawal->user->notifyNow(new GuichetWithdrawalCancelledNotification(
                $withdrawal->fresh(),
                $reason
            ));
        }

        return redirect()
            ->route('cashier.guichet.index', ['status' => 'pending'])
            ->with('success', 'Retrait annulé. $'.number_format($amount, 2).' recrédité au portefeuille du membre (notification envoyée).');
    }

    /**
     * Rapport PDF journalier des retraits guichet remis (même structure que commissions-pdf).
     */
    public function exportDailyPdf(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $reportDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay()
            : now()->startOfDay();

        $withdrawals = Withdrawal::query()
            ->with(['user:id,name,sponsor_id', 'cashier:id,name'])
            ->where('method', 'guichet')
            ->where('status', 'completed')
            ->whereDate('completed_at', $reportDate->toDateString())
            ->orderBy('completed_at')
            ->get();

        $totalAmount = (float) $withdrawals->sum('amount');
        $logoBase64 = $this->loadPdfLogoBase64();
        $generatedAt = now();

        $html = view('cashier.guichet.daily-pdf', compact(
            'withdrawals',
            'totalAmount',
            'reportDate',
            'generatedAt',
            'logoBase64'
        ))->render();

        $this->preparePdfExport(300, '512M');
        $pdf = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions($this->safePdfOptions());

        return $this->downloadPdfBinary(
            $pdf->output(),
            'rapport_retraits_guichet_'.$reportDate->format('Y-m-d').'.pdf'
        );
    }
}
