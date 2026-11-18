<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('consultations')) {
            return;
        }

        if (! Schema::hasColumn('consultations', 'services_payment')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->json('services_payment')->nullable()->after('total_services');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultations')) {
            return;
        }

        if (Schema::hasColumn('consultations', 'services_payment')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropColumn('services_payment');
            });
        }
    }
};
