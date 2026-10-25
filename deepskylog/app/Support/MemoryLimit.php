<?php

namespace App\Support;

/**
 * Raise PHP's memory limit for a heavy task, never lower it.
 *
 * A queue worker runs job after job in one process, so an ini_set() of one
 * job holds for all that follow: a zip that set 1024M left the unlimited CLI
 * limit at 1 GB, and the next atlas ran out of memory building its PDF.
 */
class MemoryLimit
{
    public static function atLeast(string $limit): void
    {
        $current = ini_get('memory_limit');

        if ($current === false || $current === '-1' || self::bytes($current) >= self::bytes($limit)) {
            return;
        }

        @ini_set('memory_limit', $limit);
    }

    public static function bytes(string $limit): int
    {
        $limit = trim($limit);
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        return (int) $limit * match (strtolower(substr($limit, -1))) {
            'g' => 1024 ** 3,
            'm' => 1024 ** 2,
            'k' => 1024,
            default => 1,
        };
    }
}
