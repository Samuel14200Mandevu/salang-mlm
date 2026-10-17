<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'cashier_id')) {
                $table->unsignedBigInteger('cashier_id')->nullable()->after('user_id');
                $table->foreign('cashier_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['cashier_id']);
            }
            if (! Schema::hasColumn('orders', 'total_pv')) {
                $table->decimal('total_pv', 15, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('orders', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('cashier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cashier_id')) {
                $table->dropForeign(['cashier_id']);
                $table->dropIndex(['cashier_id']);
                $table->dropColumn('cashier_id');
            }
            if (Schema::hasColumn('orders', 'total_pv')) {
                $table->dropColumn('total_pv');
            }
            if (Schema::hasColumn('orders', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
};
