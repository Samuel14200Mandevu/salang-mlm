<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grandfathering : les comptes déjà créés (imports, caissier, legacy)
 * sont considérés comme e-mail vérifiés pour ne pas bloquer ~2500 membres
 * si le middleware "verified" est activé plus tard.
 *
 * Les nouveaux inscrits restent avec email_verified_at = null jusqu'au clic du lien.
 *
 * SQL portable MySQL + SQLite (tests) : CURRENT_TIMESTAMP au lieu de NOW().
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        // Irréversible volontairement : on ne remet pas NULL sur les comptes grandfathered.
    }
};
