<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PVHistory;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Services\MLM\AdvancedRankCalculator;
use App\Jobs\UpdateTeamPV;
use App\Jobs\UpdateRanks;
use App\Jobs\CalculatePVBV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AdminPVImportController extends Controller
{
    protected AdvancedRankCalculator $rankCalculator;

    public function __construct(AdvancedRankCalculator $rankCalculator)
    {
        $this->rankCalculator = $rankCalculator;
    }

    public function index(Request $request)
    {
        $userId = $request->input('user_id');
        $user = null;
        
        if ($userId) {
            $user = User::with(['rank', 'parrain'])->find($userId);
        }
        
        return view('admin.pv.import', compact('user'));
    }

    public function searchUser(Request $request)
    {
        $query = $request->input('search');
        
        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'Recherche trop courte (minimum 2 caracteres)'], 400);
        }
        
        $user = User::with(['rank', 'parrain'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->first();
        
        if (!$user) {
            return response()->json(['error' => 'Aucun membre trouve avec ces criteres'], 404);
        }
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'sponsor_id' => $user->sponsor_id,
            'rank_name' => $user->rank ?? 'Distributeur',
            'rank_level' => $user->rank_level ?? 1,
            'pv_balance' => number_format($user->pv_balance ?? 0, 1, ',', ' '),
            'monthly_pv' => number_format($user->monthly_pv ?? 0, 1, ',', ' '),
            'team_pv' => number_format($user->team_pv ?? 0, 1, ',', ' '),
            'bv_balance' => number_format($user->bv_balance ?? 0, 1, ',', ' '),
            'total_team' => $user->total_team ?? 0,
            'parrain_name' => $user->parrain?->name,
            'parrain_sponsor_id' => $user->parrain?->sponsor_id,
        ]);
    }

    public function getUserStats($userId)
    {
        $user = User::with(['rank', 'parrain'])->find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouve'], 404);
        }
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'sponsor_id' => $user->sponsor_id,
            'rank_name' => $user->rank ?? 'Distributeur',
            'rank_level' => $user->rank_level ?? 1,
            'pv_balance' => number_format($user->pv_balance ?? 0, 1, ',', ' '),
            'monthly_pv' => number_format($user->monthly_pv ?? 0, 1, ',', ' '),
            'team_pv' => number_format($user->team_pv ?? 0, 1, ',', ' '),
            'bv_balance' => number_format($user->bv_balance ?? 0, 1, ',', ' '),
            'total_team' => $user->total_team ?? 0,
            'parrain_name' => $user->parrain?->name,
            'parrain_sponsor_id' => $user->parrain?->sponsor_id,
        ]);
    }

    public function importCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'period' => 'required|date_format:Y-m',
        ]);

        $file = $request->file('csv_file');
        $period = $request->period;

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $errors = [];
        $usersUpdated = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);

                $validator = Validator::make($data, [
                    'member_id' => 'required|numeric',
                    'member_name' => 'required|string',
                    'product_code' => 'required|string',
                    'quantity' => 'required|numeric|min:1',
                    'unit_pv' => 'required|numeric|min:0',
                    'total_pv' => 'required|numeric|min:0',
                    'order_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Erreur ligne " . ($imported + 1) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $user = User::where('sponsor_id', $data['member_id'])
                    ->orWhere('id', $data['member_id'])
                    ->first();

                if (!$user) {
                    $errors[] = "Membre non trouve: " . $data['member_id'] . " - " . $data['member_name'];
                    continue;
                }

                $orderDate = date('Y-m-d', strtotime($data['order_date']));
                $totalPv = (float) $data['total_pv'];

                $order = Order::where('user_id', $user->id)
                    ->where('order_date', $orderDate)
                    ->where('period', $period)
                    ->first();

                if (!$order) {
                    $order = Order::create([
                        'user_id' => $user->id,
                        'order_number' => 'ORD-' . $period . '-' . $user->id . '-' . time() . '-' . $imported,
                        'total_pv' => 0,
                        'total_bv' => 0,
                        'total_amount' => 0,
                        'period' => $period,
                        'order_date' => $orderDate,
                        'status' => 'completed',
                        'created_by' => auth()->id(),
                    ]);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_code' => $data['product_code'],
                    'product_name' => $data['product_name'] ?? $data['product_code'],
                    'quantity' => (int) $data['quantity'],
                    'unit_pv' => (float) $data['unit_pv'],
                    'total_pv' => $totalPv,
                    'unit_bv' => (float) $data['unit_pv'] * 0.8,
                    'total_bv' => $totalPv * 0.8,
                ]);

                $order->increment('total_pv', $totalPv);
                $order->increment('total_bv', $totalPv * 0.8);

                PVHistory::create([
                    'user_id' => $user->id,
                    'amount' => $totalPv,
                    'date' => $orderDate,
                    'period' => $period,
                    'type' => 'personal',
                    'notes' => "Import CSV - {$data['product_code']} - " . ($data['product_name'] ?? ''),
                    'created_by' => auth()->id(),
                ]);

                $user->pv_balance += $totalPv;
                $user->monthly_pv += $totalPv;
                $user->bv_balance += $totalPv * 0.8;
                $user->monthly_bv += $totalPv * 0.8;
                $user->saveQuietly();

                if (!in_array($user->id, $usersUpdated)) {
                    $usersUpdated[] = $user->id;
                }

                $imported++;
            }

            fclose($handle);

            DB::commit();

            // =============================================
            // IMPORTANT: Recalcul pour chaque utilisateur ET son parrain ET tous les ancêtres
            // =============================================
            foreach ($usersUpdated as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // Recalculer le grade de l'utilisateur
                    $this->recalculateUserRank($user);
                    
                    // Mettre à jour le team_pv du parrain et de tous ses ancêtres
                    if ($user->parrain_id) {
                        $parrain = User::find($user->parrain_id);
                        if ($parrain && $parrain->is_active) {
                            $this->updateParrainAndAncestorsTeamPV($parrain);
                        }
                    }
                }
            }

            $message = "Import termine !\n";
            $message .= "Lignes importees: {$imported}\n";
            $message .= "Utilisateurs mis a jour: " . count($usersUpdated) . "\n";
            $message .= "Les grades et les team_pv des parrains et ancetres ont ete recalcules automatiquement.";

            if (!empty($errors)) {
                $message .= "\nErreurs: " . count($errors) . "\n";
                Log::warning('Erreurs import CSV', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur import CSV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function addMonthlyPV(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0.1',
            'type' => 'required|in:personal,team,monthly',
            'notes' => 'nullable|string|max:255',
        ]);

        $period = $request->period;
        $minPeriod = '2020-01';
        
        if ($period < $minPeriod) {
            return back()->with('error', 'La periode ne peut pas etre anterieure a ' . $minPeriod);
        }

        $user = User::with(['rank', 'parrain'])->findOrFail($userId);
        $amount = (float) $request->amount;

        DB::beginTransaction();
        try {
            $date = $period . '-01';

            PVHistory::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'date' => $date,
                'period' => $period,
                'type' => $request->type,
                'notes' => $request->notes ?: "Ajout mensuel pour {$period}",
                'created_by' => auth()->id(),
            ]);

            if ($request->type === 'personal' || $request->type === 'monthly') {
                $user->pv_balance += $amount;
                $user->monthly_pv += $amount;
                $user->bv_balance += $amount;
                $user->monthly_bv += $amount;
            }

            if ($request->type === 'team') {
                $user->team_pv += $amount;
                $user->team_bv += $amount;
            }

            $user->saveQuietly();

            // Mettre à jour le team_pv du parrain et de tous ses ancêtres
            if ($user->parrain_id && ($request->type === 'personal' || $request->type === 'monthly')) {
                $parrain = User::find($user->parrain_id);
                if ($parrain && $parrain->is_active) {
                    $this->updateParrainAndAncestorsTeamPV($parrain);
                }
            }

            DB::commit();

            // Recalcul automatique du grade
            $rankChanged = $this->recalculateUserRank($user);

            $user->refresh();
            $user = User::with(['rank', 'parrain'])->find($user->id);

            $rankName = $user->rank ?? 'Distributeur';
            $rankLevel = $user->rank_level ?? 1;
            $cumulPV = ($user->pv_balance ?? 0) + ($user->team_pv ?? 0);

            Log::info('PV ajoutes avec recalcul automatique', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'amount' => $amount,
                'period' => $period,
                'rank' => $rankName,
                'rank_level' => $rankLevel,
                'rank_changed' => $rankChanged,
            ]);

            $message = "{$amount} PV ajoutes pour {$period} a {$user->name}.\n";
            $message .= "Grade: {$rankName} (Niv. {$rankLevel})\n";
            
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $message .= "\nLe parrain {$parrain->name} a recu {$amount} PV dans son equipe.";
                    $message .= "\nteam_pv du parrain: " . number_format($parrain->team_pv ?? 0, 1, ',', ' ') . "\n";
                }
            }
            
            $message .= "\nLes recalculs complets sont en cours en arriere-plan.";

            return redirect()->route('admin.pv.show', $user->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout PV mensuel', [
                'user_id' => $user->id,
                'period' => $period,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // ============================================================
    // METHODES DE RECALCUL DU TEAM_PV AVEC TOUS LES DESCENDANTS ET ANCETRES
    // ============================================================

    /**
     * Met à jour le team_pv du parrain et de TOUS ses ancêtres
     */
    private function updateParrainAndAncestorsTeamPV(User $parrain): void
    {
        try {
            // 1. Mettre à jour le parrain direct
            $this->updateParrainTeamPV($parrain);

            // 2. Mettre à jour TOUS les ancêtres du parrain
            $ancestor = $parrain->parrain;
            $level = 0;
            $maxLevel = 10;
            
            while ($ancestor && $level < $maxLevel) {
                $this->updateParrainTeamPV($ancestor);
                $ancestor = $ancestor->parrain;
                $level++;
            }

            Log::info('team_pv mis a jour pour le parrain et tous ses ancetres', [
                'parrain_id' => $parrain->id,
                'parrain_name' => $parrain->name,
                'ancestors_updated' => $level,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise a jour team_pv du parrain et ancetres', [
                'parrain_id' => $parrain->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Met à jour le team_pv d'un seul parrain
     */
    private function updateParrainTeamPV(User $parrain): void
    {
        try {
            $teamData = $this->calculateTeamPVRecursive($parrain);

            $parrain->team_pv = $teamData['pv'];
            $parrain->team_bv = $teamData['bv'];
            $parrain->total_team = $teamData['total'];
            $parrain->saveQuietly();

            $this->rankCalculator->recalculateUserRank($parrain, 'Mise a jour team_pv');

        } catch (\Exception $e) {
            Log::error('Erreur mise a jour team_pv du parrain', [
                'parrain_id' => $parrain->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calcul récursif du team_pv avec TOUS les descendants
     */
    private function calculateTeamPVRecursive(User $user): array
    {
        $totalPV = $user->pv_balance ?? 0;
        $totalBV = $user->bv_balance ?? 0;
        $totalCount = 0;

        $filleuls = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($filleuls as $filleul) {
            $childData = $this->calculateTeamPVRecursive($filleul);
            $totalPV += $childData['pv'];
            $totalBV += $childData['bv'];
            $totalCount += 1 + $childData['total'];
        }

        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }

    /**
     * Recalculer le grade d'un utilisateur
     */
    private function recalculateUserRank(User $user): bool
    {
        try {
            $user->updateTeamPVOptimized();
            $rankChanged = $user->updateRankSync();
            
            dispatch(new UpdateTeamPV($user->id, true))->onQueue('low');
            dispatch(new UpdateRanks($user->id))->onQueue('low');
            dispatch(new CalculatePVBV($user->id))->onQueue('low');
            
            if ($user->parrain_id) {
                dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
            }
            
            Cache::forget("user_rank_{$user->id}");
            Cache::forget("rank_calculation_{$user->id}");
            Cache::forget("descendants_{$user->id}");
            Cache::forget("descendants_count_{$user->id}");
            
            return $rankChanged;
            
        } catch (\Exception $e) {
            Log::error('Erreur recalcul grade', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}