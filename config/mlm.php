<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mode démo MLM (calcul / sync historique)
    |--------------------------------------------------------------------------
    |
    | true  = phase de démo : recalculate + CommissionHistoryToCommissionsSync autorisés
    | false = production : runtime uniquement, écriture historique bloquée
    |
    */
    'demo_mode_enabled' => env('MLM_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Affichage des commissions (périodes)
    |--------------------------------------------------------------------------
    |
    | Les périodes strictement antérieures à cette valeur (YYYY-MM) sont
    | exclues des écrans admin / caisse / sélecteurs de mois.
    | Les données restent en base (FIFO portefeuille, audit).
    |
    */
    'commissions_display_from' => env('MLM_COMMISSIONS_DISPLAY_FROM', '2026-01'),
];
