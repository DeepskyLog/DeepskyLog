<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * ORDER BY fragment that sorts object names as people read them: catalog
 * first, then the number, then what follows it (NGC 317A, NGC 3690B,
 * NGC 6636-1, NGC 7640; Sh 2-27 before Sh 2-155).
 *
 * The name is split into the text before the first number, that number, the
 * text after it up to the next number, and that number; the full name breaks
 * the ties. Removing every digit to get the "prefix" (as the tables did) made
 * NGC 3690B's prefix "ngc b", which sorted it after every NGC without a
 * suffix whatever its number.
 */
class NaturalNameOrder
{
    /**
     * @param  string  $column  A qualified column or SQL expression (never user input).
     * @param  string  $direction  asc or desc.
     */
    public static function sql(string $column, string $direction = 'asc'): string
    {
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $name = "COALESCE({$column}, '')";

        // SQLite (the tests) has no REGEXP_SUBSTR/REGEXP_REPLACE.
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "LOWER({$name}) {$dir}";
        }

        $rest = "REGEXP_REPLACE({$name}, '^[^0-9]*[0-9]*', '')";
        $number = fn (string $text) => "CAST(COALESCE(NULLIF(REGEXP_SUBSTR({$text}, '[0-9]+'), ''), '0') AS UNSIGNED)";

        return implode(', ', [
            "LOWER(TRIM(REGEXP_SUBSTR({$name}, '^[^0-9]*'))) {$dir}",
            $number($name) . " {$dir}",
            "LOWER(TRIM(REGEXP_SUBSTR({$rest}, '^[^0-9]*'))) {$dir}",
            $number($rest) . " {$dir}",
            "{$column} {$dir}",
        ]);
    }
}
