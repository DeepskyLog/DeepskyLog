<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Once;

/**
 * Cached replacement for Schema::hasColumn(), which asks the database for the
 * columns of the table on every call (information_schema on MariaDB).
 *
 * The column list of a table is kept in the application cache for a day and
 * once per request; AppServiceProvider forgets it after migrations have run.
 */
class SchemaColumns
{
    private const CACHE_PREFIX = 'schema_columns:';

    /**
     * Whether the table has the column (case-insensitive, like Schema::hasColumn()).
     */
    public static function has(string $table, string $column): bool
    {
        return in_array(strtolower($column), self::columns($table), true);
    }

    /**
     * The lower case column names of the table.
     *
     * @return list<string>
     */
    public static function columns(string $table): array
    {
        return once(fn () => Cache::remember(
            self::CACHE_PREFIX . $table,
            now()->addDay(),
            fn () => array_map('strtolower', Schema::getColumnListing($table))
        ));
    }

    /**
     * Forget the cached column lists of the tables.
     *
     * @param  list<string>  $tables
     */
    public static function forget(array $tables): void
    {
        Once::flush();

        foreach ($tables as $table) {
            try {
                Cache::forget(self::CACHE_PREFIX . $table);
            } catch (\Throwable $_) {
                // A rollback can drop the cache table of the database cache store.
            }
        }
    }
}
