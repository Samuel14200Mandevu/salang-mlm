#!/usr/bin/env php
<?php

// ============================================================
// SCRIPT OPTIMISÉ : METTRE À JOUR LES PARRAINS
// Version : 2.0 - Une seule requête pour tout !
// ============================================================

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

// ============================================================
// CONFIGURATION
// ============================================================

$filePath = storage_path('app/imports/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx');

// Si le fichier n'existe pas, essayer dans Téléchargements
if (!file_exists($filePath)) {
    $altPath = '/home/samuel-mandevu/Téléchargements/LISTE DES MEMBRES SALANG_AOUT_2026.xlsx';
    if (file_exists($altPath)) {
        $filePath = $altPath;
    } else {
        die("❌ Fichier non trouvé !\n");
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     🔄 MISE À JOUR OPTIMISÉE DES PARRAINS                  ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "📂 Fichier : " . basename($filePath) . "\n";
echo "📊 Base : " . config('database.connections.mysql.database') . "\n\n";

// ============================================================
// 1. EXTRAIRE LES RELATIONS
// ============================================================

echo "⏳ Extraction des relations...\n";

$spreadsheet = IOFactory::load($filePath);
$rows = $spreadsheet->getActiveSheet()->toArray();

$relations = [];
for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (!is_numeric(trim($row[0] ?? '')) || empty(trim($row[1] ?? ''))) {
        continue;
    }
    
    $code = str_replace([' ', '-', '—', '.'], '', trim($row[2] ?? ''));
    if (empty($code)) continue;
    
    if ($i + 1 < count($rows)) {
        $nextCode = str_replace([' ', '-', '—', '.'], '', trim($rows[$i + 1][2] ?? ''));
        if (!empty($nextCode)) {
            $relations[] = ['user' => $code, 'parrain' => $nextCode];
            $i++;
        }
    }
}

echo "✅ Relations extraites : " . count($relations) . "\n\n";

if (count($relations) === 0) {
    die("❌ Aucune relation trouvée\n");
}

// ============================================================
// 2. CONSTRUIRE UNE TABLE DE MAPPING
// ============================================================

echo "⏳ Construction du mapping des codes...\n";

// Récupérer tous les utilisateurs en UNE SEULE requête
$allUsers = User::where('id', '>', 1)->get(['id', 'sponsor_id']);
$codeToId = [];
foreach ($allUsers as $user) {
    if ($user->sponsor_id) {
        $codeToId[$user->sponsor_id] = $user->id;
    }
}

echo "✅ Codes en base : " . count($codeToId) . "\n\n";

// ============================================================
// 3. PRÉPARER LES DONNÉES POUR LA MISE À JOUR
// ============================================================

echo "⏳ Préparation des mises à jour...\n";

$updates = [];
$notFound = 0;
$autoParrain = 0;

foreach ($relations as $rel) {
    $userId = $codeToId[$rel['user']] ?? null;
    $parrainId = $codeToId[$rel['parrain']] ?? null;
    
    if (!$userId || !$parrainId) {
        $notFound++;
        continue;
    }
    
    if ($userId == $parrainId) {
        $autoParrain++;
        continue;
    }
    
    $updates[] = ['user_id' => $userId, 'parrain_id' => $parrainId];
}

echo "✅ Mises à jour à effectuer : " . count($updates) . "\n";
echo "⚠️  Non trouvés : $notFound\n";
echo "⚠️  Auto-parrainages : $autoParrain\n\n";

// ============================================================
// 4. EXÉCUTER EN UNE SEULE REQUÊTE (BULK UPDATE)
// ============================================================

if (count($updates) === 0) {
    echo "✅ Aucune mise à jour nécessaire !\n";
    exit;
}

echo "🚀 Exécution des mises à jour en une seule requête...\n\n";

DB::beginTransaction();

try {
    // Construire la requête CASE pour mettre à jour en UNE SEULE FOIS
    $caseSql = "UPDATE users SET parrain_id = CASE id\n";
    $ids = [];
    
    foreach ($updates as $update) {
        $caseSql .= "    WHEN {$update['user_id']} THEN {$update['parrain_id']}\n";
        $ids[] = $update['user_id'];
    }
    
    $caseSql .= "END WHERE id IN (" . implode(',', $ids) . ")";
    
    // Exécuter en une seule requête !
    $affected = DB::update($caseSql);
    
    DB::commit();
    
    echo "✅ Mise à jour terminée !\n";
    echo "📊 $affected utilisateurs mis à jour\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

// ============================================================
// 5. VÉRIFICATION FINALE
// ============================================================

echo "⏳ Vérification finale...\n";

$avecParrain = User::whereNotNull('parrain_id')->where('id', '>', 1)->count();
$sansParrain = User::whereNull('parrain_id')->where('id', '>', 1)->count();

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                  📊 RÉSULTAT FINAL                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "✅ Utilisateurs avec parrain : " . number_format($avecParrain) . "\n";
echo "⏭️  Utilisateurs sans parrain : " . number_format($sansParrain) . "\n";
echo "\n";
echo "🎉 Mise à jour terminée avec succès !\n";