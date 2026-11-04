<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicateColumn('reference');
        $this->deduplicateColumn('transaction_id');

        Schema::table('transactions', function (Blueprint $table) {
            if (! $this->indexExists('transactions', 'transactions_reference_unique')) {
                $table->unique('reference', 'transactions_reference_unique');
            }
            if (! $this->indexExists('transactions', 'transactions_transaction_id_unique')) {
                $table->unique('transaction_id', 'transactions_transaction_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if ($this->indexExists('transactions', 'transactions_reference_unique')) {
                $table->dropUnique('transactions_reference_unique');
            }
            if ($this->indexExists('transactions', 'transactions_transaction_id_unique')) {
                $table->dropUnique('transactions_transaction_id_unique');
            }
        });
    }

    private function deduplicateColumn(string $column): void
    {
        if (! Schema::hasColumn('transactions', $column)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $duplicates = DB::table('transactions')
                ->select($column)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->havingRaw('COUNT(*) > 1')
                ->pluck($column);

            foreach ($duplicates as $value) {
                $ids = DB::table('transactions')
                    ->where($column, $value)
                    ->orderBy('id')
                    ->pluck('id');

                $ids->shift();
                if ($ids->isNotEmpty()) {
                    DB::table('transactions')
                        ->whereIn('id', $ids->all())
                        ->update([$column => null]);
                }
            }

            return;
        }

        DB::statement("
            UPDATE transactions t
            INNER JOIN (
                SELECT {$column}, MIN(id) AS keep_id
                FROM transactions
                WHERE {$column} IS NOT NULL AND {$column} != ''
                GROUP BY {$column}
                HAVING COUNT(*) > 1
            ) d ON t.{$column} = d.{$column} AND t.id != d.keep_id
            SET t.{$column} = NULL
        ");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? '') === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = Schema::getConnection()->getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return ((int) ($row->c ?? 0)) > 0;
    }
};
