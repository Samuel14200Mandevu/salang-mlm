<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DiagnoseImport extends Command
{
    protected $signature = 'users:diagnose {file}';
    protected $description = 'Diagnostique les problèmes d\'importation Excel';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("Fichier non trouvé: $filePath");
            return 1;
        }

        $this->info("📊 Analyse du fichier...");
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $total = 0;
        $valid = 0;
        $invalid = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index < 1) continue; // Skip header
            
            $name = trim($row[2] ?? '');
            $code = trim($row[3] ?? '');
            $phone = trim($row[4] ?? '');
            $parrainCode = trim($row[6] ?? '');
            
            $total++;
            
            // Vérifier les problèmes
            $issues = [];
            
            if (empty($name)) {
                $issues[] = "Nom vide";
            }
            
            if (empty($code) || !preg_match('/^\d{6}$/', $code)) {
                $issues[] = "Code invalide: '$code'";
            }
            
            if (!empty($phone) && !preg_match('/^[0-9+ ]+$/', $phone)) {
                $issues[] = "Téléphone invalide: '$phone'";
            }
            
            if (!empty($parrainCode) && !preg_match('/^\d{6}$/', $parrainCode)) {
                $issues[] = "Code parrain invalide: '$parrainCode'";
            }
            
            if (empty($issues)) {
                $valid++;
            } else {
                $invalid++;
                $errors[] = "Ligne " . ($index + 1) . ": " . implode(", ", $issues) . " | $name";
            }
        }

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║                 📊 RAPPORT DE DIAGNOSTIC                  ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📌 Total lignes analysées : $total");
        $this->line("✅ Lignes valides : $valid");
        $this->line("❌ Lignes invalides : $invalid");
        $this->newLine();

        if (!empty($errors)) {
            $this->line("📝 Détails des 50 premières erreurs :");
            foreach (array_slice($errors, 0, 50) as $error) {
                $this->error("  - $error");
            }
            if (count($errors) > 50) {
                $this->line("  ... et " . (count($errors) - 50) . " autres erreurs");
            }
        }

        return 0;
    }
}