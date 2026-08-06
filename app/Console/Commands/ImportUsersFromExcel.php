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
                            {--chunk=100 : Nombre d\'enregistrements par lot}
                            {--skip-header : Ignorer la première ligne (en-tête)}
                            {--force : Forcer l\'importation en production}';
    
    protected $description = 'Importe les utilisateurs depuis un fichier Excel (ADHESION 2023-2024)';

    protected $columnMapping = [
        'numero' => 0,
        'date' => 1,
        'name' => 2,
        'sponsor_id' => 3,
        'phone' => 4,
        'sponsor_name' => 5,
        'parrain_code' => 6,
    ];

    // Stocker les codes pour la mise à jour des parrains
    protected $userCodes = [];

    public function handle()
    {
        // === VÉRIFICATION DE L'ENVIRONNEMENT ===
        $currentEnv = app()->environment();
        $dbName = config('database.connections.mysql.database');
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
        $defaultPath = '/home/samuel-mandevu/Téléchargements/ADHESION 2023-2024_095756.xlsx';
        $filePath = $this->argument('file') ?? $defaultPath;
        $dryRun = $this->option('dry-run');
        $skipHeader = $this->option('skip-header');

        if ($dryRun) {
            $this->warn('⚠️  MODE DRY-RUN - Aucune modification');
            $this->newLine();
        }

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé : $filePath");
            return 1;
        }

        try {
            // Charger le fichier
            $this->info("⏳ Chargement du fichier Excel...");
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $totalRows = count($rows);
            $startRow = $skipHeader ? 1 : 0;
            $rowsToProcess = $totalRows - $startRow;

            $this->info("✅ Fichier chargé : " . number_format($rowsToProcess) . " utilisateurs");
            $this->newLine();

            if (!$this->confirm("Continuer l'importation ?", true)) {
                return 0;
            }

            // === ÉTAPE 1 : Importer les utilisateurs SANS parrain ===
            $this->info("🚀 ÉTAPE 1 : Importation des utilisateurs...");
            $result = $this->importUsersWithoutParrain($rows, $startRow, $dryRun);

            if ($dryRun) {
                $this->showSummary($result, true);
                return 0;
            }

            // === ÉTAPE 2 : Mettre à jour les parrains ===
            if ($result['inserted'] > 0) {
                $this->newLine();
                $this->info("🚀 ÉTAPE 2 : Mise à jour des relations de parrainage...");
                $this->updateParrains($rows, $startRow);
            }

            // === RÉSUMÉ ===
            $this->showSummary($result, false);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            return 1;
        }
    }

    /**
     * ÉTAPE 1 : Importer les utilisateurs sans les parrains
     */
    protected function importUsersWithoutParrain($rows, $startRow, $dryRun)
    {
        $inserted = 0;
        $errors = [];
        $skipped = 0;
        $totalRows = count($rows) - $startRow;

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $index => $row) {
            if ($index < $startRow) continue;

            try {
                $data = $this->extractUserData($row, $index);
                
                if (empty($data['name'])) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    // Insérer SANS parrain_id (null)
                    $user = $this->createUserWithoutParrain($data);
                    if ($user) {
                        $inserted++;
                        // Stocker la correspondance code -> id
                        $this->userCodes[$data['sponsor_id']] = $user->id;
                    }
                } else {
                    $this->line("\n[DRY-RUN] " . $data['name'] . " -> " . $data['sponsor_id']);
                    $inserted++;
                }

            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($index + 1) . ": " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        return [
            'total' => $totalRows,
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * ÉTAPE 2 : Mettre à jour les parrains
     */
    protected function updateParrains($rows, $startRow)
    {
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index < $startRow) continue;

            try {
                $data = $this->extractUserData($row, $index);
                
                if (empty($data['name']) || empty($data['parrain_code'])) {
                    continue;
                }

                // Trouver l'utilisateur par son CODE
                $user = User::where('sponsor_id', $data['sponsor_id'])->first();
                
                if (!$user) {
                    continue;
                }

                // Trouver le parrain par son CODE
                $parrain = User::where('sponsor_id', $data['parrain_code'])->first();
                
                if ($parrain) {
                    $user->parrain_id = $parrain->id;
                    $user->save();
                    $updated++;
                } else {
                    // Si le parrain n'existe pas encore
                    $errors[] = "Parrain non trouvé pour " . $data['name'] . " (CODE: " . $data['parrain_code'] . ")";
                }

            } catch (\Exception $e) {
                $errors[] = "Erreur mise à jour ligne " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        $this->line("✅ Parrains mis à jour : $updated");
        if (!empty($errors)) {
            $this->warn("⚠️ " . count($errors) . " parrains non trouvés");
        }
    }

    /**
     * Crée un utilisateur SANS parrain
     */
    protected function createUserWithoutParrain($data)
    {
        $email = $this->generateUniqueEmail($data['name']);

        $userData = [
            'name' => $data['name'],
            'email' => $email,
            'phone' => $data['phone'],
            'sponsor_id' => $data['sponsor_id'],
            'parrain_id' => null,  // IMPORTANT : mis à null
            'rank' => 'Distributor',
            'rank_level' => 1,
            'country' => 'R.D.Congo',
            'city' => 'Goma',
            'is_active' => true,
            'user_type' => 'member',
            'kyc_status' => 'not_submitted',
            'password' => Hash::make('password123'),
            'created_at' => $data['date'] ?: now(),
            'updated_at' => now(),
            'last_rank_update' => $data['date'] ?: now()->toDateString(),
        ];

        if ($data['sponsor_id'] === '500000') {
            $userData['id'] = 500000;
        }

        return User::create($userData);
    }

    /**
     * Extrait les données
     */
    protected function extractUserData($row, $index)
    {
        return [
            'numero' => trim($row[0] ?? ''),
            'date' => $this->parseDate($row[1] ?? ''),
            'name' => $this->cleanName($row[2] ?? ''),
            'sponsor_id' => trim($row[3] ?? ''),
            'phone' => $this->cleanPhone($row[4] ?? ''),
            'sponsor_name' => $this->cleanName($row[5] ?? ''),
            'parrain_code' => trim($row[6] ?? ''),
        ];
    }

    /**
     * Génère un email unique SANS points
     */
    protected function generateUniqueEmail($name)
    {
        $base = strtolower(trim($name));
        $base = $this->removeAccents($base);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        if (strlen($base) > 50) {
            $base = substr($base, 0, 50);
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
     * Parse une date
     */
    protected function parseDate($date)
    {
        if (empty($date)) return null;

        if (is_numeric($date)) {
            try {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $dateStr = trim($date);
        
        if (preg_match('/^\d{4}$/', $dateStr)) {
            return $dateStr . '-01-01';
        }
        
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
     * Affiche le résumé
     */
    protected function showSummary($result, $dryRun)
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                  📊 RÉSUMÉ DE L\'IMPORTATION                 ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📌 Total lignes traitées : " . number_format($result['total']));
        $this->line("✅ Utilisateurs importés : " . number_format($result['inserted']));
        $this->line("⏭️  Lignes ignorées : " . number_format($result['skipped']));
        $this->line("❌ Erreurs : " . number_format(count($result['errors'])));

        if ($dryRun) {
            $this->newLine();
            $this->warn("⚠️  Mode DRY-RUN - Aucune donnée modifiée");
        }

        if (!empty($result['errors']) && count($result['errors']) <= 10) {
            $this->newLine();
            $this->line("📝 Détails des erreurs :");
            foreach ($result['errors'] as $error) {
                $this->error("  - $error");
            }
        }
    }
}