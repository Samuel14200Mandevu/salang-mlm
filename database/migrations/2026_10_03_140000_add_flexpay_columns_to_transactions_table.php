<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('wallet_id')
                    ->constrained()
                    ->onDelete('set null');
            }

            if (! Schema::hasColumn('transactions', 'transaction_id')) {
                $table->string('transaction_id')
                    ->nullable()
                    ->after('reference');
            }

            if (! Schema::hasColumn('transactions', 'provider')) {
                $table->string('provider')
                    ->nullable()
                    ->after('transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('transactions', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('transactions', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};
