<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $this->middleware('auth');
    }

    /**
     * Afficher le tableau de bord de gestion des PV
     */
    public function index(Request $request)
    {
        $authUser = Auth::user();
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

        $transactions = PvTransaction::where('user_id', $subject->id)
            ->with(['order', 'allocation'])
            ->latest()
            ->paginate(30);

        return view('cashier.pv.dashboard', compact(
            'subject',
            'viewingMember',
            'balance',
            'memberPv',
            'pendingAllocations',
            'approvedAllocations',
            'networkMembers',
            'transactions'
        ));
    }

    /**
     * Afficher le formulaire de distribution PV
     */
    public function createDistribution(Request $request)
    {
        $authUser = Auth::user();
        $subject = $this->resolvePvSubjectUser($request);
        $viewingMember = $subject->id !== $authUser->id;

        $balance = $subject->pvBalance;
        $memberPv = $subject->pvSummary($balance);
        $networkMembers = $this->getNetworkMembers($subject);

        return view('cashier.pv.distribute', compact(
            'subject',
            'viewingMember',
            'balance',
            'memberPv',
            'networkMembers'
        ));
    }

    /**
     * Distribuer des PV
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
            $message = "{$result['success']} distribution(s) effectuée(s) avec succès.";
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} échec(s).";
            }
            
            if (!empty($result['errors'])) {
                $message .= ' Erreurs: ' . implode(', ', $result['errors']);
            }
            
            $redirectParams = $distributor->id !== Auth::id()
                ? ['member_id' => $distributor->id]
                : [];

            return redirect()->route('cashier.pv.dashboard', $redirectParams)
                ->with('success', $message);
        } else {
            return redirect()->back()
                ->with('error', 'Échec des distributions: ' . implode(', ', $result['errors']))
                ->withInput();
        }
    }

    /**
     * Obtenir les membres du réseau
     */
    private function getNetworkMembers(User $user): array
    {
        $members = [];
        $current = $user;
        $depth = 0;
        $maxDepth = 5;

        // Récupérer les parrainés directs
        $directSponsors = User::where('parrain_id', $user->id)
            ->where('user_type', 'member')
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
            
            // Récupérer les parrainés indirects (niveau 2)
            $indirectSponsors = User::where('parrain_id', $sponsor->id)
                ->where('user_type', 'member')
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
     * Approuver une allocation
     */
    public function approveAllocation(Request $request, PvAllocation $allocation)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est autorisé à approuver
        if ($allocation->distributor_id != $user->id && !$user->is_admin) {
            return response()->json(['error' => 'Non autorisé'], 403);
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
            return User::query()
                ->where('user_type', 'member')
                ->with('pvBalance')
                ->findOrFail($request->integer('member_id'));
        }

        $authUser->loadMissing('pvBalance');

        return $authUser;
    }
}