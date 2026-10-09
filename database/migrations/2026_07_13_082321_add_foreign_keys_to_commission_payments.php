<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('commission_payments') || $this->foreignKeyExists('commission_payments', 'commission_payments_commission_period_id_foreign')) {
            return;
        }

        Schema::table('commission_payments', function (Blueprint $table) {
            $table->foreign('commission_period_id')
                  ->references('id')
                  ->on('commission_periods')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('commission_payments', function (Blueprint $table) {
            $table->dropForeign(['commission_period_id']);
        });
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT 1 AS found FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $table, $constraintName, 'FOREIGN KEY']
        );

        return $row !== null;
    }
};
