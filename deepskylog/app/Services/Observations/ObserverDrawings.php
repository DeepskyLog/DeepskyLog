<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The drawings of one deep-sky object by one observer, oldest to newest, with
 * the aperture and sky of each, so that the page
 * /object/{slug}/drawings/observer/{observer} shows how the observer's view
 * of the object grew over the years. DrawingComparison does the same per
 * aperture class for all observers.
 */
class ObserverDrawings
{
    /** Drawings read for an observer and object; the oldest ones when there are more. */
    public const MAX_DRAWINGS = 200;

    /** Observers listed on the comparison page. */
    public const MAX_OBSERVERS = 50;

    /**
     * The observer's drawings of the object, oldest first. Each carries
     * drawing_aperture_mm, drawing_sqm and drawing_nelm (the observation's
     * own sky, else its location's, as ApertureRecords::skySql()) and
     * drawing_sky_from_location.
     *
     * @return Collection<int, Observation>
     */
    public function forObserver(string $objectName, User $observer): Collection
    {
        return Observation::ofDeepskyObject($objectName)
            ->where('observations.observer_username', $observer->username)
            ->where('observations.has_drawing', 1)
            ->leftJoin('instruments', 'instruments.id', '=', 'observations.instrument_id')
            ->leftJoin('locations', 'locations.id', '=', 'observations.location_id')
            ->select('observations.*', 'instruments.aperture_mm as drawing_aperture_mm')
            ->selectRaw(ApertureRecords::skySql('sqm').' AS drawing_sqm')
            ->selectRaw(ApertureRecords::skySql('nelm').' AS drawing_nelm')
            ->selectRaw('CASE WHEN '.ApertureRecords::ownSkySql().' THEN 0 ELSE 1 END AS drawing_sky_from_location')
            ->orderBy('observations.observed_on')
            ->orderBy('observations.observed_time')
            ->orderBy('observations.id')
            ->limit(self::MAX_DRAWINGS)
            ->get();
    }

    /** The number of drawings of the object by the observer. */
    public function count(string $objectName, string $username): int
    {
        return Observation::ofDeepskyObject($objectName)
            ->where('observations.observer_username', $username)
            ->where('observations.has_drawing', 1)
            ->count();
    }

    /**
     * The observers with at least two drawings of the object, most drawings
     * first: username => number of drawings.
     *
     * @return array<string, int>
     */
    public function observersWithSeveral(string $objectName): array
    {
        return Observation::ofDeepskyObject($objectName)
            ->where('observations.has_drawing', 1)
            ->groupBy('observations.observer_username')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('drawings')
            ->orderBy('observations.observer_username')
            ->limit(self::MAX_OBSERVERS)
            ->select('observations.observer_username', DB::raw('COUNT(*) AS drawings'))
            ->pluck('drawings', 'observer_username')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
