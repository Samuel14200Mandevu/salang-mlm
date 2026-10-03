<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Expressions SQL compatibles MySQL (prod) et SQLite (tests unitaires).
 */
class SqlDialect
{
    public static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    public static function isSqlite(): bool
    {
        return self::driver() === 'sqlite';
    }

    /** Évite les cycles dans un chemin d'ids séparés par des virgules. */
    public static function notInPath(string $memberSql, string $pathSql): string
    {
        if (self::isSqlite()) {
            return "(',' || {$pathSql} || ',' NOT LIKE '%,' || CAST({$memberSql} AS TEXT) || ',%')";
        }

        return "FIND_IN_SET({$memberSql}, {$pathSql}) = 0";
    }

    /** Concatène path,id pour la CTE récursive. */
    public static function appendToPath(string $pathSql, string $idSql): string
    {
        if (self::isSqlite()) {
            return "({$pathSql} || ',' || CAST({$idSql} AS TEXT))";
        }

        return "CONCAT({$pathSql}, ',', {$idSql})";
    }
}
