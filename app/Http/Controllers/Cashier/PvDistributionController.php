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
        $user = Auth::user();
        
        // Récupérer le solde PV du membre
        $balance = UserPvBalance::where('user_id', $user->id)->first();
        
        // Récupérer les allocations en attente
        $pendingAllocations = PvAllocation::where('distributor_id', $user->id)
            ->where('status', 'pending')
            ->with(['user', 'order'])
            ->latest()
            ->get();
            
        // Récupérer les allocations approuvées
        $approvedAllocations = PvAllocation::where('distributor_id', $user->id)
            ->where('status', 'approved')
            ->with(['user', 'order', 'approver'])
            ->latest()
            ->paginate(20);
            
        // Récupérer les membres du réseau (parrainage)
        $networkMembers = $this->getNetworkMembers($user);
        
        // Récupérer l'historique des transactions PV
        $transactions = PvTransaction::where('user_id', $user->id)
            ->with(['order', 'allocation'])
            ->latest()
            ->paginate(30);

        return view('cashier.pv.dashboard', compact(
            'balance',
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
        $user = Auth::user();
        
        // Récupérer le solde disponible
        $balance = UserPvBalance::where('user_id', $user->id)->first();
        
        // Récupérer les membres du réseau
        $networkMembers = $this->getNetworkMembers($user);
        
        return view('cashier.pv.distribute', compact('balance', 'networkMembers'));
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

        $user = Auth::user();
        
        $result = $this->pvService->distributePv($user, $request->distributions);

        if ($result['success'] > 0) {
            $message = "{$result['success']} distribution(s) effectuée(s) avec succès.";
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} échec(s).";
            }
            
            if (!empty($result['errors'])) {
                $message .= ' Erreurs: ' . implode(', ', $result['errors']);
            }
            
            return redirect()->route('cashier.pv.dashboard')
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
                'pv_balance' => UserPvBalance::where('user_id', $sponsor->id)->value('total_pv') ?? 0,
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
                    'pv_balance' => UserPvBalance::where('user_id', $indirect->id)->value('total_pv') ?? 0,
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
        $balance = UserPvBalance::where('user_id', $member->id)->first();
        
        return response()->json([
            'user' => [
                'id' => $member->id,
                'name' => $member->name,
            ],
            'balance' => $balance ? [
                'total_pv' => $balance->total_pv,
                'available_pv' => $balance->available_pv,
                'allocated_pv' => $balance->allocated_pv,
                'pending_pv' => $balance->pending_pv,
                'total_bv' => $balance->total_bv,
                'available_bv' => $balance->available_bv,
            ] : [
                'total_pv' => 0,
                'available_pv' => 0,
                'allocated_pv' => 0,
                'pending_pv' => 0,
                'total_bv' => 0,
                'available_bv' => 0,
            ]
        ]);
    }
}