<?php

namespace App\Services;

use App\Models\Location;

/**
 * The local horizon of a location: the altitude of the trees, houses and
 * hills around it every STEP degrees of azimuth (from the north through the
 * east), as the observer entered it on the location page
 * (locations.horizon, POINTS values; null for an open horizon). Between the
 * points the altitude is interpolated linearly.
 *
 * A point of the sky is clear when it stands at least as high as the
 * horizon in its direction and the planner's own lowest altitude. The
 * planners (NightPlanner, SeasonalVisibility, TonightsBest, MoonlightSky)
 * compute when a point crosses a fixed altitude from the hour angle; with a
 * horizon that only ever raises the limit, so they ask blocksAbove() first
 * and only then take the clear parts of the night from here.
 *
 * Whether a point is clear depends on the latitude, its declination and its
 * hour angle only: clearHourAngles() samples the hour angle every
 * HOUR_ANGLE_STEP degrees (4 minutes) for the declination rounded to
 * DEC_STEP, and remembers it, so a list of thousands of objects takes a
 * fraction of a second.
 */
final class LocalHorizon
{
    /** Degrees of azimuth between two points of the horizon. */
    public const STEP = 15;

    public const POINTS = 360 / self::STEP;

    /** The highest altitude (degrees) a point of the horizon can have. */
    public const MAX_ALTITUDE = 80;

    /** Degrees of hour angle between two samples of clearHourAngles(). */
    private const HOUR_ANGLE_STEP = 1.0;

    /** The declination is rounded to this (degrees) for clearHourAngles(). */
    private const DEC_STEP = 0.2;

    /** The highest point of the horizon. */
    public readonly float $max;

    /** @var array<string, list<array{0: float, 1: float}>> */
    private array $clear = [];

    /**
     * @param  list<float>  $altitudes  POINTS altitudes, from azimuth 0 (north) every STEP degrees
     */
    private function __construct(public readonly array $altitudes)
    {
        $this->max = max($altitudes);
    }

    /**
     * The horizon of POINTS altitudes, or null when there is none or it is
     * open all around.
     */
    public static function fromArray(?array $altitudes): ?self
    {
        $altitudes = self::normalize($altitudes);

        return $altitudes === null ? null : new self($altitudes);
    }

    public static function forLocation(?Location $location): ?self
    {
        return $location ? self::fromArray($location->horizon) : null;
    }

    /**
     * POINTS altitudes in [0, MAX_ALTITUDE], rounded to whole degrees, or
     * null for an open horizon (all 0) or a list of another length.
     *
     * @return list<float>|null
     */
    public static function normalize(?array $altitudes): ?array
    {
        if ($altitudes === null || count($altitudes) !== self::POINTS) {
            return null;
        }
        $altitudes = array_map(fn ($value) => (float) max(0, min(self::MAX_ALTITUDE, (int) round(is_numeric($value) ? (float) $value : 0.0))), array_values($altitudes));

        return max($altitudes) > 0.0 ? $altitudes : null;
    }

