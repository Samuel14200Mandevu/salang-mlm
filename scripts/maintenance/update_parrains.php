#!/usr/bin/env php
<?php

// ============================================================
// SCRIPT : METTRE À JOUR LES PARRAINS
// Fichier : update_parrains.php
// Date : 24/08/2026
// ============================================================

// Charger Laravel
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

// ============================================================
// CONFIGURATION
// ============================================================

// Chemin du fichier Excel
$filePath = storage_path('app/imports/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx');

// Si le fichier n'existe pas, proposer un autre chemin
if (!file_exists($filePath)) {
    // Essayer dans le répertoire courant
    $altPath = __DIR__ . '/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx';
    if (file_exists($altPath)) {
        $filePath = $altPath;
    } else {
        // Essayer dans Téléchargements
        $downloadPath = '/home/samuel-mandevu/Téléchargements/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx';
        if (file_exists($downloadPath)) {
            $filePath = $downloadPath;
        } else {
            die("❌ Fichier non trouvé !\n");
        }
    }
}

// ============================================================
// AFFICHAGE DE L'EN-TÊTE
// ============================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔄 MISE À JOUR DES PARRAINS DES UTILISATEURS           ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📂 Fichier Excel : " . basename($filePath) . "\n";
echo "📊 Base de données : " . config('database.connections.mysql.database') . "\n";
echo "🌍 Environnement : " . app()->environment() . "\n";
echo "\n";

// ============================================================
// CHARGER LE FICHIER EXCEL
// ============================================================

echo "⏳ Chargement du fichier Excel...\n";

try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
} catch (Exception $e) {
    die("❌ Erreur de chargement : " . $e->getMessage() . "\n");
}

echo "✅ Fichier chargé : " . count($rows) . " lignes\n\n";

// ============================================================
// EXTRAIRE LES RELATIONS UTILISATEUR → PARRAIN
// ============================================================

echo "🔍 Extraction des relations utilisateur → parrain...\n";

$relations = [];
$skipHeader = true;
$startIndex = $skipHeader ? 1 : 0;

for ($i = $startIndex; $i < count($rows); $i++) {
    $row = $rows[$i];
    
    $firstCell = trim($row[0] ?? '');
    $nameCell = trim($row[1] ?? '');
    $codeCell = trim($row[2] ?? '');
    $phoneCell = trim($row[4] ?? '');
    
    // Ignorer les lignes vides
    if (empty($nameCell) && empty($codeCell)) {
        continue;
    }
    
    // Ignorer les lignes de mois/titre
    if (preg_match('/^(JANVIER|FEVRIER|MARS|AVRIL|MAI|JUIN|JUILLET|AOUT|SEPTEMBRE|OCTOBRE|NOVEMBRE|DECEMBRE)/i', $firstCell)) {
        continue;
    }
    
    // Vérifier si c'est une ligne d'utilisateur
    if (is_numeric($firstCell) && !empty($nameCell)) {
        // Nettoyer le code
        $code = str_replace(' ', '', $codeCell);
        $code = str_replace('-', '', $code);
        $code = str_replace('—', '', $code);
        $code = str_replace('.', '', $code);
        
        // Vérifier si la ligne suivante est le sponsor
        $sponsorCode = null;
        $sponsorName = null;
        
        if ($i + 1 < count($rows)) {
            $nextRow = $rows[$i + 1];
            $nextName = trim($nextRow[1] ?? '');
            $nextCode = trim($nextRow[2] ?? '');
            
            // Nettoyer le code du sponsor
            $nextCodeClean = str_replace(' ', '', $nextCode);
            $nextCodeClean = str_replace('-', '', $nextCodeClean);
            $nextCodeClean = str_replace('—', '', $nextCodeClean);
            $nextCodeClean = str_replace('.', '', $nextCodeClean);
            
            // Vérifier si c'est un sponsor (contient "Sponsor" ou a un code valide)
            $isSponsor = (strpos(strtolower($nextName), 'sponsor') !== false || 
                          preg_match('/^\d{5,6}$/', $nextCodeClean));
            
            if ($isSponsor && !empty($nextName) && !empty($nextCodeClean)) {
                $sponsorName = str_ireplace('sponsor', '', $nextName);
                $sponsorName = str_ireplace('Sponsor', '', $sponsorName);
                $sponsorName = str_ireplace('SPONSOR', '', $sponsorName);
                $sponsorName = trim($sponsorName);
                $sponsorCode = $nextCodeClean;
            }
        }
        
        // Stocker la relation
        if (!empty($code) && !empty($sponsorCode)) {
            $relations[] = [
                'user_code' => $code,
                'user_name' => cleanName($nameCell),
                'sponsor_code' => $sponsorCode,
                'sponsor_name' => cleanName($sponsorName),
            ];
        }
    }
}

echo "✅ Relations extraites : " . count($relations) . "\n\n";

if (count($relations) === 0) {
    die("❌ Aucune relation trouvée dans le fichier\n");
}

