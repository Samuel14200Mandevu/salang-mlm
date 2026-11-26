<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Concerns\GeneratesPdfSafely;
use App\Http\Controllers\Controller;
use App\Models\Commission;
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
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));
        $dateFilter = null;
        if ($request->filled('date')) {
            try {
                $dateFilter = Carbon::createFromFormat('Y-m-d', (string) $request->date)->toDateString();
            } catch (\Throwable) {
                $dateFilter = null;
            }
        }

        $query = Withdrawal::query()
            ->with(['user:id,name,sponsor_id,phone', 'wallet'])
            ->where('method', 'guichet')
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($dateFilter) {
            $query->where(function ($q) use ($dateFilter) {
                $q->whereDate('completed_at', $dateFilter)
                    ->orWhere(function ($q2) use ($dateFilter) {
                        $q2->whereNull('completed_at')
                            ->whereDate('created_at', $dateFilter);
                    });
            });
        }

        $phaseFilter = in_array($request->input('phase'), ['direct', 'il'], true)
            ? (string) $request->input('phase')
            : null;
        if ($phaseFilter) {
            $query->where('notes', 'like', '%[bonus_phase='.$phaseFilter.']%');
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

        $completedRemittedQuery = Withdrawal::query()
            ->where('method', 'guichet')
            ->where('status', 'completed');
        if ($dateFilter) {
            $completedRemittedQuery->whereDate('completed_at', $dateFilter);
        }

        // En attente = bonus D/I/L pas encore crédités au portefeuille
        // (périodes affichables uniquement — exclut l’historique masqué / pré-2026).
        $pendingCommissionsAmount = (float) Commission::query()
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved'])
            ->displayablePeriod()
            ->where('amount', '>', 0)
            ->sum('amount');

        // Montant dû = argent déjà en portefeuille (pas encore remis en cash au guichet).
        $memberWallets = Wallet::query()
            ->whereHas('user', fn ($q) => $q->where('user_type', 'member'));
        $walletDueAmount = round(
            (float) (clone $memberWallets)->sum('balance')
            + (float) (clone $memberWallets)->sum('pending_balance'),
            2
        );

        $stats = [
            'pending' => Withdrawal::where('method', 'guichet')->where('status', 'pending')->count(),
            'pending_commissions_amount' => $pendingCommissionsAmount,
            'wallet_due_amount' => $walletDueAmount,
            'completed' => Withdrawal::where('method', 'guichet')->where('status', 'completed')->count(),
            'completed_today' => (clone $completedRemittedQuery)->count(),
            // Montant payé = déjà remis en espèces (journée filtrée ou tout).
            'paid_today_amount' => (float) (clone $completedRemittedQuery)->sum('amount'),
            // compat sous-titre / anciennes clés
            'pending_amount' => $walletDueAmount,
            'all' => Withdrawal::where('method', 'guichet')->count(),
        ];

        return view('cashier.guichet.index', compact('withdrawals', 'stats', 'status', 'dateFilter', 'phaseFilter'));
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
                $phases = $this->memberPhaseAmounts((int) $m->id);

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'sponsor_id' => $m->sponsor_id,
                    'phone' => $m->phone,
                    'label' => trim(($m->sponsor_id ? $m->sponsor_id.' : ' : '').$m->name),
                    'balance' => $wallet ? (float) $wallet->balance : 0.0,
                    'pending_balance' => $wallet ? (float) $wallet->pending_balance : 0.0,
                    'direct_reste' => $phases['direct_reste'],
                    'il_reste' => $phases['il_reste'],
                    'direct_total' => $phases['direct_total'],
                    'il_total' => $phases['il_total'],
                    'period' => $phases['period'],
                    'eligible' => $eligible && (bool) $wallet,
                    'message' => ! $eligible
                        ? 'Ce compte n’a pas de portefeuille retrait.'
                        : (! $wallet ? 'Portefeuille introuvable.' : null),
                ];
            })->values(),
        ]);
    }

    /**
     * Montants Direct / I&L (période la plus récente) et reste après retraits.
     * Inclut pending/approved/paid pour afficher le montant dès le calcul caisse.
     *
     * @return array{period:?string,direct_total:float,il_total:float,direct_reste:float,il_reste:float}
     */
    protected function memberPhaseAmounts(int $userId): array
    {
        $period = Commission::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->displayablePeriod()
            ->where('amount', '>', 0)
            ->orderByDesc('period')
            ->value('period');

        if (! is_string($period) || $period === '') {
            return [
                'period' => null,
                'direct_total' => 0.0,
                'il_total' => 0.0,
                'direct_reste' => 0.0,
                'il_reste' => 0.0,
            ];
        }

        $byType = Commission::query()
            ->where('user_id', $userId)
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->where('amount', '>', 0)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $direct = round((float) ($byType['direct'] ?? 0), 2);
        $il = round(
            (float) ($byType['indirect'] ?? 0) + (float) ($byType['leadership'] ?? 0),
            2
        );

        $withdrawn = round((float) Withdrawal::query()
            ->where('user_id', $userId)
            ->where('method', 'guichet')
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount'), 2);

        $retireDirect = round(min($withdrawn, $direct), 2);
        $retireIl = round(min(max(0, $withdrawn - $direct), $il), 2);

        return [
            'period' => $period,
            'direct_total' => $direct,
            'il_total' => $il,
            'direct_reste' => round(max(0, $direct - $retireDirect), 2),
            'il_reste' => round(max(0, $il - $retireIl), 2),
        ];
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
            'bonus_phase' => ['required', 'in:direct,il'],
            'paid_on' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subDays(90)->toDateString()],
        ]);

        $amount = round((float) $data['amount'], 2);
        $bonusPhase = $data['bonus_phase'] === 'il' ? 'il' : 'direct';
        $bonusPhaseLabel = $bonusPhase === 'il' ? 'Indirect & Leadership' : 'Direct';
        $paidOn = Carbon::createFromFormat('Y-m-d', (string) $data['paid_on'])->startOfDay();
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

        $dayPending = (float) Withdrawal::where('user_id', $member->id)
            ->whereDate('created_at', $paidOn)
            ->where('status', 'pending')
            ->sum('amount');

        if ($dayPending + $amount > 5000) {
            throw ValidationException::withMessages([
                'amount' => 'Plafond journalier de retraits atteint (5 000 USD) pour le '.$paidOn->format('d/m/Y').'.',
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

            $paidOnTag = $paidOn->toDateString();
            $withdrawal = Withdrawal::create([
                'user_id' => $member->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'method' => 'guichet',
                'status' => 'pending',
                'cashier_id' => auth()->id(),
                'notes' => 'Retrait guichet · paiement '.$bonusPhaseLabel
                    .' · initié au comptoir par '.auth()->user()->name
                    .' (sans demande smartphone)'
                    .' · date paiement '.$paidOn->format('d/m/Y')
                    ."\n[bonus_phase={$bonusPhase}]"
                    ."\n[paid_on={$paidOnTag}]",
                'created_at' => $paidOn->copy()->setTimeFrom(now()),
                'updated_at' => now(),
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
                'description' => 'Retrait guichet ('.$bonusPhaseLabel.') #'.$withdrawal->id,
                'metadata' => [
                    'bonus_phase' => $bonusPhase,
                    'source' => 'guichet_cashier',
                    'paid_on' => $paidOnTag,
                ],
                'completed_at' => null,
                'created_at' => $paidOn->copy()->setTimeFrom(now()),
                'updated_at' => now(),
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
            'paid_on' => $paidOn->toDateString(),
        ]);

        return redirect()
            ->route('cashier.guichet.show', $withdrawal->id)
            ->with('success', 'Retrait créé pour le '.$paidOn->format('d/m/Y').'. Vérifiez le montant, remettez les espèces puis confirmez pour imprimer le reçu.');
    }

    public function show(int $id)
    {
        $withdrawal = Withdrawal::query()
            ->with(['user', 'wallet', 'cashier'])
            ->where('method', 'guichet')
            ->findOrFail($id);

        // Marquer la notif liée comme lue
        auth()->user()->unreadNotifications
            ->where('type', GuichetWithdrawalRequestedNotification::class)
            ->filter(fn ($n) => ($n->data['withdrawal_id'] ?? null) == $withdrawal->id)
            ->each->markAsRead();

        $bonusPhase = 'direct';
        if (is_string($withdrawal->notes) && preg_match('/\[bonus_phase=(direct|il)\]/', $withdrawal->notes, $phaseMatch)) {
            $bonusPhase = $phaseMatch[1];
        }

        $phases = $this->memberPhaseAmounts((int) $withdrawal->user_id);
        $phaseReste = $bonusPhase === 'il'
            ? (float) ($phases['il_reste'] ?? 0)
            : (float) ($phases['direct_reste'] ?? 0);
        $phaseTotal = $bonusPhase === 'il'
            ? (float) ($phases['il_total'] ?? 0)
            : (float) ($phases['direct_total'] ?? 0);

        return view('cashier.guichet.show', compact(
            'withdrawal',
            'bonusPhase',
            'phases',
            'phaseReste',
            'phaseTotal'
        ));
    }

    /**
     * Reçu thermique stylé (même langage que la facture commande).
     */
    public function receipt(Request $request, int $id)
    {
        $withdrawal = Withdrawal::query()
            ->with(['user', 'cashier'])
            ->where('method', 'guichet')
            ->findOrFail($id);

        return view('cashier.guichet.receipt', [
            'withdrawal' => $withdrawal,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    /**
     * Caissier remet l'argent + imprime le reçu : finalise le retrait.
     */
    public function fulfill(Request $request, int $id)
    {
        $data = $request->validate([
            'paid_on' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:'.now()->subDays(90)->toDateString()],
        ]);

        $withdrawal = Withdrawal::query()
            ->where('method', 'guichet')
            ->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Ce retrait n’est plus en attente.');
        }

        $paidOnDate = $this->resolveGuichetPaidOnDate($withdrawal, $data['paid_on'] ?? null);
        $paidAt = $paidOnDate->isToday()
            ? now()
            : $paidOnDate->copy()->setTimeFrom(now());

        DB::beginTransaction();
        try {
            $wallet = Wallet::whereKey($withdrawal->wallet_id)->lockForUpdate()->first();
            if (! $wallet) {
                throw new \RuntimeException('Portefeuille introuvable.');
            }

            if ((float) $wallet->pending_balance < (float) $withdrawal->amount) {
                throw new \RuntimeException('Montant en attente insuffisant sur le portefeuille.');
            }

            $receiptNumber = 'GUICH-'.$paidAt->format('Ymd').'-'.Str::upper(Str::random(6));

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
                    'completed_at' => $paidAt,
                    'description' => 'Retrait guichet remis en espèces #'.$receiptNumber,
                ]);

            $withdrawal->status = 'completed';
            $withdrawal->processed_at = $paidAt;
            $withdrawal->completed_at = $paidAt;
            $withdrawal->cashier_id = auth()->id();
            $withdrawal->receipt_number = $receiptNumber;
            $withdrawal->notes = trim(($withdrawal->notes ? $withdrawal->notes.' | ' : '').
                'Remis au guichet par '.auth()->user()->name.
                ' le '.$paidAt->format('d/m/Y'));
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
            $success .= ' Reçu PDF envoyé par e-mail à '.$user->email.'.';
        } elseif ($user && ! filled($user->email)) {
            $success .= ' (Aucun e-mail sur le compte membre — reçu non envoyé.)';
        }

        return redirect()
            ->route('cashier.guichet.show', $withdrawal->id)
            ->with('success', $success);
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
            ->route('cashier.guichet.index', ['status' => 'all'])
            ->with('success', 'Retrait annulé. $'.number_format($amount, 2).' recrédité au portefeuille du membre (notification envoyée).');
    }

    /**
     * Rapport PDF de fin de paiement des bonus (période) :
     * 1ʳᵉ date = Direct, 2ᵉ date = Indirect + Leadership.
     * Les retraits sont imputés Direct d’abord, puis I&L.
     */
    public function exportDailyPdf(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $period = $request->input('period');
        if (! is_string($period) || $period === '') {
            $period = Commission::query()
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->where('status', 'paid')
                ->where('amount', '>', 0)
                ->orderByDesc('period')
                ->value('period');
        }
        if (! is_string($period) || $period === '') {
            $period = $request->filled('date')
                ? Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m')
                : now()->format('Y-m');
        }

        $indexQuery = array_filter([
            'date' => $request->input('date'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'period' => $period,
        ], fn ($v) => filled($v));

        if (! $request->boolean('pdf')) {
            return view('partials.pdf.preview-shell', [
                'title' => 'Rapport fin de paiement — '.$period,
                'backUrl' => route('cashier.guichet.index', $indexQuery),
                'pdfUrl' => $request->fullUrlWithQuery(['pdf' => 1]),
            ]);
        }

        $payload = $this->buildFinalPaymentReport($period);
        $generatedAt = now();
        $filename = 'rapport_fin_paiement_bonus_'.$period.'.pdf';

        return $this->streamPdfView(
            'cashier.guichet.daily-pdf',
            array_merge($payload, [
                'period' => $period,
                'generatedAt' => $generatedAt,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'letterheadBase64' => $this->loadPdfLetterheadBase64(),
            ]),
            $filename,
            'a4',
            'portrait',
            300,
            '512M'
        );
    }

    /**
     * Construit le détail fin de paiement Direct + I&L pour une période.
     *
     * @return array{
     *   memberRows:\Illuminate\Support\Collection,
     *   directLines:\Illuminate\Support\Collection,
     *   ilLines:\Illuminate\Support\Collection,
     *   directDate:?string,
     *   ilDate:?string,
     *   totals:array<string,float|int>
     * }
     */
    protected function buildFinalPaymentReport(string $period): array
    {
        $paidByUserType = Commission::query()
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->selectRaw('user_id, type, SUM(amount) as total')
            ->groupBy('user_id', 'type')
            ->get()
            ->groupBy('user_id');

        $userIds = $paidByUserType->keys()->map(fn ($id) => (int) $id)->values()->all();
        if ($userIds === []) {
            return [
                'memberRows' => collect(),
                'directLines' => collect(),
                'ilLines' => collect(),
                'directDate' => null,
                'ilDate' => null,
                'totals' => [
                    'direct_total' => 0.0,
                    'direct_retire' => 0.0,
                    'direct_reste' => 0.0,
                    'il_total' => 0.0,
                    'il_retire' => 0.0,
                    'il_reste' => 0.0,
                    'grand_total' => 0.0,
                    'grand_retire' => 0.0,
                    'grand_reste' => 0.0,
                    'members' => 0,
                ],
            ];
        }

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'sponsor_id'])
            ->keyBy('id');

        $withdrawalsByUser = Withdrawal::query()
            ->with(['cashier:id,name'])
            ->where('method', 'guichet')
            ->where('status', 'completed')
            ->whereIn('user_id', $userIds)
            ->orderBy('completed_at')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        $directLines = collect();
        $ilLines = collect();
        $memberRows = collect();

        foreach ($userIds as $userId) {
            $byType = $paidByUserType->get($userId, collect());
            $direct = round((float) $byType->where('type', 'direct')->sum('total'), 2);
            $il = round((float) $byType->whereIn('type', ['indirect', 'leadership'])->sum('total'), 2);
            if ($direct <= 0 && $il <= 0) {
                continue;
            }

            $remainingDirect = $direct;
            $remainingIl = $il;
            $retireDirect = 0.0;
            $retireIl = 0.0;

            foreach ($withdrawalsByUser->get($userId, collect()) as $w) {
                $amount = round((float) $w->amount, 2);
                if ($amount <= 0) {
                    continue;
                }

                $toDirect = round(min($amount, $remainingDirect), 2);
                if ($toDirect > 0) {
                    $remainingDirect = round($remainingDirect - $toDirect, 2);
                    $retireDirect = round($retireDirect + $toDirect, 2);
                    $directLines->push((object) [
                        'date' => optional($w->completed_at)->format('Y-m-d'),
                        'date_label' => optional($w->completed_at)->format('d/m/Y') ?? '—',
                        'time' => optional($w->completed_at)->format('H:i') ?? '—',
                        'user' => $users->get($userId),
                        'amount' => $toDirect,
                        'receipt' => $w->receipt_number ?: '—',
                        'cashier' => $w->cashier?->name ?? '—',
                    ]);
                }

                $rest = round($amount - $toDirect, 2);
                $toIl = round(min($rest, $remainingIl), 2);
                if ($toIl > 0) {
                    $remainingIl = round($remainingIl - $toIl, 2);
                    $retireIl = round($retireIl + $toIl, 2);
                    $ilLines->push((object) [
                        'date' => optional($w->completed_at)->format('Y-m-d'),
                        'date_label' => optional($w->completed_at)->format('d/m/Y') ?? '—',
                        'time' => optional($w->completed_at)->format('H:i') ?? '—',
                        'user' => $users->get($userId),
                        'amount' => $toIl,
                        'receipt' => $w->receipt_number ?: '—',
                        'cashier' => $w->cashier?->name ?? '—',
                    ]);
                }
            }

            $memberRows->push((object) [
                'user' => $users->get($userId),
                'direct_total' => $direct,
                'direct_retire' => $retireDirect,
                'direct_reste' => round(max(0, $direct - $retireDirect), 2),
                'il_total' => $il,
                'il_retire' => $retireIl,
                'il_reste' => round(max(0, $il - $retireIl), 2),
                'total' => round($direct + $il, 2),
                'retire' => round($retireDirect + $retireIl, 2),
                'reste' => round(max(0, $direct - $retireDirect) + max(0, $il - $retireIl), 2),
            ]);
        }

        $memberRows = $memberRows
            ->sortBy(fn ($row) => mb_strtolower((string) ($row->user?->name ?? '')))
            ->values();

        $directLines = $directLines->sortBy([
            ['date', 'asc'],
            ['time', 'asc'],
        ])->values();
        $ilLines = $ilLines->sortBy([
            ['date', 'asc'],
            ['time', 'asc'],
        ])->values();

        return [
            'memberRows' => $memberRows,
            'directLines' => $directLines,
            'ilLines' => $ilLines,
            'directDate' => $directLines->pluck('date')->filter()->unique()->sort()->first(),
            'ilDate' => $ilLines->pluck('date')->filter()->unique()->sort()->first(),
            'totals' => [
                'direct_total' => round((float) $memberRows->sum('direct_total'), 2),
                'direct_retire' => round((float) $memberRows->sum('direct_retire'), 2),
                'direct_reste' => round((float) $memberRows->sum('direct_reste'), 2),
                'il_total' => round((float) $memberRows->sum('il_total'), 2),
                'il_retire' => round((float) $memberRows->sum('il_retire'), 2),
                'il_reste' => round((float) $memberRows->sum('il_reste'), 2),
                'grand_total' => round((float) $memberRows->sum('total'), 2),
                'grand_retire' => round((float) $memberRows->sum('retire'), 2),
                'grand_reste' => round((float) $memberRows->sum('reste'), 2),
                'members' => $memberRows->count(),
            ],
        ];
    }

    /**
     * Feuille à imprimer avant les retraits : Total / Retiré / Reste / Signature.
     *
     * Deux dates de remise :
     *  - phase=direct → bonus Direct uniquement
     *  - phase=il     → Indirect + Leadership (tout ou partie)
     *
     * Les retraits déjà remis (ex. le 16) réduisent le Reste : un membre soldé
     * n’apparaît plus sur la feuille du jour suivant (ex. le 17).
     * Ordre d’imputation des retraits : Direct d’abord, puis I&L.
     */
    public function exportSignoffPdf(Request $request)
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'period' => ['nullable', 'date_format:Y-m'],
            'phase' => ['nullable', 'in:direct,il'],
        ]);

        $reportDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->date)->startOfDay()
            : now()->startOfDay();

        $phase = $request->input('phase', 'direct') === 'il' ? 'il' : 'direct';
        $phaseLabel = $phase === 'il' ? 'Indirect & Leadership' : 'Direct';
        $docTitle = $phase === 'il' ? 'Paiement de bonus Indirect & Leadership' : 'Paiement de bonus Direct';
        $phaseTypes = $phase === 'il' ? ['indirect', 'leadership'] : ['direct'];

        $period = $request->input('period');
        if (! is_string($period) || $period === '') {
            $period = Commission::query()
                ->whereIn('type', ['direct', 'indirect', 'leadership'])
                ->where('status', 'paid')
                ->where('amount', '>', 0)
                ->orderByDesc('period')
                ->value('period');
        }
        if (! is_string($period) || $period === '') {
            $period = $reportDate->format('Y-m');
        }

        $indexQuery = array_filter([
            'date' => $reportDate->toDateString(),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'phase' => $phase,
            'period' => $period,
        ], fn ($v) => filled($v));

        if (! $request->boolean('pdf')) {
            return view('partials.pdf.preview-shell', [
                'title' => $docTitle,
                'backUrl' => route('cashier.guichet.index', $indexQuery),
                'pdfUrl' => $request->fullUrlWithQuery(['pdf' => 1]),
            ]);
        }

        $paidByUserType = Commission::query()
            ->where('period', $period)
            ->whereIn('type', ['direct', 'indirect', 'leadership'])
            ->where('status', 'paid')
            ->where('amount', '>', 0)
            ->selectRaw('user_id, type, SUM(amount) as total')
            ->groupBy('user_id', 'type')
            ->get()
            ->groupBy('user_id');

        $userIds = $paidByUserType->keys()->map(fn ($id) => (int) $id)->all();
        if ($userIds === []) {
            $rows = collect();
        } else {
            $users = User::query()
                ->whereIn('id', $userIds)
                ->get(['id', 'name', 'sponsor_id'])
                ->keyBy('id');

            // Retraits remis jusqu’à la fin de la journée (pas les jours suivants).
            $withdrawnByUser = Withdrawal::query()
                ->where('method', 'guichet')
                ->where('status', 'completed')
                ->whereIn('user_id', $userIds)
                ->where('completed_at', '<=', $reportDate->copy()->endOfDay())
                ->selectRaw('user_id, SUM(amount) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id')
                ->map(fn ($v) => round((float) $v, 2));

            $rows = collect($userIds)->map(function (int $userId) use ($paidByUserType, $withdrawnByUser, $users, $phase) {
                $byType = $paidByUserType->get($userId, collect());
                $direct = round((float) $byType->where('type', 'direct')->sum('total'), 2);
                $il = round(
                    (float) $byType->whereIn('type', ['indirect', 'leadership'])->sum('total'),
                    2
                );

                $withdrawn = (float) ($withdrawnByUser[$userId] ?? 0);
                // Imputation : Direct d’abord, puis I&L.
                $retireDirect = round(min($withdrawn, $direct), 2);
                $retireIl = round(min(max(0, $withdrawn - $direct), $il), 2);

                if ($phase === 'direct') {
                    $total = $direct;
                    $retire = $retireDirect;
                } else {
                    $total = $il;
                    $retire = $retireIl;
                }
                $reste = round(max(0, $total - $retire), 2);

                return (object) [
                    'user' => $users->get($userId),
                    'total' => $total,
                    'retire' => $retire,
                    'reste' => $reste,
                ];
            })
                ->filter(fn ($row) => $row->reste > 0.004)
                ->sortBy(fn ($row) => mb_strtolower((string) ($row->user?->name ?? '')))
                ->values();
        }

        $generatedAt = now();
        $filename = 'feuille_signatures_'.$phase.'_'.$period.'_'.$reportDate->format('Y-m-d').'.pdf';

        return $this->streamPdfView(
            'cashier.guichet.signoff-pdf',
            [
                'rows' => $rows,
                'totalReste' => round((float) $rows->sum('reste'), 2),
                'totalRetire' => round((float) $rows->sum('retire'), 2),
                'totalAll' => round((float) $rows->sum('total'), 2),
                'reportDate' => $reportDate,
                'period' => $period,
                'phase' => $phase,
                'phaseLabel' => $phaseLabel,
                'phaseTypes' => $phaseTypes,
                'generatedAt' => $generatedAt,
                'logoBase64' => $this->loadPdfLogoBase64(),
                'letterheadBase64' => $this->loadPdfLetterheadBase64(),
            ],
            $filename,
            'a4',
            'landscape',
            300,
            '512M'
        );
    }

    /**
     * Date de paiement guichet : champ formulaire, tag [paid_on=…] en notes, sinon aujourd’hui.
     */
    private function resolveGuichetPaidOnDate(Withdrawal $withdrawal, ?string $paidOnInput = null): Carbon
    {
        if (filled($paidOnInput)) {
            try {
                return Carbon::createFromFormat('Y-m-d', (string) $paidOnInput)->startOfDay();
            } catch (\Throwable) {
                // fallback below
            }
        }

        if (is_string($withdrawal->notes)
            && preg_match('/\[paid_on=(\d{4}-\d{2}-\d{2})\]/', $withdrawal->notes, $matches)
        ) {
            try {
                return Carbon::createFromFormat('Y-m-d', $matches[1])->startOfDay();
            } catch (\Throwable) {
                // fallback below
            }
        }

        return today();
    }
}
