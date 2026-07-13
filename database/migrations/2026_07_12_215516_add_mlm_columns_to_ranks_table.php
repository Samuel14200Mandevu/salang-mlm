<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            if (!Schema::hasColumn('ranks', 'level')) {
                $table->integer('level')->default(1);
            }
            if (!Schema::hasColumn('ranks', 'monthly_pv_required')) {
                $table->integer('monthly_pv_required')->default(0);
            }
            if (!Schema::hasColumn('ranks', 'team_pv_required')) {
                $table->integer('team_pv_required')->default(0);
            }
            if (!Schema::hasColumn('ranks', 'pv_payment_required')) {
                $table->integer('pv_payment_required')->default(0);
            }
            if (!Schema::hasColumn('ranks', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('ranks', 'conditions')) {
                $table->json('conditions')->nullable();
            }
            if (!Schema::hasColumn('ranks', 'commission_types')) {
                $table->json('commission_types')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ranks', function (Blueprint $table) {
            $columns = ['level', 'monthly_pv_required', 'team_pv_required', 
                        'pv_payment_required', 'description', 'conditions', 'commission_types'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ranks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
