<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * The search conditions on the sketches of the week and month, shared by the
 * observation search (the drawing filter) and the object search.
 *
 * A sketch chosen for a later date only counts for those who choose them
 * (administrators and database experts), so a search does not give the choice
 * away before its date.
 */
final class SketchChoices
{
    public const WEEK = 'week';

    public const MONTH = 'month';

    public const WEEK_OR_MONTH = 'week_or_month';

    private const TABLES = [
        self::WEEK => ['sketch_of_the_week'],
        self::MONTH => ['sketch_of_the_month'],
        self::WEEK_OR_MONTH => ['sketch_of_the_week', 'sketch_of_the_month'],
    ];

    /**
     * Keeps the observations (by the column holding their id) that were, or
     * with $chosen false were not, chosen as a sketch of the given kind.
     */
    public static function whereObservation($query, string $kind, bool $chosen = true, string $idColumn = 'observations.id'): void
    {
        self::where($query, $kind, $chosen, function ($subQuery, string $table) use ($idColumn) {
            $subQuery->whereColumn("{$table}.observation_id", $idColumn);
        });
    }

    /**
     * Keeps the deep-sky objects (by the column holding their name) of which a
     * drawing was, or with $chosen false none was, chosen as a sketch of the
     * given kind.
     */
    public static function whereObject($query, string $kind, bool $chosen = true, string $nameColumn = 'objects.name'): void
    {
        self::where($query, $kind, $chosen, function ($subQuery, string $table) use ($nameColumn) {
            $subQuery->join('observations as sketch_observations', 'sketch_observations.id', '=', "{$table}.observation_id")
                ->where('sketch_observations.target_type', Observation::TYPE_DEEPSKY)
                ->whereColumn('sketch_observations.target_name', $nameColumn);
        });
    }

    public static function isKind(string $kind): bool
    {
        return array_key_exists($kind, self::TABLES);
    }

    private static function where($query, string $kind, bool $chosen, callable $link): void
    {
        $tables = self::TABLES[$kind] ?? null;
        if ($tables === null) {
            return;
        }

        $canChoose = Auth::user()?->can('add_sketch', User::class) ?? false;

        $query->where(function ($outer) use ($tables, $chosen, $canChoose, $link) {
            foreach ($tables as $table) {
                $exists = function ($subQuery) use ($table, $canChoose, $link) {
                    $subQuery->selectRaw('1')->from($table);
                    $link($subQuery, $table);
                    if (! $canChoose) {
                        $subQuery->whereDate("{$table}.date", '<=', today()->toDateString());
                    }
                };
                // Chosen: in any of the tables; not chosen: in none of them.
                if ($chosen) {
                    $outer->orWhereExists($exists);
                } else {
                    $outer->whereNotExists($exists);
                }
            }
        });
    }
}
