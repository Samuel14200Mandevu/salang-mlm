<?php
// app/Http/Controllers/Admin/AdminPVImportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PVHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminPVImportController extends Controller
{
    /**
     * Page d'import des PV mensuels
     */
    public function index()
    {
        return view('admin.pv.import');
    }

    /**
     * Rechercher un utilisateur (API)
     */
    public function searchUser(Request $request)
    {
        $query = $request->input('search');
        
        // Validation
        if (!$query || strlen($query) < 2) {
            return response()->json(['error' => 'Recherche trop courte (minimum 2 caractères)'], 400);
        }
        
        // Rechercher par nom, email ou sponsor_id
        $user = User::with(['rank', 'parrain'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->first();
        
        if (!$user) {
            return response()->json(['error' => 'Aucun membre trouvé avec ces critères'], 404);
        }
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'sponsor_id' => $user->sponsor_id,
            'rank_name' => $user->rank_name ?? 'Distributeur',
            'rank_level' => $user->rank_level ?? 1,
            'pv_balance' => $user->pv_balance ?? 0,
            'monthly_pv' => $user->monthly_pv ?? 0,
            'team_pv' => $user->team_pv ?? 0,
            'bv_balance' => $user->bv_balance ?? 0,
            'total_team' => $user->total_team ?? 0,
            'parrain_name' => $user->parrain?->name,
            'parrain_sponsor_id' => $user->parrain?->sponsor_id,
        ]);
    }

    /**
     * Récupérer les statistiques d'un utilisateur par ID (API)
     */
    public function getUserStats($userId)
    {
        $user = User::with(['rank', 'parrain'])->find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'sponsor_id' => $user->sponsor_id,
            'rank_name' => $user->rank_name ?? 'Distributeur',
            'rank_level' => $user->rank_level ?? 1,
            'pv_balance' => $user->pv_balance ?? 0,
            'monthly_pv' => $user->monthly_pv ?? 0,
            'team_pv' => $user->team_pv ?? 0,
            'bv_balance' => $user->bv_balance ?? 0,
            'total_team' => $user->total_team ?? 0,
            'parrain_name' => $user->parrain?->name,
            'parrain_sponsor_id' => $user->parrain?->sponsor_id,
        ]);
    }

    /**
     * Importer des PV depuis un fichier CSV
     */
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

                // Valider les données
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

                // Trouver l'utilisateur
                $user = User::where('sponsor_id', $data['member_id'])
                    ->orWhere('id', $data['member_id'])
                    ->first();

                if (!$user) {
                    $errors[] = "Membre non trouvé: " . $data['member_id'] . " - " . $data['member_name'];
                    continue;
                }

                $orderDate = date('Y-m-d', strtotime($data['order_date']));

                // Vérifier si une commande existe déjà
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

                // Ajouter l'item
                $totalPv = (float) $data['total_pv'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_code' => $data['product_code'],
                    'product_name' => $data['product_name'],
                    'quantity' => (int) $data['quantity'],
                    'unit_pv' => (float) $data['unit_pv'],
                    'total_pv' => $totalPv,
                    'unit_bv' => (float) $data['unit_pv'] * 0.8,
                    'total_bv' => $totalPv * 0.8,
                ]);

                // Mettre à jour le total de la commande
                $order->increment('total_pv', $totalPv);
                $order->increment('total_bv', $totalPv * 0.8);

                // Ajouter à l'historique
                PVHistory::create([
                    'user_id' => $user->id,
                    'amount' => $totalPv,
                    'date' => $orderDate,
                    'period' => $period,
                    'type' => 'personal',
                    'notes' => "Import CSV - {$data['product_code']} - {$data['product_name']}",
                    'created_by' => auth()->id(),
                ]);

                // Ajouter les PV à l'utilisateur
                $user->pv_balance += $totalPv;
                $user->monthly_pv += $totalPv;
                $user->bv_balance += $totalPv * 0.8;
                $user->monthly_bv += $totalPv * 0.8;
                $user->saveQuietly();

                // Marquer l'utilisateur pour mise à jour du grade
                if (!in_array($user->id, $usersUpdated)) {
                    $usersUpdated[] = $user->id;
                }

                $imported++;
            }

            fclose($handle);

            // Mettre à jour les grades des utilisateurs qui ont changé
            foreach ($usersUpdated as $userId) {
                $user = User::find($userId);
                if ($user) {
                    // Recalculer team_pv
                    $user->updateTeamPVOptimized();
                    // Mettre à jour le grade (synchrone pour l'import)
                    $user->updateRankSync();
                    
                    // Mettre à jour les ancêtres
                    if ($user->parrain_id) {
                        $ancestor = User::find($user->parrain_id);
                        if ($ancestor) {
                            $ancestor->updateTeamPVOptimized();
                            $ancestor->updateRankSync();
                        }
                    }
                }
            }

            DB::commit();

            $message = "✅ Import terminé !\n";
            $message .= "📊 Lignes importées: {$imported}\n";
            $message .= "👥 Utilisateurs mis à jour: " . count($usersUpdated) . "\n";

            if (!empty($errors)) {
                $message .= "⚠️ Erreurs: " . count($errors) . "\n";
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

    /**
     * Ajouter manuellement des PV pour un mois spécifique
     */
    public function addMonthlyPV(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m|min:2020-01',
            'amount' => 'required|numeric|min:0.1',
            'type' => 'required|in:personal,team,monthly',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($userId);
        $period = $request->period;
        $amount = (float) $request->amount;

        DB::beginTransaction();
        try {
            $date = $period . '-01';

            // Ajouter à l'historique
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
            }

            if ($request->type === 'team') {
                $user->team_pv += $amount;
            }

            $user->saveQuietly();
            $user->updateTeamPVOptimized();
            $user->updateRankSync();

            // Mettre à jour les ancêtres
            if ($user->parrain_id) {
                $ancestor = User::find($user->parrain_id);
                if ($ancestor) {
                    $ancestor->updateTeamPVOptimized();
                    $ancestor->updateRankSync();
                }
            }

            DB::commit();

            return redirect()->route('admin.pv.show', $user->id)
                ->with('success', "✅ {$amount} PV ajoutés pour {$period} à {$user->name}. Nouveau grade: {$user->rank_name}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout PV mensuel', [
                'user_id' => $user->id,
                'period' => $period,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
}