    /**
     * The POINTS altitudes of a horizon written as lines of an azimuth and an
     * altitude in degrees (the horizon.txt of a Stellarium landscape, or a
     * list from a horizon app), separated by spaces, tabs, commas or
     * semicolons; lines starting with # or ; are comments. Between the
     * points (in any order, going round) the altitude is interpolated.
     * Null when there are fewer than two points.
     *
     * @return list<float>|null
     */
    public static function parse(string $text): ?array
    {
        $points = [];
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }
            $values = preg_split('/[\s,;]+/', $line);
            if (count($values) < 2 || ! is_numeric($values[0]) || ! is_numeric($values[1])) {
                continue;
            }
            $azimuth          = fmod(fmod((float) $values[0], 360.0) + 360.0, 360.0);
            $points[$azimuth] = (float) $values[1];
        }
        if (count($points) < 2) {
            return null;
        }
        ksort($points);
        $azimuths = array_keys($points);
        $values   = array_values($points);
        $count    = count($azimuths);

        $altitudes = [];
        for ($i = 0; $i < self::POINTS; $i++) {
            $azimuth = $i * self::STEP;
            // The points on either side, going round through north.
            $after = 0;
            while ($after < $count && $azimuths[$after] < $azimuth) {
                $after++;
            }
            $next     = $after                % $count;
            $previous = ($after - 1 + $count) % $count;
            $from     = $azimuths[$previous] - ($azimuths[$previous] > $azimuth ? 360.0 : 0.0);
            $to       = $azimuths[$next] + ($azimuths[$next] < $azimuth ? 360.0 : 0.0);
            $fraction = $to > $from ? ($azimuth - $from) / ($to - $from) : 0.0;

            $altitudes[] = (float) max(0, min(self::MAX_ALTITUDE, (int) round($values[$previous] + $fraction * ($values[$next] - $values[$previous]))));
        }

        return $altitudes;
    }

    /**
     * The altitude (degrees) of the horizon at the azimuth (degrees from the
     * north through the east).
     */
    public function altitudeAt(float $azimuth): float
    {
        $position = fmod(fmod($azimuth, 360.0) + 360.0, 360.0) / self::STEP;
        $index    = (int) floor($position);
        $fraction = $position - $index;

        return $this->altitudes[$index % self::POINTS] * (1.0 - $fraction) + $this->altitudes[($index + 1) % self::POINTS] * $fraction;
    }

    /**
     * Whether the horizon rises above the altitude somewhere: only then does
     * it change what a planner with that lowest altitude finds.
     */
    public function blocksAbove(float $minAltitude): bool
    {
        return $this->max > $minAltitude;
    }

    /**
     * Altitude and azimuth (degrees, the azimuth from the north through the
     * east) of a point of the sky at the hour angle.
     *
     * @return array{0: float, 1: float}
     */
    public static function horizontal(float $latitude, float $hourAngleDeg, float $decDeg): array
    {
        $lat = deg2rad($latitude);
        $ha  = deg2rad($hourAngleDeg);
        $dec = deg2rad($decDeg);

        $altitude = asin(max(-1.0, min(1.0, sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($ha))));
        $azimuth  = atan2(-cos($dec) * sin($ha), sin($dec) * cos($lat) - cos($dec) * sin($lat) * cos($ha));

        return [rad2deg($altitude), fmod(rad2deg($azimuth) + 360.0, 360.0)];
    }

    /**
     * Whether the point stands above the horizon in its direction and at
     * least $minAltitude high at the hour angle.
     */
    public function isClear(float $latitude, float $hourAngleDeg, float $decDeg, float $minAltitude = 0.0): bool
    {
        [$altitude, $azimuth] = self::horizontal($latitude, $hourAngleDeg, $decDeg);

        return $altitude >= max($minAltitude, $this->altitudeAt($azimuth));
    }

    /**
     * The hour angles (degrees, in [-180, 180]) at which a point of the
     * declination stands above the horizon and at least $minAltitude high,
     * as intervals; the crossings interpolated between the samples.
     *
     * @return list<array{0: float, 1: float}>
     */
    public function clearHourAngles(float $latitude, float $decDeg, float $minAltitude): array
    {
        $dec = round($decDeg / self::DEC_STEP) * self::DEC_STEP;
        $key = $latitude.'|'.$dec.'|'.$minAltitude;
        if (isset($this->clear[$key])) {
            return $this->clear[$key];
        }

        $margin = function (float $hourAngle) use ($latitude, $dec, $minAltitude): float {
            [$altitude, $azimuth] = self::horizontal($latitude, $hourAngle, $dec);

            return $altitude - max($minAltitude, $this->altitudeAt($azimuth));
        };

        $intervals = [];
        $start     = null;
        $previous  = null;
        for ($hourAngle = -180.0; $hourAngle <= 180.0 + 1e-9; $hourAngle += self::HOUR_ANGLE_STEP) {
            $value = $margin($hourAngle);
            if ($previous !== null && ($value >= 0.0) !== ($previous[1] >= 0.0)) {
                // The crossing between the two samples.
                $crossing = $previous[0] + self::HOUR_ANGLE_STEP * $previous[1] / ($previous[1] - $value);
                if ($value >= 0.0) {
                    $start = $crossing;
                } else {
                    $intervals[] = [$start, $crossing];
                    $start       = null;
                }
            } elseif ($previous === null && $value >= 0.0) {
                $start = -180.0;
            }
            $previous = [$hourAngle, $value];
        }
        if ($start !== null) {
            $intervals[] = [$start, 180.0];
        }

        return $this->clear[$key] = $intervals;
    }

    /**
     * The parts of the window (timestamps) the point (RA and declination in
     * degrees) stands above the horizon and at least $minAltitude high.
     *
     * @return list<array{0: int, 1: int}>
     */
    public function intervals(NightWindow $window, float $raDeg, float $decDeg, float $minAltitude): array
    {
        $span    = $window->siderealTime($window->end) - $window->lstStart;
        $seconds = $window->end                        - $window->start;
        $from    = fmod(fmod($window->lstStart - $raDeg, 360.0) + 360.0, 360.0);
        $to      = $from + $span;
        $moment  = fn (float $hourAngle) => $window->start + (int) round(($hourAngle - $from) / $span * $seconds);

        // The hour angle of the window runs from [0, 360) over less than a
        // day; the clear hour angles in [-180, 180] meet it shifted by up to
        // two turns.
        $intervals = [];
        foreach ([0.0, 360.0, 720.0] as $shift) {
            foreach ($this->clearHourAngles($window->latitude, $decDeg, $minAltitude) as [$a, $b]) {
                $a = max($from, $a + $shift);
                $b = min($to, $b + $shift);
                if ($b > $a) {
                    $intervals[] = [$moment($a), $moment($b)];
                }
            }
        }
        usort($intervals, fn (array $x, array $y) => $x[0] <=> $y[0]);

        $merged = [];
        foreach ($intervals as $interval) {
            if ($merged !== [] && $interval[0] <= end($merged)[1] + 1) {
                $merged[count($merged) - 1][1] = max(end($merged)[1], $interval[1]);
            } else {
                $merged[] = $interval;
            }
        }

        return $merged;
    }

    /**
     * The hours of the window the point is clear (intervals()).
     */
    public function hours(NightWindow $window, float $raDeg, float $decDeg, float $minAltitude): float
    {
        return array_sum(array_map(fn (array $i) => $i[1] - $i[0], $this->intervals($window, $raDeg, $decDeg, $minAltitude))) / 3600.0;
    }

    /**
     * The highest altitude of the point while it is clear in the window, and
     * the moment (a timestamp); null when it never is.
     *
     * @return array{altitude: float, timestamp: int}|null
     */
    public function highest(NightWindow $window, float $raDeg, float $decDeg, float $minAltitude): ?array
    {
        $best = null;
        foreach ($this->intervals($window, $raDeg, $decDeg, $minAltitude) as [$from, $until]) {
            $part      = $window->between($from, $until);
            $candidate = $part ? $part->highest($raDeg, $decDeg) : ['altitude' => $window->altitude($raDeg, $decDeg, $from), 'timestamp' => $from];
            if ($best === null || $candidate['altitude'] > $best['altitude']) {
                $best = $candidate;
            }
        }

        return $best;
    }
}
