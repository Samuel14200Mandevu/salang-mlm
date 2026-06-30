<?php

// Charger l'autoloader de Composer
require __DIR__ . '/../vendor/autoload.php';

// Créer l'application Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Exécuter la requête
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Envoyer la réponse
$response->send();
$kernel->terminate($request, $response);