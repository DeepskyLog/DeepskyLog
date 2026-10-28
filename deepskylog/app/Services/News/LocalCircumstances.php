<?php

namespace App\Services\News;

use App\Models\Location;
use App\Models\NewsItem;
use App\Services\Planets\PlanetMoonEvents;
use Illuminate\Support\Facades\Cache;

/**
 * How an astronomical event of the news looks from a location: the local
 * circumstances of an eclipse, the best moment to see a conjunction (the
 * moment closest to it with the bodies at least MIN_ALTITUDE high in a dark
 * enough sky), whether the Moon covers a planet there, when the Moon covers
 * a planet or bright star there (lunarOccultation()), how high a planet at
 * opposition gets, the altitude of Mercury or Venus at the end or start of
 * the civil twilight around its greatest elongation, which part of the
 * shadows on Jupiter or Saturn can be seen.
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
            'lunar_occultation'   => self::lunarOccultation($at, $data, $latitude, $longitude, $height),
            'comet'               => self::comet($at, $data, $latitude, $longitude),
            // Highest in a dark (nautical) sky from the coming night on, as a comet far from the Sun
            'supernova', 'nova'                             => self::comet(max($at, time()), ['elongation' => 90.0] + $data, $latitude, $longitude),
            'double_shadow_transit', 'titan_shadow_transit' => ['visible' => PlanetMoonEvents::visibleDuring($data, $latitude, $longitude)],
            default                                         => null,
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
     * The disappearance and reappearance of the planet or star behind the
     * Moon seen from the place (to the second), each with the altitudes of the
     * Moon and the Sun and the limb (dark or bright) it happens at; null when
     * the Moon misses it there, with the best moment to see them together
     * (conjunction()).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function lunarOccultation(int $at, array $data, float $latitude, float $longitude, float $height): array
    {
        // The parallax shifts the moments seen from a place up to two hours from the geocentric ones.
        $step    = 120;
        $inside  = null;
        $contact = ['disappears' => null, 'reappears' => null];
        for ($t = $at - 4 * 3600; $t <= $at + 4 * 3600; $t += $step) {
            $sample = self::occultationSample($data, $t, $latitude, $longitude, $height);
            $now    = $sample['separation'] < $sample['moon_radius'];
            if ($inside !== null && $now !== $inside) {
                $moment = self::bisectContact($data, $t - $step, $t, $latitude, $longitude, $height);
                $kind   = $now ? 'disappears' : 'reappears';
                $contact[$kind] ??= self::contact($data, $moment, $latitude, $longitude, $height);
            }
            $inside = $now;
        }

        if ($contact['disappears'] === null && $contact['reappears'] === null) {
            return ['occultation' => null] + self::conjunction('lunar_occultation', $at, $data, $latitude, $longitude, $height);
        }

        $visible = array_filter($contact, fn ($c) => $c !== null && $c['moon_altitude'] > 0);

        return ['occultation' => $contact + ['visible' => $visible !== []], 'best' => null];
    }

    /** The moment (to a second) between two the target crosses the Moon's limb. */
    private static function bisectContact(array $data, int $before, int $after, float $latitude, float $longitude, float $height): int
    {
        $inside = fn (int $t) => ($s = self::occultationSample($data, $t, $latitude, $longitude, $height))['separation'] < $s['moon_radius'];
        $first  = $inside($before);
        while ($after - $before > 1) {
            $middle = intdiv($before + $after, 2);
            if ($inside($middle) === $first) {
                $before = $middle;
            } else {
                $after = $middle;
            }
        }

        return $after;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{at: int, moon_altitude: float, sun_altitude: float, limb: string}
     */
    private static function contact(array $data, int $t, float $latitude, float $longitude, float $height): array
    {
        $sample = self::occultationSample($data, $t, $latitude, $longitude, $height);

        return [
            'at'            => $t,
            'moon_altitude' => round($sample['moon_altitude'], 0),
            'sun_altitude'  => round($sample['sun_altitude'], 0),
            'limb'          => $sample['bright_limb'] ? 'bright' : 'dark',
        ];
    }

    /**
     * The topocentric distance of the target from the Moon's centre, the
     * Moon's radius, the altitudes of the Moon and the Sun, and whether the
     * target is on the lit side of the Moon: within 90 degrees of the position
     * angle of the bright limb (Meeus 48.5).
     *
     * @param  array<string, mixed>  $data
     * @return array{separation: float, moon_radius: float, moon_altitude: float, sun_altitude: float, bright_limb: bool}
     */
    private static function occultationSample(array $data, int $t, float $latitude, float $longitude, float $height): array
    {
        $moon     = SkyPositions::moon($t);
        $moonEq   = SkyPositions::toEquatorial($moon['lon'], $moon['lat'], $t);
        $moonTopo = SkyPositions::topocentric($moonEq['ra'], $moonEq['dec'], $moon['distance'], $latitude, $longitude, $height, $t);

        if (($data['target'] ?? 'star') === 'star') {
            // The star's J2000 position moved to the equinox of the date, as the Moon's.
            $ecliptical = SkyPositions::eclipticalOfDate((float) $data['star']['ra'], (float) $data['star']['decl'], $t);
        } else {
            $ecliptical = SkyPositions::planet($data['target'], $t);
        }
        $target = SkyPositions::toEquatorial($ecliptical['lon'], $ecliptical['lat'], $t);
        $sun    = SkyPositions::sun($t);
        $sunEq  = SkyPositions::toEquatorial($sun['lon'], $sun['lat'], $t);

        $positionAngle = fn (array $p) => rad2deg(atan2(
            cos(deg2rad($p['dec'])) * sin(deg2rad($p['ra'] - $moonTopo['ra'])),
            sin(deg2rad($p['dec'])) * cos(deg2rad($moonTopo['dec'])) - cos(deg2rad($p['dec'])) * sin(deg2rad($moonTopo['dec'])) * cos(deg2rad($p['ra'] - $moonTopo['ra'])),
        ));

        return [
            'separation'    => SkyPositions::separation($moonTopo['ra'], $moonTopo['dec'], $target['ra'], $target['dec']),
            'moon_radius'   => 358473400.0 / $moon['distance'] / 3600.0,
            'moon_altitude' => SkyPositions::altitude($moonTopo['ra'], $moonTopo['dec'], $latitude, $longitude, $t),
            'sun_altitude'  => SkyPositions::altitude($sunEq['ra'], $sunEq['dec'], $latitude, $longitude, $t),
            'bright_limb'   => abs(SkyPositions::wrap180($positionAngle($target) - $positionAngle($sunEq))) < 90.0,
        ];
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
        } elseif ($kind === 'lunar_occultation' && ($data['target'] ?? 'star') === 'star') {
            $ecliptical  = SkyPositions::eclipticalOfDate((float) $data['star']['ra'], (float) $data['star']['decl'], $t);
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
