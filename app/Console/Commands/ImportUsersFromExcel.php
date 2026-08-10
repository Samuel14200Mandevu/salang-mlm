<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportUsersFromExcel extends Command
{
    protected $signature = 'users:import 
                            {file? : Chemin vers le fichier Excel}
                            {--dry-run : Simuler l\'importation sans modifier la base}
                            {--skip-header : Ignorer la première ligne (en-tête)}
                            {--force : Forcer l\'importation en production}
                            {--step1 : Importer uniquement les utilisateurs (sans parrain)}
                            {--step2 : Mettre à jour uniquement les parrains}
                            {--clear-first : Supprimer tous les utilisateurs avant l\'import}
                            {--id-start=3 : ID de départ pour les utilisateurs}';
    
    protected $description = 'Importe les utilisateurs depuis un fichier Excel avec gestion du parrainage en 2 étapes';

    protected $inserted = 0;
    protected $errors = [];
    protected $skipped = 0;
    protected $parrainUpdated = 0;
    protected $parrainNotFound = 0;
    protected $userCodes = [];
    protected $yearCounter = [
        '2023' => 0,
        '2024' => 0,
        '2025' => 0,
        '2026' => 0,
    ];
    protected $yearStartId = [
        '2023' => null,
        '2024' => null,
        '2025' => null,
        '2026' => null,
    ];

    // Liste des mots qui indiquent une ligne de titre/mois
    protected $skipPatterns = [
        '/^(JANVIER|FEVRIER|MARS|AVRIL|MAI|JUIN|JUILLET|AOUT|SEPTEMBRE|OCTOBRE|NOVEMBRE|DECEMBRE)/i',
        '/^ADHESION/i',
        '/^N°\s+DATE/i',
        '/^NOM/i',
        '/^CODE/i',
        '/^TOTAL/i',
        '/^Récapitulatif/i',
        '/^---/',
        '/^\s*$/',
    ];

    public function handle()
    {
        // === VÉRIFICATION DE L'ENVIRONNEMENT ===
        $currentEnv = app()->environment();
        $force = $this->option('force');

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║     📥 IMPORTATION DES UTILISATEURS DEPUIS EXCEL           ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("🌍 Environnement actuel : " . strtoupper($currentEnv));
        $this->line("📊 Base de données : " . config('database.connections.mysql.database'));
        $this->newLine();

        if ($currentEnv === 'production' && !$force) {
            $this->error("❌ Utilisez --force pour importer en production");
            return 1;
        }

        // === CHEMIN DU FICHIER ===
        $defaultPath = '/home/samuel-mandevu/Téléchargements/ADHESION FICHE COMPLET 2023-2024 2025,2026.xlsx';
        $filePath = $this->argument('file') ?? $defaultPath;
        $dryRun = $this->option('dry-run');
        $skipHeader = $this->option('skip-header');
        $clearFirst = $this->option('clear-first');
        $step1 = $this->option('step1');
        $step2 = $this->option('step2');
        $idStart = (int) $this->option('id-start');

        if ($dryRun) {
            $this->warn('⚠️  MODE DRY-RUN - Aucune modification');
            $this->newLine();
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé : $filePath");
            return 1;
        }

        try {
            // === CHARGER LE FICHIER ===
            $this->info("⏳ Chargement du fichier Excel...");
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $totalRows = count($rows);
            $startRow = $skipHeader ? 1 : 0;

            // === FILTRER LES LIGNES VALIDES ===
            $this->info("🔍 Filtrage des lignes valides...");
            $validRows = $this->filterValidRows($rows, $startRow);
            $rowsToProcess = count($validRows);

            $this->info("✅ Lignes valides trouvées : " . number_format($rowsToProcess) . " utilisateurs");
            $this->line("⏭️  Lignes ignorées (titres/mois) : " . number_format($totalRows - $rowsToProcess - $startRow));
            $this->newLine();

            if ($rowsToProcess === 0) {
                $this->error("❌ Aucune ligne valide trouvée dans le fichier");
                return 1;
            }

            // === NETTOYAGE OPTIONNEL ===
            if ($clearFirst && !$dryRun && $step1) {
                if ($this->confirm("⚠️ Supprimer tous les utilisateurs (sauf admin) ?", false)) {
                    $this->info("🗑️ Suppression des utilisateurs...");
                    User::where('id', '>', 1)->delete();
                    DB::statement('ALTER TABLE users AUTO_INCREMENT = ' . $idStart);
                    $this->info("✅ Utilisateurs supprimés, AUTO_INCREMENT réinitialisé à " . $idStart);
                    $this->newLine();
                }
            }

            // === ÉTAPE 1 : Importer les utilisateurs ===
            if ($step1 || (!$step1 && !$step2)) {
                $this->info("🚀 ÉTAPE 1 : Importation des utilisateurs (sans parrain)...");
                $this->info("📌 Les IDs seront attribués par année d'adhésion");
                $this->newLine();
                $this->importUsers($validRows, $dryRun, $idStart);
                $this->showStep1Summary($dryRun);
            }

            // === ÉTAPE 2 : Mettre à jour les parrains ===
            if ($step2 || (!$step1 && !$step2)) {
                if ($this->inserted > 0 || $step2) {
                    $this->newLine();
                    $this->info("🚀 ÉTAPE 2 : Mise à jour des relations de parrainage...");
                    $this->updateParrains($validRows, $dryRun);
                    $this->showStep2Summary($dryRun);
                } else {
                    $this->warn("⚠️ Aucun utilisateur importé, étape 2 ignorée");
                }
            }

            // === RÉSUMÉ GLOBAL ===
            $this->showGlobalSummary($dryRun);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            $this->error("📄 Détails : " . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Filtre les lignes pour ne garder que les utilisateurs valides
     */
    protected function filterValidRows($rows, $startRow)
    {
        $validRows = [];

        foreach ($rows as $index => $row) {
            if ($index < $startRow) continue;

            $firstCell = trim($row[0] ?? '');
            $secondCell = trim($row[2] ?? '');
            $thirdCell = trim($row[3] ?? '');

            // Vérifier si c'est une ligne de mois/titre
            $isSkip = false;
            foreach ($this->skipPatterns as $pattern) {
                if (preg_match($pattern, $firstCell) || preg_match($pattern, $secondCell)) {
                    $isSkip = true;
                    break;
                }
            }

            $isNumber = is_numeric($firstCell) && $firstCell > 0;
            $isMonth = preg_match('/^(JANVIER|FEVRIER|MARS|AVRIL|MAI|JUIN|JUILLET|AOUT|SEPTEMBRE|OCTOBRE|NOVEMBRE|DECEMBRE)/i', $firstCell);
            $hasCode = preg_match('/^\d{6}$/', $thirdCell);

            if (!$isSkip && !$isMonth && !empty($secondCell) && ($isNumber || $hasCode)) {
                $validRows[] = $row;
            }
        }

        return $validRows;
    }

    /**
     * ÉTAPE 1 : Importer les utilisateurs avec gestion des IDs par année
     */
    protected function importUsers($rows, $dryRun, $idStart)
    {
        $totalRows = count($rows);
        $this->inserted = 0;
        $this->skipped = 0;
        $this->errors = [];

        // Réinitialiser les compteurs par année
        $this->yearCounter = [
            '2023' => 0,
            '2024' => 0,
            '2025' => 0,
            '2026' => 0,
        ];

        // Déterminer les IDs de départ pour chaque année
        $currentId = $idStart;
        foreach (['2023', '2024', '2025', '2026'] as $year) {
            $this->yearStartId[$year] = $currentId;
            // Réserver 500 IDs par année
            $currentId += 500;
        }

        $this->info("📌 Plages d'IDs par année :");
        foreach (['2023', '2024', '2025', '2026'] as $year) {
            $this->line("   - $year : ID " . $this->yearStartId[$year] . " à " . ($this->yearStartId[$year] + 500));
        }
        $this->newLine();

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $index => $row) {
            try {
                $data = $this->extractUserData($row, $index);
                
                // Vérifier si le nom est vide
                if (empty($data['name'])) {
                    $this->skipped++;
                    $bar->advance();
                    continue;
                }

                // Vérifier si le code est valide
                if (empty($data['code']) || !preg_match('/^\d{6}$/', $data['code'])) {
                    $this->skipped++;
                    $bar->advance();
                    continue;
                }

                // Vérifier si l'utilisateur existe déjà
                $existingUser = User::where('sponsor_id', $data['code'])->first();
                if ($existingUser) {
                    $this->skipped++;
                    $this->userCodes[$data['code']] = $existingUser->id;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    // Déterminer l'année et l'ID
                    $year = $this->determineYear($data['date']);
                    $userId = $this->getNextIdForYear($year);
                    
                    $user = $this->createUser($data, $userId);
                    if ($user) {
                        $this->inserted++;
                        $this->userCodes[$data['code']] = $user->id;
                        $this->yearCounter[$year]++;
                        
                        if ($this->inserted % 50 === 0) {
                            $this->newLine();
                            $this->line("✅ " . number_format($this->inserted) . " utilisateurs importés...");
                            $this->line("   📊 Répartition : 2023: {$this->yearCounter['2023']} | 2024: {$this->yearCounter['2024']} | 2025: {$this->yearCounter['2025']} | 2026: {$this->yearCounter['2026']}");
                        }
                    }
                } else {
                    $this->inserted++;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    /**
     * Détermine l'année à partir de la date
     */
    protected function determineYear($date)
    {
        if (empty($date)) return '2023';
        
        $year = date('Y', strtotime($date));
        
        if ($year >= 2026) return '2026';
        if ($year >= 2025) return '2025';
        if ($year >= 2024) return '2024';
        return '2023';
    }

    /**
     * Récupère le prochain ID disponible pour une année
     */
    protected function getNextIdForYear($year)
    {
        static $yearCounters = [
            '2023' => 0,
            '2024' => 0,
            '2025' => 0,
            '2026' => 0,
        ];

        $startId = $this->yearStartId[$year] ?? 3;
        $counter = $yearCounters[$year] ?? 0;
        $yearCounters[$year] = $counter + 1;
        
        return $startId + $counter;
    }

    /**
     * ÉTAPE 2 : Mettre à jour les parrains
     */
    protected function updateParrains($rows, $dryRun)
    {
        $this->parrainUpdated = 0;
        $this->parrainNotFound = 0;
        $this->errors = [];

        // Charger tous les codes utilisateur existants
        $allUsers = User::where('id', '>', 1)->get(['id', 'sponsor_id']);
        $userCodeMap = [];
        foreach ($allUsers as $user) {
            if ($user->sponsor_id) {
                $userCodeMap[$user->sponsor_id] = $user->id;
            }
        }

        $totalRows = count($rows);
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $index => $row) {
            try {
                $data = $this->extractUserData($row, $index);
                
                if (empty($data['name']) || empty($data['parrain_code'])) {
                    $bar->advance();
                    continue;
                }

                if (!preg_match('/^\d{6}$/', $data['parrain_code'])) {
                    $bar->advance();
                    continue;
                }

                $userId = $userCodeMap[$data['code']] ?? null;
                if (!$userId) {
                    $bar->advance();
                    continue;
                }

                $user = User::find($userId);
                if (!$user) {
                    $bar->advance();
                    continue;
                }

                if ($user->parrain_id !== null) {
                    $parrain = User::find($user->parrain_id);
                    if ($parrain && $parrain->sponsor_id === $data['parrain_code']) {
                        $bar->advance();
                        continue;
                    }
                }

                $parrainId = $userCodeMap[$data['parrain_code']] ?? null;
                
                if ($parrainId) {
                    if (!$dryRun) {
                        $user->parrain_id = $parrainId;
                        $user->save();
                        $this->parrainUpdated++;
                        
                        if ($this->parrainUpdated % 50 === 0) {
                            $this->newLine();
                            $this->line("🔄 " . number_format($this->parrainUpdated) . " parrains mis à jour...");
                        }
                    }
                    $this->parrainUpdated++;
                } else {
                    $this->parrainNotFound++;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Erreur mise à jour ligne " . ($index + 1) . ": " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    /**
     * Crée un utilisateur avec un ID spécifique
     */
    protected function createUser($data, $userId)
    {
        $email = $this->generateUniqueEmail($data['name']);

        $userData = [
            'id' => $userId,
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'],
            'sponsor_id' => $data['code'],
            'parrain_id' => null,
            'rank' => 'Distributeur',
            'rank_id' => 1,
            'rank_level' => 1,
            'country' => 'R.D.Congo',
            'city' => 'Goma',
            'is_active' => true,
            'user_type' => 'member',
            'kyc_status' => 'not_submitted',
            'password' => Hash::make('password123'),
            'created_at' => $data['date'] ?: now(),
            'updated_at' => now(),
            'last_login_at' => $data['date'] ?: null,
            'last_rank_update' => $data['date'] ?: now()->toDateString(),
        ];

        return User::create($userData);
    }

    /**
     * Extrait les données d'une ligne
     */
    protected function extractUserData($row, $index)
    {
        return [
            'numero' => trim($row[0] ?? ''),
            'date' => $this->parseDate($row[1] ?? ''),
            'name' => $this->cleanName($row[2] ?? ''),
            'code' => trim($row[3] ?? ''),
            'phone' => $this->cleanPhone($row[4] ?? ''),
            'sponsor_name' => $this->cleanName($row[5] ?? ''),
            'parrain_code' => trim($row[6] ?? ''),
        ];
    }

    /**
     * Génère un email unique
     */
    protected function generateUniqueEmail($name)
    {
        $base = strtolower(trim($name));
        $base = $this->removeAccents($base);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        if (strlen($base) > 30) {
            $base = substr($base, 0, 30);
        }
        
        if (empty($base)) {
            $base = 'user' . rand(1000, 9999);
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

    /**
     * Supprime les accents
     */
    protected function removeAccents($string)
    {
        $accents = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'Ç' => 'C', 'ç' => 'c',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ñ' => 'N', 'ñ' => 'n',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ý' => 'Y', 'ý' => 'y', 'ÿ' => 'y',
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z',
        ];
        return strtr($string, $accents);
    }

    /**
     * Nettoie le nom
     */
    protected function cleanName($name)
    {
        if (empty($name)) return '';
        return trim(preg_replace('/\s+/', ' ', trim($name)));
    }

    /**
     * Nettoie le téléphone
     */
    protected function cleanPhone($phone)
    {
        if (empty($phone)) return null;
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }

    /**
     * Parse une date Excel ou texte
     */
    protected function parseDate($date)
    {
        if (empty($date)) return null;

        // Si c'est une date Excel (nombre)
        if (is_numeric($date)) {
            try {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $dateStr = trim($date);
        
        // Si c'est juste une année (ex: "2023")
        if (preg_match('/^\d{4}$/', $dateStr)) {
            return $dateStr . '-01-01';
        }
        
        // Essayer différents formats de date
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            $dateObj = \DateTime::createFromFormat($format, $dateStr);
            if ($dateObj !== false) {
                return $dateObj->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Affiche le résumé de l'étape 1
     */
    protected function showStep1Summary($dryRun)
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║              📊 ÉTAPE 1 - RÉSUMÉ IMPORTATION               ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("✅ Utilisateurs importés : " . number_format($this->inserted));
        $this->line("⏭️  Lignes ignorées (doublons/vides) : " . number_format($this->skipped));
        $this->line("❌ Erreurs : " . number_format(count($this->errors)));
        
        $this->newLine();
        $this->line("📊 Répartition par année :");
        foreach (['2023', '2024', '2025', '2026'] as $year) {
            $this->line("   - $year : " . number_format($this->yearCounter[$year] ?? 0) . " utilisateurs");
        }

        if ($dryRun) {
            $this->warn("⚠️  Mode DRY-RUN - Aucune donnée modifiée");
        }
    }

    /**
     * Affiche le résumé de l'étape 2
     */
    protected function showStep2Summary($dryRun)
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║              📊 ÉTAPE 2 - RÉSUMÉ PARRAINAGE                ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("🔄 Parrains mis à jour : " . number_format($this->parrainUpdated));
        $this->line("⚠️  Parrains non trouvés : " . number_format($this->parrainNotFound));
        $this->line("❌ Erreurs : " . number_format(count($this->errors)));

        if ($dryRun) {
            $this->warn("⚠️  Mode DRY-RUN - Aucune donnée modifiée");
        }
    }

    /**
     * Affiche le résumé global
     */
    protected function showGlobalSummary($dryRun)
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                  📊 RÉSUMÉ GLOBAL                         ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📌 Total utilisateurs importés : " . number_format($this->inserted));
        $this->line("🔄 Total parrains mis à jour : " . number_format($this->parrainUpdated));
        $this->line("⚠️  Parrains non trouvés : " . number_format($this->parrainNotFound));
        $this->line("⏭️  Lignes ignorées : " . number_format($this->skipped));
        $this->line("❌ Erreurs totales : " . number_format(count($this->errors)));

        if ($dryRun) {
            $this->newLine();
            $this->warn("⚠️  Mode DRY-RUN - Aucune donnée modifiée");
        }

        if ($this->inserted > 0 && !$dryRun) {
            $this->newLine();
            $this->line("📌 Derniers utilisateurs importés :");
            $users = User::orderBy('id', 'desc')->limit(10)->get();
            foreach ($users as $user) {
                $parrainName = $user->parrain ? $user->parrain->name : 'Aucun';
                $this->line("  - ID: {$user->id} | NOM: {$user->name} | ANNÉE: " . date('Y', strtotime($user->created_at)) . " | PARRAIN: {$parrainName}");
            }
        }

        if (!empty($this->errors) && count($this->errors) <= 15) {
            $this->newLine();
            $this->line("📝 Détails des erreurs :");
            foreach ($this->errors as $error) {
                $this->error("  - $error");
            }
        } elseif (!empty($this->errors)) {
            $this->newLine();
            $this->warn("⚠️ " . count($this->errors) . " erreurs - Vérifiez les logs pour plus de détails");
        }
    }
}