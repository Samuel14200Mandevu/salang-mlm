<?php
// app/Observers/GenealogyObserver.php

namespace App\Observers;

use App\Models\Genealogy;
use Illuminate\Support\Facades\Log;

class GenealogyObserver
{
    /**
     * Après la création d'une entrée dans l'arbre généalogique
     */
    public function created(Genealogy $genealogy): void
    {
        // Mettre à jour le grade de l'utilisateur
        if ($genealogy->user_id) {
            $genealogy->user->calculateAndUpdateRank();
        }
        
        // Mettre à jour le grade du parrain
        if ($genealogy->sponsor_id) {
            $sponsor = \App\Models\User::find($genealogy->sponsor_id);
            if ($sponsor) {
                $sponsor->calculateAndUpdateRank();
            }
        }
    }
}