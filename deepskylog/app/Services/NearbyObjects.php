<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The deep-sky objects near a position: for the coordinate check of a new
 * object and the new targets of an OAL import (#221).
 */
class NearbyObjects
{
    /**
     * The objects in a box around a position (RA in hours, declination in
     * degrees), $radius degrees in declination and wide enough in RA to hold
     * the circle of that radius, nearest first. The caller selects the
     * columns.
     */
    public static function query(float $raHours, float $decl, float $radius): Builder
    {
        // An hour of RA spans 15 cos(dec) degrees, so the RA range widens towards the poles.
        // The declination of the box edge closest to the pole gives the widest range needed.
        $edgeDec = min(90.0, abs($decl) + $radius);
        $cosEdgeDec = cos(deg2rad($edgeDec));
        $raRadius = $cosEdgeDec > 1e-6 ? $radius / (15.0 * $cosEdgeDec) : 12.0; // hours

        // Squared angular distance in degrees, with the RA difference taken the short way around 0/24 h
        $raDiff = '(CASE WHEN ABS(objects.ra - ?) > 12 THEN 24 - ABS(objects.ra - ?) ELSE ABS(objects.ra - ?) END) * ?';
        $raScale = 15.0 * cos(deg2rad($decl));
        $distanceBindings = [$raHours, $raHours, $raHours, $raScale];

        return DB::table('objects')
            ->when($raRadius < 12.0, function ($query) use ($raHours, $raRadius) {
                $min = $raHours - $raRadius;
                $max = $raHours + $raRadius;

                if ($min < 0.0) {
                    $query->where(fn ($q) => $q->where('objects.ra', '>=', $min + 24.0)->orWhere('objects.ra', '<=', $max));
                } elseif ($max > 24.0) {
                    $query->where(fn ($q) => $q->where('objects.ra', '>=', $min)->orWhere('objects.ra', '<=', $max - 24.0));
                } else {
                    $query->whereBetween('objects.ra', [$min, $max]);
                }
            })
            ->whereBetween('objects.decl', [$decl - $radius, $decl + $radius])
            ->orderByRaw(
                "($raDiff) * ($raDiff) + (objects.decl - ?) * (objects.decl - ?)",
                [...$distanceBindings, ...$distanceBindings, $decl, $decl]
            );
    }

    /**
     * The objects within $radius degrees of a position (RA in hours,
     * declination in degrees), nearest first.
     *
     * @return list<array{name: string, slug: string, type: string, type_label: string, diameter: float|null, separation: float}>
     *               diameter (the major axis) and separation in arcminutes
     */
    public static function within(float $raHours, float $decl, float $radius, int $limit): array
    {
        return self::query($raHours, $decl, $radius)
            ->leftJoin('target_types', 'objects.type', '=', 'target_types.id')
            ->limit($limit)
            ->get(['objects.name', 'objects.slug', 'objects.type', 'objects.ra', 'objects.decl', 'objects.diam1', 'target_types.type as type_label'])
            ->map(fn ($o) => [
                'name' => $o->name,
                'slug' => (string) $o->slug,
                'type' => (string) $o->type,
                'type_label' => (string) ($o->type_label ?? $o->type),
                'diameter' => $o->diam1 > 0 ? $o->diam1 / 60 : null,
                'separation' => round(MoonlightSky::separation($raHours * 15, $decl, $o->ra * 15, (float) $o->decl) * 60, 1),
            ])
            // The corners of the box are farther than the radius
            ->filter(fn (array $o) => $o['separation'] <= $radius * 60)
            ->values()
            ->all();
    }
}
