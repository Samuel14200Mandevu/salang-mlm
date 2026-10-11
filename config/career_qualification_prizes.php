<?php

use App\Support\MlmRank;

$levelNames = MlmRank::levelNames();

return [
    /*
    | Types de commission affichés dans Portefeuille → Aperçu (montants payés, périodes visibles membre).
    */
    'wallet_bonus_types' => [
        'direct' => [
            'label' => 'Direct',
            'short' => 'Bonus direct',
        ],
        'indirect' => [
            'label' => 'Indirect',
            'short' => 'Bonus indirect',
        ],
        'sponsor' => [
            'label' => 'Parrainage',
            'short' => 'Bonus de parrainage',
        ],
        'cash_pos' => [
            'label' => 'Profit POS',
            'short' => 'Détail profit (POS)',
        ],
    ],

    /*
    | Récompenses de qualification carrière (niveaux MLM 4–9).
    | image : chemin sous public/ (null = pas de visuel).
    | publication : contenu idempotent pour la galerie Services / Publications.
    */
    'levels' => [
        4 => [
            'reward' => '15 $',
            'image' => 'images/15$.png',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 4 — 15 $',
                'description' => 'Récompense de qualification au grade '.$levelNames[4].' : prime de 15 USD à l’atteinte du niveau.',
            ],
        ],
        5 => [
            'reward' => 'Smart TV',
            'image' => 'images/smarttv.jpeg',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 5 — Smart TV',
                'description' => 'Récompense de qualification au grade '.$levelNames[5].' : une Smart TV.',
            ],
        ],
        6 => [
            'reward' => 'Moto Boxer',
            'image' => 'images/moto.jpeg',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 6 — Moto Boxer',
                'description' => 'Récompense de qualification au grade '.$levelNames[6].' : une moto Boxer.',
            ],
        ],
        7 => [
            'reward' => 'Mini voiture',
            'image' => 'images/minivoiture.jpeg',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 7 — Mini voiture',
                'description' => 'Récompense de qualification au grade '.$levelNames[7].' : une mini voiture.',
            ],
        ],
        8 => [
            'reward' => 'Voyage',
            'image' => 'images/voyage.jpeg',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 8 — Voyage',
                'description' => 'Récompense de qualification au grade '.$levelNames[8].' : un voyage.',
            ],
        ],
        9 => [
            'reward' => 'Grande voiture',
            'image' => 'images/grandvoiture.jpg',
            'publication' => [
                'title' => 'Bonus carrière · Niveau 9 — Grande voiture',
                'description' => 'Récompense de qualification au grade '.$levelNames[9].' : une grande voiture.',
            ],
        ],
    ],
];
