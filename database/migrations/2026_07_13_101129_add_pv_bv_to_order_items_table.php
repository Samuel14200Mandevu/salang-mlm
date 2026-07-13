<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'pv_value')) {
                $table->integer('pv_value')->default(0)->after('total');
            }
            if (!Schema::hasColumn('order_items', 'bv_value')) {
                $table->integer('bv_value')->default(0)->after('pv_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'pv_value')) {
                $table->dropColumn('pv_value');
            }
            if (Schema::hasColumn('order_items', 'bv_value')) {
                $table->dropColumn('bv_value');
            }
        });
    }
};
