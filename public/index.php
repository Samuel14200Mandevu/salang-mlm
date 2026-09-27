<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ══════════════════════════════════════════════════════════════════════
// ✅ TIMEOUTS ÉTENDUS POUR LE SYSTÈME MLM
// ══════════════════════════════════════════════════════════════════════
// Nécessaires pour :
//   - Imports CSV massifs (jusqu'à 2000+ lignes)
//   - Recalculs de team_pv sur de gros arbres
//   - Traitement des jobs d'import
// ══════════════════════════════════════════════════════════════════════
set_time_limit(600);              // 10 minutes max par requête
ini_set('memory_limit', '512M');  // 512 Mo de mémoire max
ini_set('max_input_time', '600'); // 10 minutes pour lire les entrées

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());