<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('commissions', 'source_commission_history_id')) {
            return;
        }

        Schema::table('commissions', function (Blueprint $table) {
            $table->unsignedBigInteger('source_commission_history_id')
                ->nullable()
                ->after('id');
            $table->unique('source_commission_history_id', 'commissions_source_history_unique');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            if (Schema::hasColumn('commissions', 'source_commission_history_id')) {
                $table->dropUnique('commissions_source_history_unique');
                $table->dropColumn('source_commission_history_id');
            }
        });
    }
};
