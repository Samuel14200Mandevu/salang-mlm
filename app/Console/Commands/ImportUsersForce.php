<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ImportUsersForce extends Command
{
    protected $signature = 'users:import-force {file} {--dry-run}';
    protected $description = 'Force l\'importation des utilisateurs en ignorant les erreurs';

    public function handle()
    {
        $filePath = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé: $filePath");
            return 1;
        }

        $this->info("📊 Chargement du fichier...");
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $total = 0;
        $imported = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $index => $row) {
            if ($index < 1) continue;
            
            $total++;
            
            $name = trim($row[2] ?? '');
            $code = trim($row[3] ?? '');
            $phone = trim($row[4] ?? '');
            $date = $this->parseDate($row[1] ?? '');
            
            // Ignorer les lignes sans nom ou sans code
            if (empty($name) || empty($code)) {
                $bar->advance();
                continue;
            }
            
            // Vérifier si l'utilisateur existe déjà
            $existing = User::where('sponsor_id', $code)->first();
            if ($existing) {
                $bar->advance();
                continue;
            }
            
            try {
                if (!$dryRun) {
                    $email = $this->generateEmail($name);
                    $userId = $this->getNextId();
                    
                    User::create([
                        'id' => $userId,
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'sponsor_id' => $code,
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
                        'created_at' => $date ?: now(),
                        'updated_at' => now(),
                    ]);
                    
                    $imported++;
                }
                $imported++;
            } catch (\Exception $e) {
                $errors++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        
        $this->newLine();
        $this->line("✅ Importés: $imported");
        $this->line("❌ Erreurs: $errors");
        $this->line("📊 Total: $total");
    }

    private function parseDate($date)
    {
        if (empty($date)) return null;
        if (is_numeric($date)) {
            return Date::excelToDateTimeObject($date)->format('Y-m-d');
        }
        $year = intval($date);
        if ($year > 2000 && $year < 2030) {
            return $year . '-01-01';
        }
        return null;
    }

    private function generateEmail($name)
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        if (strlen($base) > 20) $base = substr($base, 0, 20);
        if (empty($base)) $base = 'user' . rand(1000, 9999);
        return $base . '@salanggroup.com';
    }

    private function getNextId()
    {
        static $counter = null;
        if ($counter === null) {
            $maxId = User::max('id') ?? 2;
            $counter = $maxId + 1;
        }
        return $counter++;
    }
}