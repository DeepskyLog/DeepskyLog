<?php

namespace App\Services\News;

use App\Services\CometCoordinates;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The comets the news announces (#960): those whose predicted magnitude
 * (CometCoordinates, from the orbital elements and light curves of
 * comets_orbital_elements) reaches MAX_MAGNITUDE while they are at least
 * MIN_ELONGATION from the Sun. The event is the day the comet is brightest
 * while it can be seen: the peak of its light curve, or the last (first) day
 * before (after) it disappears in the glare of the Sun.
 *
 * Comets without a plausible H and coefficient of log r have no predicted
 * magnitude (CometCoordinates::photometry()) and are never announced.
 */
class BrightComets
{
    /** The faintest predicted magnitude announced: a comet for binoculars. */
    public const MAX_MAGNITUDE = 8.0;

    /** Degrees from the Sun a comet needs to be seen in the twilight. */
    public const MIN_ELONGATION = 20.0;

    private const STEP_DAYS = 1;

    /**
     * The comets at their brightest between two moments (timestamps, UT): one
     * event per comet, perihelion passage and side of the Sun (morning or
     * evening sky).
     *
     * @return list<array{key: string, kind: string, at: int, data: array<string, mixed>}>
     */
    public static function between(int $from, int $to): array
    {
        if (! Schema::hasTable('comets_orbital_elements')) {
            return [];
        }

        $days = [];
        for ($t = $from - 86400 * self::STEP_DAYS; $t <= $to + 86400 * self::STEP_DAYS; $t += 86400 * self::STEP_DAYS) {
            $days[] = $t;
        }

        $events = [];
        $pages  = null;
        foreach (DB::table('comets_orbital_elements')->whereNotNull('H')->get() as $elements) {
            if (! ($target = CometCoordinates::target($elements))) {
                continue;
            }

            // The magnitudes first: they are cheap, the positions are not.
            $magnitudes = [];
            foreach ($days as $t) {
                $magnitude = CometCoordinates::magnitude($target, Carbon::createFromTimestamp($t, 'UTC'));
                // Some orbits give no number (NaN), which no comparison would catch
                $magnitudes[] = $magnitude !== null && is_finite($magnitude) ? $magnitude : null;
            }
            $known = array_filter($magnitudes, fn ($m) => $m !== null);
            if ($known === [] || min($known) > self::MAX_MAGNITUDE) {
                continue;
            }

            // Not seen when too close to the Sun: an infinite magnitude.
            $seen = [];
            foreach ($days as $i => $t) {
                $seen[$i] = $magnitudes[$i] === null || $magnitudes[$i] > self::MAX_MAGNITUDE + 1
                    ? INF
                    : (abs(self::position($target, $t)['elongation']) >= self::MIN_ELONGATION ? $magnitudes[$i] : INF);
            }

            // The brightest moment on each side of the Sun: a comet can be
            // seen in the morning sky before its perihelion and in the
            // evening sky after it, or the other way round.
            $peaks = [];
            for ($i = 1; $i < count($days) - 1; $i++) {
                $m = $seen[$i];
                if ($m > self::MAX_MAGNITUDE || $m > $seen[$i - 1] || $m >= $seen[$i + 1] || $days[$i] < $from || $days[$i] >= $to) {
                    continue;
                }
                $position = self::position($target, $days[$i]);
                $side     = $position['elongation'] > 0 ? 'evening' : 'morning';
                if (! isset($peaks[$side]) || $m < $peaks[$side]['magnitude']) {
                    $peaks[$side] = ['at' => $days[$i], 'magnitude' => $m, 'position' => $position];
                }
            }
            if ($peaks === []) {
                continue;
            }

            $perihelion = CometCoordinates::perihelionDate($elements->Tp);
            $pages ??= self::cometPages();
            [$designation] = CometCoordinates::splitName((string) $elements->name);
            foreach ($peaks as $side => $peak) {
                $events[] = [
                    'key'  => 'comet:' . Str::slug((string) $elements->name) . ':' . $perihelion?->format('Y-m-d') . ':' . $side,
                    'kind' => 'comet',
                    'at'   => $peak['at'],
                    'data' => [
                        'name'       => (string) $elements->name,
                        'slug'       => $pages[$designation ?? mb_strtolower((string) $elements->name)] ?? null,
                        'magnitude'  => round($peak['magnitude'], 1),
                        'elongation' => round($peak['position']['elongation'], 1),
                        'ra'         => round($peak['position']['ra'], 4),
                        'decl'       => round($peak['position']['dec'], 4),
                        'perihelion' => $perihelion?->timestamp,
                        'q'          => round((float) $elements->q, 3),
                    ],
                ];
            }
        }

        return $events;
    }

    /**
     * The position of the comet (J2000 degrees) and its distance from the
     * Sun, positive east of the Sun (evening sky).
     *
     * @return array{ra: float, dec: float, elongation: float}
     */
    private static function position(object $target, int $t): array
    {
        $coordinates = CometCoordinates::calculate($target, Carbon::createFromTimestamp($t, 'UTC'), new GeographicalCoordinates(0.0, 0.0));
        $ra          = (float) $coordinates->getRA()->getCoordinate() * 15.0;
        $dec         = (float) $coordinates->getDeclination()->getCoordinate();

        $sun        = SkyPositions::sun($t);
        $sunEq      = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);
        $distance   = SkyPositions::separation($sunEq['ra'], $sunEq['dec'], $ra, $dec);
        $ecliptical = SkyPositions::eclipticalOfDate($ra, $dec, $t);

        return [
            'ra'         => $ra,
            'dec'        => $dec,
            'elongation' => SkyPositions::wrap180($ecliptical['lon'] - $sun['lon']) >= 0 ? $distance : -$distance,
        ];
    }

    /**
     * The slugs of the comets DeepskyLog has a page for, keyed by their
     * designation (or lower-case name when they have none).
     *
     * @return array<string, string>
     */
    private static function cometPages(): array
    {
        $pages = [];
        foreach (DB::table('cometobjects')->whereNotNull('slug')->get(['name', 'slug']) as $comet) {
            $name          = html_entity_decode((string) $comet->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            [$designation] = CometCoordinates::splitName($name);
            $pages[$designation ?? mb_strtolower($name)] ??= (string) $comet->slug;
        }

        return $pages;
    }
}
