<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_entries', function (Blueprint $table) {
            $table->date('participant_birth_date')->nullable()->after('participant_phone');
            $table->string('participant_gender', 20)->nullable()->after('participant_birth_date');
            $table->string('participant_address', 500)->nullable()->after('participant_gender');
            $table->string('participant_email')->nullable()->after('participant_address');
            $table->string('participant_profession', 255)->nullable()->after('participant_email');
            $table->string('participant_identity_number', 100)->nullable()->after('participant_profession');
        });
    }

    public function down(): void
    {
        Schema::table('activity_entries', function (Blueprint $table) {
            $table->dropColumn([
                'participant_birth_date',
                'participant_gender',
                'participant_address',
                'participant_email',
                'participant_profession',
                'participant_identity_number',
            ]);
        });
    }
};
