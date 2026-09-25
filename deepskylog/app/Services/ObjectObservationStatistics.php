<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Observation statistics of deep-sky objects for object tables, taken from the
 * unified `observations` table (never from the legacy database).
 *
 * Each statistic is a correlated subquery on the (target_type, target_name)
 * index, so it is only computed for the rows a query shows, sorts or filters
 * on; a pagination count query drops the select list. Dates use the Ymd format
 * the tables' fields and the exports expect.
 */
class ObjectObservationStatistics
{
    /**
     * The SQL expressions of the statistics, keyed by their column alias.
     *
     * @param  string  $objectName  SQL expression holding the object's name
     * @return array<string, string>
     */
    public static function expressions(?string $username, string $objectName = 'objects.name'): array
    {
        $user = DB::getPdo()->quote($username ?? '');
        $ymd = fn(string $date): string => DB::getDriverName() === 'sqlite'
            ? "strftime('%Y%m%d', {$date})"
            : "DATE_FORMAT({$date}, '%Y%m%d')";
        $statistic = fn(string $aggregate, string $condition = '1 = 1'): string => "(SELECT {$aggregate} FROM observations o"
            . " WHERE o.target_type = 'deepsky' AND o.target_name = {$objectName}"
            . " AND {$condition})";

        $expressions = [
            'total_observations' => $statistic('COUNT(*)'),
            'total_drawings' => $statistic('COUNT(*)', 'o.has_drawing = 1'),
            'your_observations' => $statistic('COUNT(*)', "o.observer_username = {$user}"),
            'your_drawings' => $statistic('COUNT(*)', "o.observer_username = {$user} AND o.has_drawing = 1"),
            'last_seen_date' => $statistic($ymd('MAX(o.observed_on)')),
            'your_last_seen_date' => $statistic($ymd('MAX(o.observed_on)'), "o.observer_username = {$user}"),
            'last_drawing_date' => $statistic($ymd('MAX(o.observed_on)'), 'o.has_drawing = 1'),
            'your_last_drawing_date' => $statistic($ymd('MAX(o.observed_on)'), "o.observer_username = {$user} AND o.has_drawing = 1"),
        ];

        // Aliases matching the PowerGrid field names, so their sorting works.
        $expressions['seen'] = $expressions['total_observations'];
        $expressions['last_seen'] = $expressions['your_last_seen_date'];

        return $expressions;
    }

    /**
     * The statistics as a select list fragment ("expr as alias, ...").
     */
    public static function selectList(?string $username, string $objectName = 'objects.name'): string
    {
        return collect(self::expressions($username, $objectName))
            ->map(fn(string $expression, string $alias): string => "{$expression} as {$alias}")
            ->implode(', ');
    }
}
