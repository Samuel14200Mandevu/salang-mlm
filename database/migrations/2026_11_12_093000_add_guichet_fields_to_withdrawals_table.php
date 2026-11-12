<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawals', 'cashier_id')) {
                $table->unsignedBigInteger('cashier_id')->nullable()->after('completed_at');
                $table->foreign('cashier_id')->references('id')->on('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('withdrawals', 'receipt_number')) {
                $table->string('receipt_number', 64)->nullable()->after('cashier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'cashier_id')) {
                $table->dropForeign(['cashier_id']);
                $table->dropColumn('cashier_id');
            }
            if (Schema::hasColumn('withdrawals', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
        });
    }
};
