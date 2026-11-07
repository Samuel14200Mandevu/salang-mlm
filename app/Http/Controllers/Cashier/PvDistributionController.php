<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CommissionPeriod;
use App\Models\User;
use App\Support\MlmPeriod;
use App\Models\PvAllocation;
use App\Models\UserPvBalance;
use App\Models\PvTransaction;
use App\Services\PvManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PvDistributionController extends Controller
{
    protected PvManagementService $pvService;

    public function __construct(PvManagementService $pvService)
    {
        $this->pvService = $pvService;
        $this->middleware(['auth', 'active']);
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasRole('cashier') && ! auth()->user()->hasRole('admin')) {
                abort(403, 'Accès réservé aux caissiers.');
            }

            return $next($request);
        });
    }

    /**
     * Afficher le tableau de bord de gestion des PV
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();

        // Accueil menu : sans membre sélectionné → écran de recherche.
        if (! $request->filled('member_id') && ($authUser->hasRole('cashier') || $authUser->hasRole('admin'))) {
            return view('cashier.pv.hub');
        }

        $subject = $this->resolvePvSubjectUser($request);
        $viewingMember = $subject->id !== $authUser->id;

        $balance = $subject->pvBalance;
        $memberPv = $subject->pvSummary($balance);

        $pendingAllocations = PvAllocation::where('distributor_id', $subject->id)
            ->where('status', 'pending')
            ->with(['user', 'order'])
            ->latest()
            ->get();

        $approvedAllocations = PvAllocation::where('distributor_id', $subject->id)
            ->where('status', 'approved')
            ->with(['user', 'order', 'approver'])
            ->latest()
            ->paginate(20);

        $networkMembers = $this->getNetworkMembers($subject);

        $mlmPeriodValue = MlmPeriod::current();
        $mlmPeriodDates = CommissionPeriod::getPeriodDates($mlmPeriodValue);
        $isMlmClosureDay = (int) now()->day === CommissionPeriod::MLM_END_DAY;
        $subjectHasMemberDownlines = $subject->hasDirectMemberDownlines();

        return view('cashier.pv.dashboard', compact(
            'subject',
            'viewingMember',
            'balance',
            'memberPv',
            'pendingAllocations',
            'approvedAllocations',
            'networkMembers',
            'mlmPeriodValue',
            'mlmPeriodDates',
            'isMlmClosureDay',
            'subjectHasMemberDownlines'
        ));
    }

    /**
     * Recherche d’un membre (tout le fichier) par code pour ouvrir sa Gestion PV.
     */
    public function findMemberByCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:40',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first() ?: 'Code membre invalide.',
            ], 422);
        }

        $code = trim((string) $request->input('code'));
        $member = User::query()
            ->where('sponsor_id', $code)
            ->where('user_type', 'member')
            ->first();

        if (! $member) {
            return response()->json([
                'ok' => false,
                'message' => "Aucun membre trouvé avec le code « {$code} ».",
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'sponsor_id' => $member->sponsor_id,
            ],
            'url' => route('cashier.pv.dashboard', ['member_id' => $member->id]),
        ]);
    }

    /**
     * Historique des transactions PV.
     * Sans member_id : historique global (menu Gestion des PV).
     * Avec member_id : filtré sur ce membre.
     */
    public function transactions(Request $request)
    {
        $authUser = Auth::user();
        $viewingMember = $request->filled('member_id');
        $subject = $viewingMember ? $this->resolvePvSubjectUser($request) : null;

        $query = PvTransaction::query()
            ->with(['user', 'order', 'allocation.user', 'allocation.distributor'])
            ->latest();

        if ($subject) {
            $query->where('user_id', $subject->id);
        } else {
            // Uniquement les mouvements liés à des membres.
            $query->whereHas('user', fn ($q) => $q->where('user_type', 'member'));
        }

        $transactions = $query->paginate(30)->withQueryString();

        return view('cashier.pv.transactions', compact(
            'subject',
            'viewingMember',
            'transactions'
        ));
    }

    /**
     * Détail d’une transaction PV + réseau du membre attributaire.
     */
    public function showTransaction(Request $request, PvTransaction $transaction)
    {
        $authUser = Auth::user();
        $viewingMember = $request->filled('member_id');
        $subject = $viewingMember
            ? $this->resolvePvSubjectUser($request)
            : ($transaction->user ?? $authUser);

        if ($viewingMember && (int) $transaction->user_id !== (int) $subject->id) {
            abort(404);
        }

        if (! $viewingMember) {
            $transaction->loadMissing('user');
            if (! $transaction->user || $transaction->user->user_type !== 'member') {
                abort(404);
            }
            $subject = $transaction->user;
        }

        $transaction->load([
            'order',
            'allocation.user',
            'allocation.distributor',
            'allocation.approver',
        ]);

        $allocation = $transaction->allocation;
        $recipient = $allocation?->user;
        $distributor = $allocation?->distributor;

        // Si pas d’allocation (ex. migration), tenter via metadata.
        if (! $recipient && is_array($transaction->metadata)) {
            $recipientId = $transaction->metadata['recipient_id'] ?? null;
            if ($recipientId) {
                $recipient = User::query()->find($recipientId);
            }
        }

        if (! $distributor && is_array($transaction->metadata)) {
            $distributorId = $transaction->metadata['distributor_id'] ?? null;
            if ($distributorId) {
                $distributor = User::query()->find($distributorId);
            }
        }

        // Membre auquel les PV ont été attribués → son réseau.
        $attributedMember = $recipient;
        if (! $attributedMember && $transaction->type === 'credit') {
            $attributedMember = $subject;
        }

        $networkMembers = $attributedMember
            ? $this->getNetworkMembers($attributedMember)
            : [];

        $networkDepth = null;
        if ($attributedMember && $distributor) {
            $networkDepth = $this->pvService->networkDepth($distributor, $attributedMember);
        }

        return view('cashier.pv.transaction-show', compact(
            'subject',
            'viewingMember',
            'transaction',
            'allocation',
            'recipient',
            'distributor',
            'attributedMember',
            'networkMembers',
            'networkDepth'
        ));
    }

    /**
     * Formulaire de distribution PV (le caissier saisit tout après proposition verbale du membre).
     */
    public function createDistribution(Request $request)
    {
        $authUser = Auth::user();
        $subject = $this->resolvePvSubjectUser($request);
        $viewingMember = $subject->id !== $authUser->id;

        $balance = $subject->pvBalance;
        $memberPv = $subject->pvSummary($balance);
        $networkMembers = $this->getNetworkMembers($subject);

        $mlmPeriodValue = MlmPeriod::current();
        $mlmPeriodDates = CommissionPeriod::getPeriodDates($mlmPeriodValue);
        $isMlmClosureDay = (int) now()->day === CommissionPeriod::MLM_END_DAY;

        return view('cashier.pv.distribute', compact(
            'subject',
            'viewingMember',
            'balance',
            'memberPv',
            'networkMembers',
            'mlmPeriodValue',
            'mlmPeriodDates',
            'isMlmClosureDay'
        ));
    }

    /**
     * Enregistrer la distribution (allocation en attente, puis approbation caisse).
     */
    public function distribute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributions' => 'required|array|min:1',
            'distributions.*.user_id' => 'required|exists:users,id',
            'distributions.*.pv_amount' => 'required|integer|min:1',
            'distributions.*.bv_amount' => 'nullable|integer|min:0',
            'distributions.*.notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $distributor = $this->resolvePvSubjectUser($request);
        $result = $this->pvService->distributePv($distributor, $request->distributions);

        if ($result['success'] > 0) {
            $message = "{$result['success']} distribution(s) enregistrée(s). Approuvez-les pour créditer les PV.";
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} échec(s).";
            }
            if (! empty($result['errors'])) {
                $message .= ' Erreurs: '.implode(', ', $result['errors']);
            }

            $redirectParams = $distributor->id !== Auth::id()
                ? ['member_id' => $distributor->id]
                : [];

            return redirect()->route('cashier.pv.dashboard', $redirectParams)
                ->with('success', $message);
        }

        return redirect()->back()
            ->with('error', 'Échec des distributions: '.implode(', ', $result['errors']))
            ->withInput();
    }

    /**
     * Recherche un membre du réseau par code membre (sponsor_id).
     */
    public function searchNetworkMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:40',
            'member_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first() ?: 'Code membre invalide.',
            ], 422);
        }

        $distributor = $this->resolvePvSubjectUser($request);
        $result = $this->pvService->findNetworkMemberByCode(
            $distributor,
            (string) $request->input('code')
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * Aperçu réseau (niveaux 1–2). Pour le reste : recherche par code.
     */
    private function getNetworkMembers(User $user): array
    {
        $members = [];

        $directSponsors = User::where('parrain_id', $user->id)
            ->where('user_type', 'member')
            ->orderBy('name')
            ->get();

        foreach ($directSponsors as $sponsor) {
            $members[] = [
                'id' => $sponsor->id,
                'name' => $sponsor->name,
                'email' => $sponsor->email,
                'phone' => $sponsor->phone,
                'level' => 1,
                'pv_balance' => (int) round((float) ($sponsor->pv_balance ?? 0)),
                'sponsor_id' => $sponsor->sponsor_id,
            ];

            $indirectSponsors = User::where('parrain_id', $sponsor->id)
                ->where('user_type', 'member')
                ->orderBy('name')
                ->get();

            foreach ($indirectSponsors as $indirect) {
                $members[] = [
                    'id' => $indirect->id,
                    'name' => $indirect->name,
                    'email' => $indirect->email,
                    'phone' => $indirect->phone,
                    'level' => 2,
                    'pv_balance' => (int) round((float) ($indirect->pv_balance ?? 0)),
                    'sponsor_id' => $indirect->sponsor_id,
                ];
            }
        }

        return $members;
    }

    /**
     * Approuver une allocation proposée par un membre.
     * Règle métier : le membre propose, le caissier (ou admin) approuve.
     */
    public function approveAllocation(Request $request, PvAllocation $allocation)
    {
        $user = Auth::user();

        $canApprove = $user->hasRole('cashier')
            || $user->isAdmin()
            || $user->hasRole('admin');

        if (! $canApprove) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => 'Seul un caissier ou un admin peut approuver.'], 403);
            }

            return redirect()->back()->with('error', 'Seul un caissier ou un admin peut approuver.');
        }

        $result = $this->pvService->approveAllocation($allocation, $user);

        if ($request->ajax()) {
            return response()->json(['success' => $result]);
        }

        if ($result) {
            return redirect()->back()->with('success', 'Allocation approuvée avec succès');
        }

        return redirect()->back()->with('error', 'Erreur lors de l\'approbation');
    }

    /**
     * Obtenir les détails des PV d'un membre
     */
    public function getMemberPvDetails(Request $request, User $member)
    {
        $member->loadMissing('pvBalance');
        $summary = $member->pvSummary();

        return response()->json([
            'user' => [
                'id' => $member->id,
                'name' => $member->name,
            ],
            'balance' => [
                'cumulative_pv' => $summary['cumulative_pv'],
                'wallet_total_pv' => $summary['wallet_total_pv'],
                'total_pv' => $summary['wallet_total_pv'],
                'available_pv' => $summary['available_pv'],
                'allocated_pv' => $summary['allocated_pv'],
                'pending_pv' => $summary['pending_pv'],
                'total_bv' => $summary['total_bv'],
                'available_bv' => $summary['available_bv'],
            ],
        ]);
    }

    /**
     * Membre dont on consulte / distribue les PV (?member_id= depuis la fiche caisse).
     */
    private function resolvePvSubjectUser(Request $request): User
    {
        $authUser = Auth::user();

        if ($request->filled('member_id')) {
            // Réservé caissier/admin (middleware) : consultation PV d’un membre depuis la fiche caisse.
            if (! $authUser->hasRole('cashier') && ! $authUser->hasRole('admin')) {
                abort(403, 'Non autorisé.');
            }

            return User::query()
                ->where('user_type', 'member')
                ->with('pvBalance')
                ->findOrFail($request->integer('member_id'));
        }

        $authUser->loadMissing('pvBalance');

        return $authUser;
    }
}