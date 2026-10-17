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
];
