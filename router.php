<?php
// router.php
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Si c'est une requête pour un fichier statique, le servir
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // Servir le fichier statique
}

// Sinon, passer par index.php
require_once __DIR__ . '/public/index.php';
