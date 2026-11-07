<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_entries', function (Blueprint $table) {
            $table->string('participant_nom', 120)->nullable()->after('participant_name');
            $table->string('participant_postnom', 120)->nullable()->after('participant_nom');
            $table->string('participant_prenom', 120)->nullable()->after('participant_postnom');
        });
    }

    public function down(): void
    {
        Schema::table('activity_entries', function (Blueprint $table) {
            $table->dropColumn([
                'participant_nom',
                'participant_postnom',
                'participant_prenom',
            ]);
        });
    }
};
