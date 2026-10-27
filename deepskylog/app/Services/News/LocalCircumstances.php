<?php

namespace App\Services\News;

use App\Models\Location;
use App\Models\NewsItem;
use Illuminate\Support\Facades\Cache;

/**
 * How an astronomical event of the news looks from a location: the local
 * circumstances of an eclipse, the best moment to see a conjunction (the
 * moment closest to it with the bodies at least MIN_ALTITUDE high in a dark
 * enough sky), whether the Moon covers a planet there, how high a planet at
 * opposition gets, the altitude of Mercury or Venus at the end or start of
 * the civil twilight around its greatest elongation.
 *
 * Moments are timestamps (UT); the views show them in the location's time
 * zone. Cached per item and location.
 */
class LocalCircumstances
{
    /** Degrees: the lowest altitude at which a conjunction is worth looking at. */
    public const MIN_ALTITUDE = 10.0;

    /** The Sun's altitude in the civil twilight, in which bright planets are seen. */
    private const CIVIL_TWILIGHT = -6.0;

    /** The Sun's altitude at the end of the nautical twilight, dark enough for a cluster or galaxy. */
    private const NAUTICAL_TWILIGHT = -12.0;

    /**
     * @return array<string, mixed>|null
     */
    public static function for(NewsItem $item, ?Location $location): ?array
    {
        if (! $item->isAutomatic() || $location === null || ! $location->hasValidCoordinates() || $item->event_at === null) {
            return null;
        }

        $latitude  = round((float) $location->latitude, 2);
        $longitude = round((float) $location->longitude, 2);
        $key       = 'news_local:v1:' . $item->id . ':' . $item->updated_at?->timestamp . ':' . $latitude . ':' . $longitude;

        return Cache::remember($key, now()->addDays(14), function () use ($item, $latitude, $longitude, $location) {
            try {
                return self::compute($item->kind, $item->event_at->timestamp, $item->data ?? [], $latitude, $longitude, max(0.0, (float) ($location->elevation ?? 0)));
            } catch (\Throwable $e) {
                report($e);

                return null;
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public static function compute(string $kind, int $at, array $data, float $latitude, float $longitude, float $height = 0.0): ?array
    {
        if (in_array($kind, ['planet_conjunction', 'moon_conjunction', 'deepsky_conjunction', 'moon_deepsky_conjunction'], true)) {
            return self::conjunction($kind, $at, $data, $latitude, $longitude, $height);
        }

        return match ($kind) {
            'solar_eclipse'       => ['eclipse' => Eclipses::localSolar($at, $latitude, $longitude, $height)],
            'lunar_eclipse'       => ['altitudes' => Eclipses::localLunar($data['contacts'] ?? [], $latitude, $longitude)],
            'opposition'          => ['altitude' => round(90.0 - abs($latitude - (float) ($data['decl'] ?? 0)), 0)],
            'greatest_elongation' => self::elongation($at, $data, $latitude, $longitude),
            'comet'               => self::comet($at, $data, $latitude, $longitude),
            // Highest in a dark (nautical) sky from the coming night on, as a comet far from the Sun
            'supernova', 'nova'   => self::comet(max($at, time()), ['elongation' => 90.0] + $data, $latitude, $longitude),
            default               => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function conjunction(string $kind, int $at, array $data, float $latitude, float $longitude, float $height): array
    {
        $sunLimit = in_array($kind, ['deepsky_conjunction', 'moon_deepsky_conjunction'], true) ? self::NAUTICAL_TWILIGHT : self::CIVIL_TWILIGHT;
        $best     = null;
        for ($t = $at - 30 * 3600; $t <= $at + 30 * 3600; $t += 600) {
            $sample = self::bodies($kind, $data, $t, $latitude, $longitude, $height);
            if ($sample['sun'] > $sunLimit || min($sample['altitudes']) < self::MIN_ALTITUDE) {
                continue;
            }
            if ($best === null || abs($t - $at) < abs($best['at'] - $at)) {
                $best = $sample + ['at' => $t];
            }
        }

        $result = ['best' => $best === null ? null : [
            'at'         => $best['at'],
            'altitude'   => round(min($best['altitudes']), 0),
            'separation' => round($best['separation'], 2),
        ]];

        if (in_array($kind, ['moon_conjunction', 'moon_deepsky_conjunction'], true)) {
            $result['occultation'] = self::occultation($kind, $data, $at, $latitude, $longitude, $height);
        }

        return $result;
    }

    /**
     * When the Moon covers the planet (or the centre of the Messier object) as
     * seen from the place: the moments it disappears and reappears (to two
     * minutes) and the Moon's altitude and the Sun's then, or null.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    private static function occultation(string $kind, array $data, int $at, float $latitude, float $longitude, float $height): ?array
    {
        $begin    = $end = null;
        $altitude = $sun = null;
        for ($t = $at - 5 * 3600; $t <= $at + 5 * 3600; $t += 120) {
            $sample = self::bodies($kind, $data, $t, $latitude, $longitude, $height);
            if ($sample['separation'] < $sample['moon_radius']) {
                $begin ??= $t;
                $end = $t;
                if ($altitude === null) {
                    $altitude = round($sample['altitudes'][0], 0);
                    $sun      = round($sample['sun'], 0);
                }
            }
        }

        return $begin === null ? null : ['begin' => $begin, 'end' => $end, 'altitude' => $altitude, 'sun' => $sun, 'visible' => $altitude > 0];
    }

    /**
     * The altitudes of the bodies of a conjunction and of the Sun, and their
     * distance (topocentric for the Moon).
     *
     * @param  array<string, mixed>  $data
     * @return array{sun: float, altitudes: list<float>, separation: float, moon_radius: float}
     */
    private static function bodies(string $kind, array $data, int $t, float $latitude, float $longitude, float $height): array
    {
        $positions  = [];
        $moonRadius = 0.0;
        foreach ($data['bodies'] ?? [] as $body) {
            if ($body === 'moon') {
                $moon        = SkyPositions::moon($t);
                $equatorial  = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $t);
                $positions[] = SkyPositions::topocentric($equatorial['ra'], $equatorial['dec'], $moon['distance'], $latitude, $longitude, $height, $t);
                $moonRadius  = 358473400.0 / $moon['distance'] / 3600.0;
            } else {
                $planet      = SkyPositions::planet($body, $t);
                $positions[] = SkyPositions::toEquatorial($planet['lon'], $planet['lat'], $t);
            }
        }
        if (in_array($kind, ['deepsky_conjunction', 'moon_deepsky_conjunction'], true)) {
            // The object's J2000 position moved to the equinox of the date, as the planets'.
            $ecliptical  = SkyPositions::eclipticalOfDate((float) $data['object']['ra'], (float) $data['object']['decl'], $t);
            $positions[] = SkyPositions::toEquatorial($ecliptical['lon'], $ecliptical['lat'], $t);
        }

        $sun   = SkyPositions::sun($t);
        $sunEq = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);

        return [
            'sun'         => SkyPositions::altitude($sunEq['ra'], $sunEq['dec'], $latitude, $longitude, $t),
            'altitudes'   => array_map(fn ($p) => SkyPositions::altitude($p['ra'], $p['dec'], $latitude, $longitude, $t), $positions),
            'separation'  => SkyPositions::separation($positions[0]['ra'], $positions[0]['dec'], $positions[1]['ra'], $positions[1]['dec']),
            'moon_radius' => $moonRadius,
        ];
    }

    /**
     * The highest a comet gets in a dark enough sky (the civil twilight
     * when it is within 45 degrees of the Sun, else the nautical) around the
     * day it is brightest.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function comet(int $at, array $data, float $latitude, float $longitude): array
    {
        $sunLimit = abs((float) ($data['elongation'] ?? 0)) < 45 ? self::CIVIL_TWILIGHT : self::NAUTICAL_TWILIGHT;
        $best     = null;
        for ($t = $at - 12 * 3600; $t <= $at + 36 * 3600; $t += 600) {
            $sun = SkyPositions::sun($t);
            $equ = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);
            if (SkyPositions::altitude($equ['ra'], $equ['dec'], $latitude, $longitude, $t) > $sunLimit) {
                continue;
            }
            $altitude = SkyPositions::altitude((float) $data['ra'], (float) $data['decl'], $latitude, $longitude, $t);
            if ($best === null || $altitude > $best['altitude']) {
                $best = ['at' => $t, 'altitude' => $altitude];
            }
        }

        return ['best' => $best === null || $best['altitude'] < self::MIN_ALTITUDE ? null : [
            'at'       => $best['at'],
            'altitude' => round($best['altitude'], 0),
        ]];
    }

    /**
     * The altitude of Mercury or Venus when the Sun is 6 degrees below the
     * horizon, after sunset at an eastern elongation and before sunrise at a
     * western one, on the twilight closest to the greatest elongation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function elongation(int $at, array $data, float $latitude, float $longitude): array
    {
        $evening  = ($data['side'] ?? 'east') === 'east';
        $planet   = $data['bodies'][0] ?? 'venus';
        $previous = null;
        $best     = null;
        for ($t = $at - 24 * 3600; $t <= $at + 24 * 3600; $t += 300) {
            $sun      = SkyPositions::sun($t);
            $equa     = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);
            $altitude = SkyPositions::altitude($equa['ra'], $equa['dec'], $latitude, $longitude, $t);
            if ($previous !== null) {
                $crossed = $evening
                    ? $previous > self::CIVIL_TWILIGHT && $altitude <= self::CIVIL_TWILIGHT
                    : $previous < self::CIVIL_TWILIGHT && $altitude >= self::CIVIL_TWILIGHT;
                if ($crossed && ($best === null || abs($t - $at) < abs($best - $at))) {
                    $best = $t;
                }
            }
            $previous = $altitude;
        }
        if ($best === null) {
            return ['twilight' => null];
        }

        $position = SkyPositions::planet($planet, $best);
        $equa     = SkyPositions::toEquatorial($position['lon'], $position['lat'], $best);

        return ['twilight' => [
            'at'       => $best,
            'altitude' => round(SkyPositions::altitude($equa['ra'], $equa['dec'], $latitude, $longitude, $best), 0),
        ]];
    }
}
