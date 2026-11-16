<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\GuichetWithdrawalCancelledNotification;
use App\Notifications\GuichetWithdrawalCompletedNotification;
use App\Notifications\GuichetWithdrawalRequestedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    /**
     * Ancienne page dédiée — le formulaire est désormais sur l’index guichet.
     */
    public function create()
    {
        return redirect()->route('cashier.guichet.index');
    }

    /**
     * Recherche membre (code / téléphone / nom) + solde portefeuille pour le formulaire guichet.
     */
    public function findMember(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json([
                'ok' => false,
                'message' => 'Saisissez au moins 2 caractères (code membre, téléphone ou nom).',
            ], 422);
        }

        $like = '%'.$q.'%';
        $members = User::query()
            ->where('user_type', 'member')
            ->where(function ($query) use ($q, $like) {
                $query->where('sponsor_id', $q)
                    ->orWhere('sponsor_id', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            })
            ->with('wallet:id,user_id,balance,pending_balance')
            ->orderByRaw('CASE WHEN sponsor_id = ? THEN 0 ELSE 1 END', [$q])
            ->limit(12)
            ->get(['id', 'name', 'sponsor_id', 'phone', 'email']);

        if ($members->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Aucun membre trouvé.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'members' => $members->map(function (User $m) {
                $wallet = $m->wallet;
                $eligible = $m->isWalletEligible();

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'sponsor_id' => $m->sponsor_id,
                    'phone' => $m->phone,
                    'label' => trim(($m->sponsor_id ? $m->sponsor_id.' : ' : '').$m->name),
                    'balance' => $wallet ? (float) $wallet->balance : 0.0,
                    'pending_balance' => $wallet ? (float) $wallet->pending_balance : 0.0,
                    'eligible' => $eligible && (bool) $wallet,
                    'message' => ! $eligible
                        ? 'Ce compte n’a pas de portefeuille retrait.'
                        : (! $wallet ? 'Portefeuille introuvable.' : null),
                ];
            })->values(),
        ]);
    }

    /**
     * Crée un retrait guichet initié par le caissier (même règles que la demande membre).
     * Redirige vers la fiche pour confirmer la remise + imprimer le reçu.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:2', 'max:10000'],
        ]);

        $amount = round((float) $data['amount'], 2);
        $member = User::query()
            ->whereKey($data['user_id'])
            ->where('user_type', 'member')
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'user_id' => 'Membre introuvable.',
            ]);
        }

        if (! $member->isWalletEligible()) {
            throw ValidationException::withMessages([
                'user_id' => 'Ce compte n’a pas de portefeuille retrait.',
            ]);
        }

        $wallet = $member->wallet;
        if (! $wallet) {
            throw ValidationException::withMessages([
                'user_id' => 'Portefeuille introuvable pour ce membre.',
            ]);
        }

        if ((float) $wallet->balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Solde insuffisant. Disponible : $'.number_format((float) $wallet->balance, 2),
            ]);
        }

        $threshold = config('commission.kyc.withdrawal_threshold', 5000);
        $totalWithdrawn = (float) Withdrawal::where('user_id', $member->id)
            ->where('status', 'completed')
            ->sum('amount');

        if (($totalWithdrawn + $amount) >= $threshold && $member->kyc_status !== 'verified') {
            throw ValidationException::withMessages([
                'amount' => 'KYC requis pour retirer plus de $'.number_format((float) $threshold, 0).'.',
            ]);
        }

        $todayPending = (float) Withdrawal::where('user_id', $member->id)
            ->whereDate('created_at', today())
            ->where('status', 'pending')
            ->sum('amount');

        if ($todayPending + $amount > 5000) {
            throw ValidationException::withMessages([
                'amount' => 'Plafond journalier de retraits atteint (5 000 USD).',
            ]);
        }

        DB::beginTransaction();
        try {
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();
            if (! $wallet || (float) $wallet->balance < $amount) {
                throw new \RuntimeException('Solde insuffisant au moment de la validation.');
            }

            $balanceBefore = (float) $wallet->balance;
            $wallet->balance = $balanceBefore - $amount;
            $wallet->pending_balance = (float) $wallet->pending_balance + $amount;
            $wallet->save();

            $withdrawal = Withdrawal::create([
                'user_id' => $member->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'method' => 'guichet',
                'status' => 'pending',
                'cashier_id' => auth()->id(),
                'notes' => 'Retrait guichet initié au comptoir par '.auth()->user()->name
                    .' (sans demande smartphone)',
            ]);

            Transaction::create([
                'user_id' => $member->id,
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'fee' => 0,
                'net_amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => (float) $wallet->balance,
                'status' => 'pending',
                'description' => 'Retrait guichet initié au comptoir #'.$withdrawal->id,
                'completed_at' => null,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Guichet store (cashier-initiated) failed', [
                'member_id' => $member->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        Log::info('Guichet withdrawal initiated by cashier', [
            'withdrawal_id' => $withdrawal->id,
            'member_id' => $member->id,
            'cashier_id' => auth()->id(),
            'amount' => $amount,
        ]);

        return redirect()
            ->route('cashier.guichet.show', $withdrawal->id)
            ->with('success', 'Retrait créé. Vérifiez le montant, remettez les espèces puis confirmez pour imprimer le reçu.');
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

        $withdrawal = $withdrawal->fresh(['user', 'cashier']);
        $mailSent = false;
        $user = $withdrawal?->user;

        if ($user) {
            try {
                // Synchrone : e-mail + notif app dès la remise caissier (pas de queue).
                $user->notifyNow(new GuichetWithdrawalCompletedNotification($withdrawal, withMail: true));
                $mailSent = filled($user->email);
            } catch (\Throwable $e) {
                Log::error('Guichet completed mail/notification failed', [
                    'withdrawal_id' => $withdrawal->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                try {
                    // Fallback : notif in-app seule si le mail SMTP a échoué.
                    $user->notifyNow(new GuichetWithdrawalCompletedNotification($withdrawal, withMail: false));
                } catch (\Throwable $fallbackError) {
                    Log::error('Guichet completed database notification failed', [
                        'withdrawal_id' => $withdrawal->id,
                        'error' => $fallbackError->getMessage(),
                    ]);
                }
            }
        }

        $success = 'Retrait confirmé. Imprimez le reçu et remettez l’argent au membre.';
        if ($mailSent) {
            $success .= ' Un e-mail a été envoyé à '.$user->email.'.';
        } elseif ($user && ! filled($user->email)) {
            $success .= ' (Aucun e-mail sur le compte membre.)';
        }

        return redirect()
            ->route('cashier.guichet.show', $withdrawal->id)
            ->with('success', $success)
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
            try {
                $withdrawal->user->notifyNow(new GuichetWithdrawalCancelledNotification(
                    $withdrawal->fresh(),
                    $reason
                ));
            } catch (\Throwable $e) {
                Log::error('Guichet cancelled notification failed', [
                    'withdrawal_id' => $withdrawal->id,
                    'user_id' => $withdrawal->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('cashier.guichet.index', ['status' => 'pending'])
            ->with('success', 'Retrait annulé. $'.number_format($amount, 2).' recrédité au portefeuille du membre (notification envoyée).');
    }

    /**
     * Rapport PDF journalier des retraits guichet remis (letterhead + aperçu comme commissions).
     */
    public function exportDailyPdf(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $reportDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay()
            : now()->startOfDay();

        if (! $request->boolean('pdf')) {
            return view('partials.pdf.preview-shell', [
                'title' => 'Rapport Retraits Guichet',
                'backUrl' => route('cashier.guichet.index', $request->except('pdf')),
                'pdfUrl' => $request->fullUrlWithQuery(['pdf' => 1]),
            ]);
        }

        $withdrawals = Withdrawal::query()
            ->with(['user:id,name,sponsor_id', 'cashier:id,name'])
            ->where('method', 'guichet')
            ->where('status', 'completed')
            ->whereDate('completed_at', $reportDate->toDateString())
            ->orderBy('completed_at')
            ->get();

        $totalAmount = (float) $withdrawals->sum('amount');
        $generatedAt = now();
        $filename = 'rapport_retraits_guichet_'.$reportDate->format('Y-m-d').'.pdf';

        return $this->streamPdfView(
            'cashier.guichet.daily-pdf',
            [
                'withdrawals' => $withdrawals,
                'totalAmount' => $totalAmount,
                'reportDate' => $reportDate,
                'generatedAt' => $generatedAt,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'letterheadBase64' => $this->loadPdfLetterheadBase64(),
            ],
            $filename,
            'a4',
            'portrait',
            300,
            '512M'
        );
    }
}
