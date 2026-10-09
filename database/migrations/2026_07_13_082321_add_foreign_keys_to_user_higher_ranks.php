<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_higher_ranks') || $this->foreignKeyExists('user_higher_ranks', 'higher_rank_id')) {
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

    private function foreignKeyExists(string $table, string $column): bool
    {
        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'sqlite') {
            foreach ($connection->select("PRAGMA foreign_key_list({$table})") as $foreignKey) {
                if (($foreignKey->from ?? null) === $column) {
                    return true;
                }
            }

            return false;
        }

        $constraintName = "{$table}_{$column}_foreign";
        $row = DB::selectOne(
            'SELECT 1 AS found FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$connection->getDatabaseName(), $table, $constraintName, 'FOREIGN KEY']
        );

        return $row !== null;
    }
};
