<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CleanExcelData extends Command
{
    protected $signature = 'excel:clean {file} {--output=}';
    protected $description = 'Nettoie les données Excel pour l\'importation';

    public function handle()
    {
        $filePath = $this->argument('file');
        $outputPath = $this->option('output') ?? str_replace('.xlsx', '_clean.xlsx', $filePath);

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé: $filePath");
            return 1;
        }

        $this->info("📊 Nettoyage du fichier...");
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $total = 0;
        $cleaned = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index < 1) continue; // Skip header
            
            $total++;
            $modified = false;
            $rowIndex = $index + 1;
            
            // Nettoyer le téléphone (colonne 5 - index 4)
            if (isset($row[4])) {
                $phone = trim($row[4]);
                // Supprimer les virgules
                $phone = str_replace(',', ' ', $phone);
                // Supprimer les tirets
                $phone = str_replace('-', '', $phone);
                // Supprimer les lettres
                $phone = preg_replace('/[^0-9+ ]/', '', $phone);
                // Nettoyer les espaces multiples
                $phone = preg_replace('/\s+/', ' ', $phone);
                $phone = trim($phone);
                
                if ($phone !== trim($row[4])) {
                    $worksheet->setCellValue('E' . $rowIndex, $phone);
                    $modified = true;
                }
            }
            
            // Nettoyer le code (colonne 4 - index 3)
            if (isset($row[3])) {
                $code = trim($row[3]);
                // Si le code a 5 chiffres, ajouter un 0
                if (preg_match('/^\d{5}$/', $code)) {
                    $code = '0' . $code;
                    $worksheet->setCellValue('D' . $rowIndex, $code);
                    $modified = true;
                }
                // Si le code contient des lettres ou n'a pas 6 chiffres
                if (!preg_match('/^\d{6}$/', $code) && !empty($code)) {
                    $errors[] = "Ligne " . ($rowIndex) . ": Code invalide '$code'";
                    $worksheet->setCellValue('D' . $rowIndex, '');
                    $modified = true;
                }
            }
            
            // Nettoyer le code parrain (colonne 7 - index 6)
            if (isset($row[6])) {
                $parrainCode = trim($row[6]);
                // Si c'est un tiret, le mettre vide
                if ($parrainCode === '-') {
                    $worksheet->setCellValue('G' . $rowIndex, '');
                    $modified = true;
                }
                // Si le code contient des lettres ou n'a pas 6 chiffres
                if (!empty($parrainCode) && !preg_match('/^\d{6}$/', $parrainCode)) {
                    $errors[] = "Ligne " . ($rowIndex) . ": Code parrain invalide '$parrainCode'";
                    $worksheet->setCellValue('G' . $rowIndex, '');
                    $modified = true;
                }
            }
            
            if ($modified) {
                $cleaned++;
            }
        }

        // Sauvegarder le fichier nettoyé
        $writer = new Xlsx($spreadsheet);
        $writer->save($outputPath);

        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║              📊 RÉSUMÉ DU NETTOYAGE                       ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->line("📌 Total lignes analysées : " . number_format($total));
        $this->line("✅ Lignes nettoyées : " . number_format($cleaned));
        $this->line("📁 Fichier sauvegardé : $outputPath");
        $this->newLine();

        if (!empty($errors)) {
            $this->line("⚠️ Problèmes détectés (" . count($errors) . ") :");
            foreach (array_slice($errors, 0, 20) as $error) {
                $this->warn("  - $error");
            }
            if (count($errors) > 20) {
                $this->warn("  ... et " . (count($errors) - 20) . " autres problèmes");
            }
        }

        return 0;
    }
}