// Afficher les 5 premières relations
echo "📋 Aperçu des relations :\n";
echo str_repeat('-', 80) . "\n";
for ($i = 0; $i < min(5, count($relations)); $i++) {
    $r = $relations[$i];
    echo sprintf("  %d. %s (%s) → %s (%s)\n",
        $i + 1,
        $r['user_name'],
        $r['user_code'],
        $r['sponsor_name'] ?? 'Inconnu',
        $r['sponsor_code']
    );
}
if (count($relations) > 5) {
    echo "  ... et " . (count($relations) - 5) . " autres\n";
}
echo str_repeat('-', 80) . "\n\n";

// ============================================================
// CHARGER LES UTILISATEURS EXISTANTS
// ============================================================

echo "⏳ Chargement des utilisateurs existants...\n";

$allUsers = User::where('id', '>', 1)->get(['id', 'name', 'sponsor_id']);
$userCodeMap = [];
foreach ($allUsers as $user) {
    if ($user->sponsor_id) {
        $userCodeMap[$user->sponsor_id] = $user->id;
    }
}

echo "✅ Utilisateurs en base : " . $allUsers->count() . "\n";
echo "✅ Codes en base : " . count($userCodeMap) . "\n\n";

// ============================================================
// METTRE À JOUR LES PARRAINS
// ============================================================

echo "🚀 Début de la mise à jour des parrains...\n\n";

DB::beginTransaction();

try {
    $updated = 0;
    $notFound = 0;
    $alreadySet = 0;
    $autoParrain = 0;
    $errors = [];
    $total = count($relations);
    
    // Barre de progression
    $barLength = 50;
    $progress = 0;
    
    foreach ($relations as $index => $rel) {
        // Mettre à jour la barre de progression
        $progress = ($index + 1) / $total;
        $bar = str_repeat('▓', round($progress * $barLength));
        $empty = str_repeat('░', $barLength - round($progress * $barLength));
        printf("\r  [%s%s] %3d%%", $bar, $empty, round($progress * 100));
        
        // Trouver l'utilisateur par son code
        $userId = $userCodeMap[$rel['user_code']] ?? null;
        
        if (!$userId) {
            $notFound++;
            continue;
        }
        
        // Trouver le parrain par son code
        $parrainId = $userCodeMap[$rel['sponsor_code']] ?? null;
        
        if (!$parrainId) {
            $notFound++;
            continue;
        }
        
        // Vérifier si c'est un auto-parrainage
        if ($userId == $parrainId) {
            $autoParrain++;
            $errors[] = "Auto-parrainage : {$rel['user_name']} (CODE: {$rel['user_code']})";
            continue;
        }
        
        // Mettre à jour
        $user = User::find($userId);
        if (!$user) {
            continue;
        }
        
        // Vérifier si déjà à jour
        if ($user->parrain_id == $parrainId) {
            $alreadySet++;
            continue;
        }
        
        $user->parrain_id = $parrainId;
        $user->save();
        $updated++;
        
        // Afficher les mises à jour
        if ($updated % 20 === 0) {
            echo "\n  🔄 $updated parrains mis à jour...";
        }
    }
    
    // Fin de la barre
    echo "\r  [" . str_repeat('▓', $barLength) . "] 100%\n\n";
    
    // ============================================================
    // AFFICHER LE RÉSUMÉ
    // ============================================================
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║              📊 RÉSUMÉ DE LA MISE À JOUR                   ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "📌 Relations traitées : " . number_format($total) . "\n";
    echo "✅ Parrains mis à jour : " . number_format($updated) . "\n";
    echo "⏭️  Déjà à jour : " . number_format($alreadySet) . "\n";
    echo "❌ Utilisateurs non trouvés : " . number_format($notFound) . "\n";
    echo "⚠️  Auto-parrainages : " . number_format($autoParrain) . "\n";
    echo "❌ Erreurs : " . number_format(count($errors)) . "\n";
    
    if (!empty($errors) && count($errors) <= 15) {
        echo "\n📝 Détails des auto-parrainages :\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    
    // ============================================================
    // VÉRIFICATION FINALE
    // ============================================================
    
    echo "\n⏳ Vérification finale...\n";
    
    $sansParrain = User::whereNull('parrain_id')
                       ->where('id', '>', 1)
                       ->whereNotNull('sponsor_id')
                       ->count();
    
    $avecParrain = User::whereNotNull('parrain_id')
                       ->where('id', '>', 1)
                       ->count();
    
    echo "\n";
    echo "📊 STATISTIQUES FINALES :\n";
    echo "  - Utilisateurs avec parrain : " . number_format($avecParrain) . "\n";
    echo "  - Utilisateurs sans parrain : " . number_format($sansParrain) . "\n";
    
    // Valider la transaction
    DB::commit();
    
    echo "\n";
    echo "✅ MISE À JOUR TERMINÉE AVEC SUCCÈS !\n";
    echo "\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ Erreur : " . $e->getMessage() . "\n";
    echo "📄 Détails : " . $e->getTraceAsString() . "\n";
}

// ============================================================
// FONCTIONS D'AIDE
// ============================================================

function cleanName($name) {
    if (empty($name)) return '';
    $name = str_ireplace('sponsor', '', $name);
    $name = str_ireplace('Sponsor', '', $name);
    $name = str_ireplace('SPONSOR', '', $name);
    $name = str_ireplace(' SPR ', ' ', $name);
    return trim(preg_replace('/\s+/', ' ', trim($name)));
}

echo "🎉 Script terminé !\n";