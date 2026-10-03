<?php

namespace App\Http\Controllers\Admin;


use App\Support\MlmPeriod;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PVHistory;
use App\Models\Rank;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\TeamPVCalculator;
use App\Jobs\RecalculateAfterPVImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
            return response()->json(['error' => 'Recherche trop courte'], 400);
        }
        $user = User::with(['rank', 'parrain'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('sponsor_id', 'LIKE', "%{$query}%");
            })
            ->first();
        if (!$user) {
            return response()->json(['error' => 'Aucun membre trouve'], 404);
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
        $packagesAttributed = 0;

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

                $user = $this->findUserBySponsorId($data['member_id']);

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
                $user->bv_balance += $totalPv * 0.8;

                if ($period === MlmPeriod::current()) {
                    $user->monthly_pv += $totalPv;
                    $user->monthly_bv += $totalPv * 0.8;
                }

                if (empty($user->package_id)) {
                    $user->package_id = 1;
                    $packagesAttributed++;
                }

                $user->saveQuietly();

                if (!in_array($user->id, $usersUpdated)) {
                    $usersUpdated[] = $user->id;
                }

                $imported++;
            }

            fclose($handle);
            DB::commit();

            if (!empty($usersUpdated) && count($usersUpdated) <= 50) {
                $this->recalculateSync($usersUpdated, $period);
            } elseif (!empty($usersUpdated)) {
                RecalculateAfterPVImport::dispatch($usersUpdated, $period)
                    ->onQueue('rank-recalculation');
            }

            $message = "Import termine !\n";
            $message .= "Lignes importees: {$imported}\n";
            $message .= "Utilisateurs mis a jour: " . count($usersUpdated) . "\n";
            if ($packagesAttributed > 0) {
                $message .= "Packages attribués (id=1) : {$packagesAttributed}\n";
            }
            $message .= " Recalcul " . (count($usersUpdated) <= 50 ? "effectué" : "en arrière-plan") . ".";

            if (!empty($errors)) {
                $message .= "\nErreurs: " . count($errors) . "\n";
                Log::warning('Erreurs import CSV', $errors);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur import CSV', [
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * IMPORT HISTORIQUE PV — VERSION CORRIGÉE
     * ═══════════════════════════════════════════════════════════════════
     * 
     * RÈGLE MÉTIER (NOUVELLE) :
     *   - Si un PVHistory existe déjà pour ce user + cette période :
     *     • Montant IDENTIQUE → IGNORER (pas de doublon)
     *     • Montant DIFFÉRENT → REMPLACER (écraser avec la nouvelle valeur)
     *   - Si pas de PVHistory → CRÉER + incrémenter pv_balance
     *   - Le pv_balance n'est JAMAIS incrémenté en double
     * 
     * CORRECTIONS :
     *   - Recalcul SYNCHRONE pour petits imports (≤ 50 users)
     *   - Recalcul ASYNCHRONE (queue) pour gros imports (> 50 users)
     *   - Recalcul des ancêtres récursifs via RecalculateAfterPVImport
     */
    public function importHistoricalPV(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480',
            'period' => 'required|date_format:Y-m',
            'skip_first_rows' => 'nullable|integer|min:0|max:10',
            'update_rank' => 'nullable|boolean',
            'dry_run' => 'nullable|boolean',
        ]);

        $file = $request->file('excel_file');
        $period = $request->period;
        $skipRows = (int) $request->input('skip_first_rows', 2);
        $updateRank = $request->boolean('update_rank', false);
        $dryRun = $request->boolean('dry_run', false);

        $isCurrentMonth = ($period === MlmPeriod::current());

        $startTime = microtime(true);

        try {
            $rows = $this->readSpreadsheet($file, $skipRows);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lecture fichier: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'Le fichier est vide ou illisible.');
        }

        $colId = 0;
        $colRank = 1;
        $colName = 2;
        $colPv = 3;

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $warnings = [];
        $usersUpdated = [];
        $totalPV = 0;
        $packagesAttributed = 0;
        $createdMembers = [];
        $rejectedMembers = [];
        $processedIds = [];

        DB::beginTransaction();
        try {
            $allUsers = User::where('is_active', true)->get();
            $usersBySponsorId = [];
            $usersById = [];

            foreach ($allUsers as $u) {
                if ($u->sponsor_id) {
                    $usersBySponsorId[(string) $u->sponsor_id] = $u;
                }
                $usersById[$u->id] = $u;
            }

            $existingHistories = PVHistory::where('period', $period)
                ->where('type', 'personal')
                ->where('notes', 'LIKE', "Import historique {$period}%")
                ->get()
                ->keyBy('user_id');

            $allNamesLower = User::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();
            $allNamesSet = array_flip($allNamesLower);

            $ranksById = Rank::all()->keyBy('id');
            $ranksByLevel = Rank::all()->keyBy('level');

            Log::info('Import historique préparé', [
                'period' => $period,
                'is_current_month' => $isCurrentMonth,
                'users_count' => count($allUsers),
                'histories_count' => $existingHistories->count(),
                'rows_count' => count($rows),
            ]);

            foreach ($rows as $index => $row) {
                $lineNum = $index + $skipRows + 1;

                $memberId   = trim((string) ($row[$colId] ?? ''));
                $rankValue  = $row[$colRank] ?? null;
                $memberName = trim((string) ($row[$colName] ?? ''));
                $pvValue    = $row[$colPv] ?? null;

                if ($memberId === '' && $memberName === '') continue;
                if (stripos($memberId, '=SUM') !== false || stripos((string)$pvValue, '=SUM') !== false) continue;

                $upperId = strtoupper($memberId);
                if (in_array($upperId, ['ID']) || stripos($memberId, 'BONUS LIST') !== false) continue;

                if (strpos($memberId, ' ') !== false) {
                    $parts = preg_split('/\s+/', $memberId);
                    $memberId = $parts[0] ?? $memberId;
                    if ($memberName === '' && isset($parts[1])) {
                        $memberName = implode(' ', array_slice($parts, 1));
                    }
                }

                if (!is_numeric($memberId)) {
                    if ($memberName !== '') {
                        $warnings[] = "Ligne {$lineNum}: ID vide pour '{$memberName}' → ligne ignorée";
                    } else {
                        $errors[] = "Ligne {$lineNum}: ID invalide '{$memberId}'";
                    }
                    $skipped++;
                    continue;
                }

                $pv = $this->parsePV($pvValue);
                if ($pv === null) {
                    $warnings[] = "Ligne {$lineNum}: PV ignoré pour ID {$memberId}";
                    $skipped++;
                    continue;
                }

                if ($pv <= 0) { $skipped++; continue; }

                $cleanId = trim((string) $memberId);

                $user = $usersBySponsorId[$cleanId] ?? null;

                if (!$user && is_numeric($cleanId)) {
                    $user = $usersById[(int) $cleanId] ?? null;
                }

                if (!$user) {
                    $withoutLeadingZeros = ltrim($cleanId, '0');
                    if ($withoutLeadingZeros !== $cleanId && $withoutLeadingZeros !== '') {
                        $user = $usersBySponsorId[$withoutLeadingZeros] ?? null;
                    }
                }

                if ($user && isset($processedIds[$user->id])) {
                    $warnings[] = "Ligne {$lineNum}: DOUBLON ID {$memberId} - PV cumulé avec ligne {$processedIds[$user->id]}";
                }

                if ($user) {
                    $processedIds[$user->id] = $lineNum;
                }

                // ✅ Flag pour éviter le double-incrément
                $skipIncrement = false;

                if (!$user) {
                    if ($memberName === '') {
                        $memberName = "MEMBRE {$memberId}";
                        $warnings[] = "Ligne {$lineNum}: Nom vide pour ID {$memberId} → création avec nom générique";
                    }

                    $alreadyPlanned = false;
                    foreach ($createdMembers as $cm) {
                        if ($cm['sponsor_id'] === $memberId) {
                            $alreadyPlanned = true;
                            break;
                        }
                    }

                    if ($alreadyPlanned) {
                        $warnings[] = "Ligne {$lineNum}: ID {$memberId} déjà prévu pour création - PV cumulé";

                        if ($dryRun) {
                            $totalPV += $pv;
                            $imported++;
                            continue;
                        }

                        $user = $usersBySponsorId[$cleanId] ?? null;
                        if (!$user) {
                            $skipped++;
                            continue;
                        }
                    } else {
                        if ($dryRun) {
                            $checkResult = $this->canCreateMemberOptimized($memberId, $memberName, $allNamesSet);

                            if ($checkResult['can_create']) {
                                $createdMembers[] = [
                                    'sponsor_id' => $memberId,
                                    'name' => $memberName,
                                    'reason' => 'Simulation (dry-run)',
                                ];
                            } else {
                                $rejectedMembers[] = [
                                    'sponsor_id' => $memberId,
                                    'name' => $memberName,
                                    'reason' => $checkResult['reason'],
                                    'existing' => $checkResult['existing'] ?? null,
                                ];
                            }

                            $totalPV += $pv;
                            $imported++;
                            continue;
                        }

                        $user = $this->createMissingMemberOptimized(
                            $memberId,
                            $memberName,
                            $createdMembers,
                            $rejectedMembers,
                            $warnings,
                            $allNamesSet
                        );

                        if (!$user) {
                            $skipped++;
                            continue;
                        }

                        if ($user->sponsor_id) {
                            $usersBySponsorId[(string) $user->sponsor_id] = $user;
                        }
                        $usersById[$user->id] = $user;
                    }
                }

                // ═══════════════════════════════════════════════════════════
                // ✅ NOUVELLE LOGIQUE : Comparer les montants
                // ═══════════════════════════════════════════════════════════
                $existingHistory = $existingHistories->get($user->id);

                if ($existingHistory) {
                    $existingAmount = (float) ($existingHistory->amount ?? 0);
                    $newAmount = (float) $pv;

                    if (abs($existingAmount - $newAmount) < 0.01) {
                        // ✅ Montant IDENTIQUE → ne rien faire
                        $warnings[] = "Ligne {$lineNum}: PV déjà importé pour {$user->name} ({$existingAmount} PV) → ignoré";
                        $skipIncrement = true;

                    } else {
                        // ✅ Montant DIFFÉRENT → remplacer
                        if (!$dryRun) {
                            $oldAmount = $existingAmount;

                            $existingHistory->amount = $newAmount;
                            $existingHistory->save();

                            // Ajuster pv_balance : -ancien +nouveau
                            $user->pv_balance = ($user->pv_balance ?? 0) - $oldAmount + $newAmount;
                            $user->bv_balance = ($user->bv_balance ?? 0) - ($oldAmount * 0.8) + ($newAmount * 0.8);

                            if ($isCurrentMonth) {
                                $user->monthly_pv = ($user->monthly_pv ?? 0) - $oldAmount + $newAmount;
                                $user->monthly_bv = ($user->monthly_bv ?? 0) - ($oldAmount * 0.8) + ($newAmount * 0.8);
                            }

                            $user->saveQuietly();
                        }

                        $warnings[] = "Ligne {$lineNum}: PV REMPLACÉ pour {$user->name} : {$existingAmount} → {$newAmount} PV";
                        $skipIncrement = true;
                    }
                } else {
                    // ✅ Nouveau PV → créer
                    if (!$dryRun) {
                        $newHistory = PVHistory::create([
                            'user_id'    => $user->id,
                            'amount'     => $pv,
                            'date'       => $period . '-01',
                            'period'     => $period,
                            'type'       => 'personal',
                            'notes'      => "Import historique {$period} - {$memberName}",
                            'created_by' => auth()->id(),
                        ]);

                        $existingHistories->put($user->id, $newHistory);
                    }
                    // $skipIncrement reste false → l'incrément normal se fera
                }

                // ═══════════════════════════════════════════════════════════
                // Incrémentation SEULEMENT si pas déjà ajusté
                // ═══════════════════════════════════════════════════════════
                if (!$dryRun && !$skipIncrement) {
                    $user->pv_balance = ($user->pv_balance ?? 0) + $pv;
                    $user->bv_balance = ($user->bv_balance ?? 0) + ($pv * 0.8);

                    if ($isCurrentMonth) {
                        $user->monthly_pv = ($user->monthly_pv ?? 0) + $pv;
                        $user->monthly_bv = ($user->monthly_bv ?? 0) + ($pv * 0.8);
                    }

                    if (empty($user->package_id)) {
                        $user->package_id = 1;
                        $packagesAttributed++;
                    }

                    if ($updateRank && is_numeric($rankValue) && $rankValue > 0 && $rankValue <= 20) {
                        $rank = $ranksByLevel->get((int) $rankValue);

                        if ($rank) {
                            $user->rank_id = $rank->id;
                            $user->rank = $rank->name;
                            $user->rank_level = $rank->level;
                            $user->last_rank_update = now();
                        } else {
                            $warnings[] = "Ligne {$lineNum}: Rang niveau {$rankValue} introuvable pour ID {$memberId}";
                        }
                    }

                    $user->saveQuietly();

                    if (!in_array($user->id, $usersUpdated)) {
                        $usersUpdated[] = $user->id;
                    }
                }

                $totalPV += $pv;
                $imported++;
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur import historique PV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }

        if (!$dryRun && !empty($usersUpdated)) {
            if (count($usersUpdated) <= 50) {
                $this->recalculateSync($usersUpdated, $period);
            } else {
                RecalculateAfterPVImport::dispatch($usersUpdated, $period)
                    ->onQueue('rank-recalculation');
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $message  = ($dryRun ? "SIMULATION (dry-run)\n" : "Import terminé !\n");
        $message .= "Période : {$period}\n";
        $message .= "Mois en cours ? " . ($isCurrentMonth ? "OUI" : "NON") . "\n";
        $message .= "Lignes traitées : " . count($rows) . "\n";
        $message .= "PV importés : {$imported}\n";
        $message .= "Total PV : " . number_format($totalPV, 1, ',', ' ') . "\n";
        $message .= "Utilisateurs mis à jour : " . count($usersUpdated) . "\n";
        $message .= "Durée : {$duration} sec\n";

        if ($packagesAttributed > 0) {
            $message .= "\nPackages attribués (id=1) : {$packagesAttributed}\n";
        }

        if (!empty($createdMembers)) {
            $message .= "\n" . ($dryRun ? "Membres à créer" : "Membres créés") . " : " . count($createdMembers) . "\n";
            $message .= "Liste :\n";
            foreach (array_slice($createdMembers, 0, 20) as $m) {
                $message .= "   {$m['sponsor_id']} : {$m['name']}\n";
            }
            if (count($createdMembers) > 20) {
                $message .= "  ... et " . (count($createdMembers) - 20) . " autres\n";
            }
        }

        if (!empty($rejectedMembers)) {
            $message .= "\n Membres refusés : " . count($rejectedMembers) . "\n";
            $message .= "Liste :\n";
            foreach (array_slice($rejectedMembers, 0, 15) as $m) {
                $message .= "   {$m['sponsor_id']} : {$m['name']}\n";
                $message .= "      Raison : {$m['reason']}\n";
                if (isset($m['existing'])) {
                    $message .= "      Existant : {$m['existing']['name']} (code: {$m['existing']['sponsor_id']})\n";
                }
            }
            if (count($rejectedMembers) > 15) {
                $message .= "  ... et " . (count($rejectedMembers) - 15) . " autres\n";
            }
        }

        $message .= "\nIgnorés : {$skipped}\n";

        if (!$dryRun && !empty($usersUpdated)) {
            $message .= "\n Recalcul " . (count($usersUpdated) <= 50 ? "effectué immédiatement" : "en arrière-plan") . ".";
        }

        if (!empty($errors)) {
            $message .= "\nErreurs (" . count($errors) . "):\n" . implode("\n", array_slice($errors, 0, 20));
        }
        if (!empty($warnings)) {
            $message .= "\nAvertissements (" . count($warnings) . "):\n" . implode("\n", array_slice($warnings, 0, 20));
        }

        return back()->with($dryRun ? 'info' : 'success', $message);
    }

    /**
     * Recalcul synchrone des team_pv + grades
     */
    protected function recalculateSync(array $userIds, string $period): void
    {
        Log::info('Recalcul synchrone démarré', [
            'users_count' => count($userIds),
            'period' => $period,
        ]);

        $startTime = microtime(true);

        $teamPVCalculator = app(TeamPVCalculator::class);
        $rankCalculator = app(AdvancedRankCalculator::class);

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            try {
                $teamPVCalculator->updateUser($user);
                $teamPVCalculator->updateAncestors($user);

                $user->refresh();
                $rankCalculator->clearCache();
                $rankCalculator->recalculateUserRank($user, "Import synchrone {$period}");

                $ancestors = $teamPVCalculator->getAncestorIds($user);
                foreach ($ancestors as $ancestorId) {
                    $ancestor = User::find($ancestorId);
                    if (!$ancestor) continue;

                    $rankCalculator->clearCache();
                    $rankCalculator->recalculateUserRank($ancestor, "Ancêtre de {$user->name}");
                }

            } catch (\Exception $e) {
                Log::error('Erreur recalcul synchrone user', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        Log::info('Recalcul synchrone terminé', [
            'users_count' => count($userIds),
            'duration_seconds' => $duration,
        ]);
    }

    private function readSpreadsheet($file, int $skipRows = 0): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                throw new \Exception('PhpSpreadsheet non installé.');
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray(null, true, true, false);

            if ($skipRows > 0) {
                $data = array_slice($data, $skipRows);
            }

            foreach ($data as $row) {
                if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) continue;
                $rows[] = array_values($row);
            }
        } else {
            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) throw new \Exception('Impossible d\'ouvrir le fichier');

            $lineNum = 0;

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $lineNum++;
                if ($lineNum <= $skipRows) continue;
                if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) continue;
                $rows[] = array_values($row);
            }
            fclose($handle);
        }

        return $rows;
    }

    private function canCreateMemberOptimized($sponsorId, $name, array &$allNamesSet): array
    {
        $name = trim($name);

        if (empty($name)) {
            return ['can_create' => false, 'reason' => "Nom vide"];
        }

        if (preg_match('/^MEMBRE \d+$/i', $name)) {
            return ['can_create' => true, 'reason' => 'OK (nom générique)'];
        }

        if (strpos($name, '?') !== false) {
            return ['can_create' => false, 'reason' => "Nom contient '?'"];
        }

        if (strlen($name) < 5 || str_word_count($name) < 2) {
            return ['can_create' => false, 'reason' => "Nom invalide (trop court)"];
        }

        $nameLower = strtolower($name);
        if (isset($allNamesSet[$nameLower])) {
            return [
                'can_create' => false,
                'reason' => "Nom similaire existe déjà",
                'existing' => [
                    'name' => $name,
                    'sponsor_id' => 'N/A',
                ],
            ];
        }

        return ['can_create' => true, 'reason' => 'OK'];
    }

    private function createMissingMemberOptimized(
        $sponsorId,
        $name,
        array &$createdMembers,
        array &$rejectedMembers,
        array &$warnings,
        array &$allNamesSet
    ): ?User {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $check = $this->canCreateMemberOptimized($sponsorId, $name, $allNamesSet);

        if (!$check['can_create']) {
            $rejectedMembers[] = [
                'sponsor_id' => $sponsorId,
                'name' => $name,
                'reason' => $check['reason'],
                'existing' => $check['existing'] ?? null,
            ];
            return null;
        }

        try {
            $email = $this->generateUniqueEmail($name, $sponsorId);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => null,
                'sponsor_id' => $sponsorId,
                'parrain_id' => null,
                'rank' => 'Distributeur',
                'rank_id' => 1,
                'rank_level' => 1,
                'package_id' => 1,
                'country' => 'R.D.Congo',
                'city' => 'Goma',
                'is_active' => 1,
                'user_type' => 'member',
                'kyc_status' => 'not_submitted',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            ]);

            $allNamesSet[strtolower($name)] = $name;

            $createdMembers[] = [
                'id' => $user->id,
                'sponsor_id' => $sponsorId,
                'name' => $name,
                'email' => $email,
            ];

            return $user;

        } catch (\Exception $e) {
            $warnings[] = "Erreur création membre '{$name}' (ID: {$sponsorId}) : " . $e->getMessage();
            return null;
        }
    }

    private function generateUniqueEmail($name, $sponsorId): string
    {
        $base = strtolower(trim($name));

        $base = str_replace(
            ['à','â','ä','á','ã','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','ö','õ','ù','ú','û','ü','ý','ÿ','œ','æ',
             'À','Â','Ä','Á','Ã','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Ö','Õ','Ù','Ú','Û','Ü','Ý','Ÿ','Œ','Æ'],
            ['a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','oe','ae',
             'a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','oe','ae'],
            $base
        );

        $base = preg_replace('/[^a-z0-9]/', '', $base);

        if (strlen($base) > 30) {
            $base = substr($base, 0, 30);
        }

        if (empty($base)) {
            $base = 'user' . $sponsorId;
        }

        $email = $base . '@salanggroup.com';
        $original = $email;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $parts = explode('@', $original);
            $email = $parts[0] . $counter . '@' . $parts[1];
            $counter++;
        }

        return $email;
    }

    private function findUserBySponsorId($memberId): ?User
    {
        $cleanId = trim((string) $memberId);
        $cleanId = preg_replace('/\s+/', '', $cleanId);

        if ($cleanId === '') return null;

        $user = User::where('sponsor_id', $cleanId)->first();
        if ($user) return $user;

        if (is_numeric($cleanId)) {
            $user = User::where('id', (int) $cleanId)->first();
            if ($user) return $user;
        }

        $user = User::where('sponsor_id', 'LIKE', "%{$cleanId}%")->first();
        if ($user) return $user;

        $withoutLeadingZeros = ltrim($cleanId, '0');
        if ($withoutLeadingZeros !== $cleanId && $withoutLeadingZeros !== '') {
            $user = User::where('sponsor_id', $withoutLeadingZeros)->first();
            if ($user) return $user;
        }

        return null;
    }

    private function parsePV($value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return (float) $value;
        $str = trim((string) $value);
        $str = str_replace(',', '.', $str);
        $str = preg_replace('/\s+/', '', $str);
        if (!is_numeric($str)) return null;
        return (float) $str;
    }

    /**
     * PV mensuel — recalcul SYNCHRONE
     */
    public function addMonthlyPV(Request $request, $userId)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0.1',
            'type' => 'required|in:personal,team,monthly',
            'notes' => 'nullable|string|max:255',
        ]);

        $period = $request->period;
        if ($period < '2020-01') {
            return back()->with('error', 'La periode ne peut pas etre anterieure a 2020-01');
        }

        $user = User::with(['rank', 'parrain'])->findOrFail($userId);
        $amount = (float) $request->amount;

        $isCurrentMonth = ($period === MlmPeriod::current());

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
                $user->bv_balance += $amount;

                if ($isCurrentMonth) {
                    $user->monthly_pv += $amount;
                    $user->monthly_bv += $amount;
                }
            }
            if ($request->type === 'team') {
                $user->team_pv += $amount;
                $user->team_bv += $amount;
            }

            if (empty($user->package_id)) {
                $user->package_id = 1;
            }

            $user->saveQuietly();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout PV mensuel', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }

        $this->recalculateSync([$user->id], $period);

        $user->refresh();
        $user = User::with(['rank', 'parrain'])->find($user->id);

        $message  = "{$amount} PV ajoutés pour {$period} à {$user->name}.\n";
        $message .= "Mois en cours ? " . ($isCurrentMonth ? "OUI" : "NON") . "\n";
        $message .= "Grade: " . ($user->rank ?? 'Distributeur') . " (Niv. " . ($user->rank_level ?? 1) . ")";
        $message .= "\n\nRecalcul effectué immédiatement.";

        return redirect()->route('admin.pv.show', $user->id)
            ->with('success', $message);
    }
}