<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Vide l’historique PV (pv_transactions) pour tous les membres.
 * Ne touche pas aux soldes users.* ni user_pv_balances.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pv_transactions')) {
            return;
        }

        DB::table('pv_transactions')->delete();
    }

    public function down(): void
    {
        // Irréversible : l’historique effacé ne peut pas être restauré.
    }
};
