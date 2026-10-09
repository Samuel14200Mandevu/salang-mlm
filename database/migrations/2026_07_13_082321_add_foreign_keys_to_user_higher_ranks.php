<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_higher_ranks') || $this->foreignKeyExists('user_higher_ranks', 'user_higher_ranks_higher_rank_id_foreign')) {
            return;
        }

        Schema::table('user_higher_ranks', function (Blueprint $table) {
            $table->foreign('higher_rank_id')
                  ->references('id')
                  ->on('higher_ranks')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('user_higher_ranks', function (Blueprint $table) {
            $table->dropForeign(['higher_rank_id']);
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
