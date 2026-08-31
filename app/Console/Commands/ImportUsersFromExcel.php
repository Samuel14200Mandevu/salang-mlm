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
                            {--step2 : Mettre à jour uniquement les parrains}';
    
    protected $description = 'Importe les utilisateurs depuis un fichier Excel avec gestion du parrainage en 2 étapes';

    protected $inserted = 0;
    protected $errors = [];
    protected $skipped = 0;
    protected $parrainUpdated = 0;
    protected $parrainNotFound = 0;
    protected $userCodes = [];

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
        $defaultPath = storage_path('app/imports/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx');
        $filePath = $this->argument('file') ?? $defaultPath;
        $dryRun = $this->option('dry-run');
        $skipHeader = $this->option('skip-header');
        $step1 = $this->option('step1');
        $step2 = $this->option('step2');

        if ($dryRun) {
            $this->warn('⚠️  MODE DRY-RUN - Aucune modification');
            $this->newLine();
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé : $filePath");
            $this->info("📂 Vérifiez que le fichier existe dans : " . dirname($filePath));
            return 1;
        }

        try {
            // === CHARGER LE FICHIER ===
            $this->info("⏳ Chargement du fichier Excel...");
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // === AFFICHER L'APERÇU ===
            $this->line("📋 Aperçu des données :");
            $this->line(str_repeat('-', 80));
            for ($i = 0; $i < min(6, count($rows)); $i++) {
                $row = $rows[$i];
                $this->line(sprintf(
                    "Ligne %d: Col0='%s' | Col1='%s' | Col2='%s' | Col3='%s' | Col4='%s'",
                    $i+1,
                    $row[0] ?? '', $row[1] ?? '', $row[2] ?? '', $row[3] ?? '', $row[4] ?? ''
                ));
            }
            $this->line(str_repeat('-', 80));
            $this->newLine();

            // === EXTRAIRE LES UTILISATEURS (format 2 lignes) ===
            $this->info("🔍 Extraction des utilisateurs...");
            $users = $this->extractUsersFromRows($rows, $skipHeader);
            $rowsToProcess = count($users);

            $this->info("✅ Utilisateurs trouvés : " . number_format($rowsToProcess));
            $this->newLine();

            if ($rowsToProcess === 0) {
                $this->error("❌ Aucun utilisateur trouvé dans le fichier");
                return 1;
            }

            // Afficher les premiers utilisateurs extraits
            $this->line("📋 Aperçu des utilisateurs extraits :");
            $this->line(str_repeat('-', 80));
            for ($i = 0; $i < min(5, count($users)); $i++) {
                $u = $users[$i];
                $this->line(sprintf(
                    "  %d. %s | CODE: %s | Tél: %s | Parrain: %s (%s)",
                    $i+1,
                    $u['name'],
                    $u['code'] ?? 'NULL',
                    $u['phone'] ?? 'N/A',
                    $u['sponsor_name'] ?? 'Aucun',
                    $u['sponsor_code'] ?? 'Aucun'
                ));
            }
            if (count($users) > 5) {
                $this->line("  ... et " . (count($users) - 5) . " autres");
            }
            $this->line(str_repeat('-', 80));
            $this->newLine();

            // === ÉTAPE 1 : Importer les utilisateurs ===
            if ($step1 || (!$step1 && !$step2)) {
                $this->info("🚀 ÉTAPE 1 : Importation des utilisateurs (sans parrain)...");
                $this->newLine();
                $this->importUsers($users, $dryRun);
                $this->showStep1Summary($dryRun);
            }

            // === ÉTAPE 2 : Mettre à jour les parrains ===
            if ($step2 || (!$step1 && !$step2)) {
                if ($this->inserted > 0 || $step2) {
                    $this->newLine();
                    $this->info("🚀 ÉTAPE 2 : Mise à jour des relations de parrainage...");
                    $this->updateParrains($users, $dryRun);
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
            return 1;
        }
    }

    /**
     * Extrait les utilisateurs du format 2 lignes
     */
    protected function extractUsersFromRows($rows, $skipHeader)
    {
        $users = [];
        $startIndex = $skipHeader ? 1 : 0;

        for ($i = $startIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Colonnes selon votre fichier :
            // Col 0: N° (vide pour les sponsors)
            // Col 1: NOM (ou "Sponsor XXX")
            // Col 2: CODE (ou code du sponsor)
            // Col 3: ADRESSE
            // Col 4: TELEPHONE
            
            $firstCell = trim($row[0] ?? '');
            $nameCell = trim($row[1] ?? '');
            $codeCell = trim($row[2] ?? '');
            $addressCell = trim($row[3] ?? '');
            $phoneCell = trim($row[4] ?? '');
            
            // Ignorer les lignes vides ou de mois
            if (empty($nameCell) && empty($codeCell)) {
                continue;
            }
            
            // Vérifier si c'est une ligne de mois/titre
            if (preg_match('/^(JANVIER|FEVRIER|MARS|AVRIL|MAI|JUIN|JUILLET|AOUT|SEPTEMBRE|OCTOBRE|NOVEMBRE|DECEMBRE)/i', $firstCell)) {
                continue;
            }

            // === CAS 1 : C'est une ligne d'utilisateur (commence par un N°) ===
            if (is_numeric($firstCell) && !empty($nameCell)) {
                // Nettoyer le code (enlever les espaces) ou NULL
                $code = !empty($codeCell) ? $this->cleanCode($codeCell) : null;
                
                $userData = [
                    'numero' => $firstCell,
                    'name' => $this->cleanName($nameCell),
                    'code' => $code,
                    'address' => $addressCell,
                    'phone' => $this->cleanPhone($phoneCell),
                    'sponsor_name' => null,
                    'sponsor_code' => null,
                ];

                // Vérifier si la ligne suivante est le sponsor
                if ($i + 1 < count($rows)) {
                    $nextRow = $rows[$i + 1];
                    $nextName = trim($nextRow[1] ?? '');
                    $nextCode = trim($nextRow[2] ?? '');
                    $nextPhone = trim($nextRow[4] ?? '');
                    
                    // La ligne suivante est un sponsor si elle contient "Sponsor" ou un code
                    $isSponsor = (strpos(strtolower($nextName), 'sponsor') !== false || 
                                  !empty($this->cleanCode($nextCode)));
                    
                    if ($isSponsor && !empty($nextName)) {
                        // Extraire le nom du sponsor (enlever le mot "Sponsor")
                        $sponsorName = str_ireplace('sponsor', '', $nextName);
                        $sponsorName = str_ireplace('Sponsor', '', $sponsorName);
                        $sponsorName = trim($sponsorName);
                        
                        $userData['sponsor_name'] = $this->cleanName($sponsorName);
                        $userData['sponsor_code'] = !empty($nextCode) ? $this->cleanCode($nextCode) : null;
                        
                        $i++; // Sauter la ligne du sponsor
                    }
                }

                // Garder TOUS les utilisateurs, même sans code
                if (!empty($userData['name'])) {
                    $users[] = $userData;
                }
            }
            // === CAS 2 : C'est une ligne de sponsor sans utilisateur ===
            else if (strpos(strtolower($nameCell), 'sponsor') !== false && empty($firstCell)) {
                continue;
            }
            // === CAS 3 : C'est une ligne d'utilisateur sans N° ===
            else if (!empty($nameCell) && !empty($codeCell)) {
                $code = $this->cleanCode($codeCell);
                
                $userData = [
                    'numero' => $firstCell,
                    'name' => $this->cleanName($nameCell),
                    'code' => $code,
                    'address' => $addressCell,
                    'phone' => $this->cleanPhone($phoneCell),
                    'sponsor_name' => null,
                    'sponsor_code' => null,
                ];

                // Vérifier si la ligne suivante est le sponsor
                if ($i + 1 < count($rows)) {
                    $nextRow = $rows[$i + 1];
                    $nextName = trim($nextRow[1] ?? '');
                    $nextCode = trim($nextRow[2] ?? '');
                    
                    if (strpos(strtolower($nextName), 'sponsor') !== false || !empty($this->cleanCode($nextCode))) {
                        $sponsorName = str_ireplace('sponsor', '', $nextName);
                        $sponsorName = str_ireplace('Sponsor', '', $sponsorName);
                        $sponsorName = trim($sponsorName);
                        
                        $userData['sponsor_name'] = $this->cleanName($sponsorName);
                        $userData['sponsor_code'] = !empty($nextCode) ? $this->cleanCode($nextCode) : null;
                        
                        $i++; // Sauter la ligne du sponsor
                    }
                }

                if (!empty($userData['name'])) {
                    $users[] = $userData;
                }
            }
            // === CAS 4 : Ligne avec nom mais sans numéro ni code ===
            else if (!empty($nameCell) && empty($firstCell) && empty($codeCell)) {
                $userData = [
                    'numero' => $firstCell,
                    'name' => $this->cleanName($nameCell),
                    'code' => null,
                    'address' => $addressCell,
                    'phone' => $this->cleanPhone($phoneCell),
                    'sponsor_name' => null,
                    'sponsor_code' => null,
                ];

                if (!empty($userData['name'])) {
                    $users[] = $userData;
                }
            }
        }

        return $users;
    }

    /**
     * Nettoie le code (enlève les espaces, les tirets)
     */
    protected function cleanCode($code)
    {
        if (empty($code)) return null;
        $code = trim($code);
        $code = str_replace(' ', '', $code);
        $code = str_replace('-', '', $code);
        $code = str_replace('—', '', $code);
        return $code;
    }

    /**
     * ÉTAPE 1 : Importer les utilisateurs
     */
    protected function importUsers($users, $dryRun)
    {
        $totalRows = count($users);
        $this->inserted = 0;
        $this->skipped = 0;
        $this->errors = [];

        // === ID DE DÉPART FORCÉ À 1453 ===
        $startId = 1453;

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($users as $index => $data) {
            try {
                // Vérifier si l'utilisateur existe déjà (par nom ou code)
                $existingUser = null;
                if (!empty($data['code'])) {
                    $existingUser = User::where('sponsor_id', $data['code'])->first();
                }
                
                if ($existingUser) {
                    $this->skipped++;
                    $this->userCodes[$data['code']] = $existingUser->id;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    $userId = $startId + $this->inserted;
                    $user = $this->createUser($data, $userId);
                    if ($user) {
                        $this->inserted++;
                        if (!empty($data['code'])) {
                            $this->userCodes[$data['code']] = $user->id;
                        }
                    }
                } else {
                    $this->inserted++;
                }

            } catch (\Exception $e) {
                $this->errors[] = "Ligne " . ($index + 1) . " - {$data['name']}: " . $e->getMessage();
            }

            $bar->advance();
            
            // Afficher la progression
            if (($index + 1) % 50 === 0) {
                $this->newLine();
                $this->line("📌 Progression : " . ($index + 1) . "/" . $totalRows . " utilisateurs traités");
            }
        }

        $bar->finish();
        $this->newLine(2);
    }

    /**
     * ÉTAPE 2 : Mettre à jour les parrains
     */
    protected function updateParrains($users, $dryRun)
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

        $totalRows = count($users);
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($users as $index => $data) {
            try {
                // Vérifier si l'utilisateur a un sponsor
                if (empty($data['sponsor_code'])) {
                    $bar->advance();
                    continue;
                }

                // Trouver l'ID de l'utilisateur
                $userId = null;
                if (!empty($data['code']) && isset($userCodeMap[$data['code']])) {
                    $userId = $userCodeMap[$data['code']];
                }
                
                if (!$userId) {
                    $bar->advance();
                    continue;
                }

                $user = User::find($userId);
                if (!$user) {
                    $bar->advance();
                    continue;
                }

                // Si l'utilisateur a déjà un parrain, vérifier si c'est le bon
                if ($user->parrain_id !== null) {
                    $existingParrain = User::find($user->parrain_id);
                    if ($existingParrain && $existingParrain->sponsor_id === $data['sponsor_code']) {
                        $bar->advance();
                        continue;
                    }
                }

                // Trouver le parrain par son code
                $parrainId = isset($userCodeMap[$data['sponsor_code']]) ? $userCodeMap[$data['sponsor_code']] : null;
                
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
                    if ($this->parrainNotFound <= 20) {
                        $this->warn("⚠️  Parrain non trouvé pour {$data['name']} (CODE: {$data['sponsor_code']})");
                    }
                }

            } catch (\Exception $e) {
                $this->errors[] = "Erreur mise à jour ligne " . ($index + 1) . ": " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        if ($this->parrainNotFound > 20) {
            $this->warn("⚠️ " . ($this->parrainNotFound - 20) . " autres parrains non trouvés...");
        }
    }

    /**
     * Crée un utilisateur
     */
    protected function createUser($data, $userId)
    {
        // Générer un email unique
        $email = $this->generateUniqueEmail($data['name']);

        $userData = [
            'id' => $userId,
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'sponsor_id' => $data['code'] ?? null,
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
            'created_at' => now(),
            'updated_at' => now(),
            'last_rank_update' => now()->toDateString(),
        ];

        return User::create($userData);
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
        $name = str_ireplace('sponsor', '', $name);
        $name = str_ireplace('Sponsor', '', $name);
        $name = str_ireplace('SPONSOR', '', $name);
        $name = str_ireplace(' SPR ', ' ', $name);
        return trim(preg_replace('/\s+/', ' ', trim($name)));
    }

    /**
     * Nettoie le téléphone
     */
    protected function cleanPhone($phone)
    {
        if (empty($phone)) return null;
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return $phone;
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
                $this->line("  - ID: {$user->id} | NOM: {$user->name} | PARRAIN: {$parrainName}");
            }
        }
    }
}