<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'rank4_grandfathered')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('rank4_grandfathered')->default(false)->after('rank_level');
            });
        }

        // Membres déjà Directeur (niv. 4+) : anciennes règles conservées (pas de rétrogradation).
        DB::table('users')
            ->where('rank_level', '>=', 4)
            ->update(['rank4_grandfathered' => true]);
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'rank4_grandfathered')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('rank4_grandfathered');
            });
        }
    }
};
