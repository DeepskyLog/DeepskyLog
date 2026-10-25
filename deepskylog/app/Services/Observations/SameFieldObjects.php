<?php

namespace App\Services\Observations;

use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Services\TonightsBest;
use Illuminate\Support\Facades\DB;

/**
 * The deep-sky objects an observer may have seen in the same field as the
 * object of a new observation (#831): the objects it is part of or that are
 * part of it, and the objects within the field of the eyepiece, nearest first.
 */
class SameFieldObjects
{
    /** The most objects suggested. */
    public const MAX = 20;

    /** The radius in degrees when the field of the equipment is not known. */
    private const DEFAULT_RADIUS = 0.25;

    /** The smallest and largest radius in degrees. */
    private const MIN_RADIUS = 5 / 60;

    private const MAX_RADIUS = 1.0;

    /**
     * @return list<array{name: string, type: string, separation: float, part_of: bool}>
     *               separation in arcminutes
     */
    public static function suggest(string $objectName, ?float $fieldDeg = null): array
    {
        $target = DB::table('objects')->where('name', $objectName)->first(['name', 'ra', 'decl']);
        if (! $target) {
            return [];
        }

        $related = DB::table('objectpartof')->where('objectname', $objectName)->pluck('partofname')
            ->merge(DB::table('objectpartof')->where('partofname', $objectName)->pluck('objectname'))
            ->unique()
            ->reject(fn ($name) => $name === $objectName)
            ->values();

        $radius = $fieldDeg !== null && $fieldDeg > 0
            ? min(self::MAX_RADIUS, max(self::MIN_RADIUS, $fieldDeg / 2))
            : self::DEFAULT_RADIUS;
        $ra = (float) $target->ra;
        $decl = (float) $target->decl;
        $dRa = $radius / 15 / max(0.01, cos(deg2rad($decl)));

        $objects = DB::table('objects')
            ->where(fn ($q) => $q
                ->whereIn('name', $related)
                ->orWhere(fn ($q) => $q
                    ->whereBetween('decl', [$decl - $radius, $decl + $radius])
                    ->whereBetween('ra', [$ra - $dRa, $ra + $dRa])
                    ->whereNotIn('type', array_merge(TonightsBest::EXCLUDED_TYPES, ['NONEX']))))
            ->where('name', '!=', $objectName)
            ->get(['name', 'type', 'ra', 'decl']);

        return $objects
            ->map(fn ($o) => [
                'name' => $o->name,
                'type' => (string) $o->type,
                'separation' => round(self::separation($ra, $decl, (float) $o->ra, (float) $o->decl) * 60, 1),
                'part_of' => $related->contains($o->name),
            ])
            ->filter(fn ($o) => $o['part_of'] || $o['separation'] <= $radius * 60)
            ->sortBy([['part_of', 'desc'], ['separation', 'asc']])
            ->take(self::MAX)
            ->values()
            ->all();
    }

    /**
     * The true field in degrees of the eyepiece (and lens) in the
     * instrument, or null when it cannot be determined.
     */
    public static function fieldOf(?int $instrumentId, ?int $eyepieceId, ?int $lensId): ?float
    {
        $instrument = $instrumentId ? Instrument::find($instrumentId) : null;
        if (! $instrument) {
            return null;
        }

        return $instrument->trueFieldOfViewDeg(
            $eyepieceId ? Eyepiece::find($eyepieceId) : null,
            $lensId ? Lens::find($lensId) : null,
        );
    }

    /** The angular distance in degrees (RA in hours, declination in degrees), flat-sky. */
    private static function separation(float $ra1, float $decl1, float $ra2, float $decl2): float
    {
        $dRa = ($ra2 - $ra1) * 15 * cos(deg2rad(($decl1 + $decl2) / 2));

        return sqrt($dRa ** 2 + ($decl2 - $decl1) ** 2);
    }
}
