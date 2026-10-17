<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pv_history') && ! Schema::hasColumn('pv_history', 'status')) {
            Schema::table('pv_history', function (Blueprint $table) {
                $table->string('status', 16)->default('posted')->after('type');
                $table->unsignedBigInteger('order_id')->nullable()->after('status');
                $table->index(['period', 'status']);
            });
        }

        if (Schema::hasTable('pv_allocations')) {
            Schema::table('pv_allocations', function (Blueprint $table) {
                if (! Schema::hasColumn('pv_allocations', 'period')) {
                    $table->string('period', 7)->nullable()->after('order_id');
                    $table->index(['distributor_id', 'period', 'status']);
                }
                if (! Schema::hasColumn('pv_allocations', 'posted_at')) {
                    $table->timestamp('posted_at')->nullable()->after('approved_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pv_history') && Schema::hasColumn('pv_history', 'status')) {
            Schema::table('pv_history', function (Blueprint $table) {
                $table->dropIndex(['period', 'status']);
                $table->dropColumn(['status', 'order_id']);
            });
        }

        if (Schema::hasTable('pv_allocations')) {
            Schema::table('pv_allocations', function (Blueprint $table) {
                if (Schema::hasColumn('pv_allocations', 'period')) {
                    $table->dropIndex(['distributor_id', 'period', 'status']);
                    $table->dropColumn('period');
                }
                if (Schema::hasColumn('pv_allocations', 'posted_at')) {
                    $table->dropColumn('posted_at');
                }
            });
        }
    }
};
