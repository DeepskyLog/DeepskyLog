<?php

namespace App\Services;

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Time;

/**
 * The dark part of a night at a location: from the end of astronomical
 * twilight in the evening to its begin the next morning, or, when it never
 * gets that dark or only for less than MIN_ASTRONOMICAL_MINUTES, between the
 * ends of nautical twilight. Without that minimum the last nights before the
 * summer solstice with astronomical darkness would have a window of half an
 * hour, and the next night one of hours; $astronomical keeps the short dark
 * part of such a nautical window.
 *
 * The local sidereal time over the window gives the highest altitude a fixed
 * point of the sky reaches in it, without stepping through the night.
 */
class NightWindow
{
    /** The local sidereal time advances this many degrees a day. */
    private const SIDEREAL_DEGREES_PER_DAY = 360.98564736629;

    /** A shorter astronomically dark window gives way to the nautical one. */
    public const MIN_ASTRONOMICAL_MINUTES = 120;

    /**
     * @param  array{0: int, 1: int}|null  $astronomical  the astronomically dark part (timestamps) of a nautical window
     */
    private function __construct(
        public readonly int $start,
        public readonly int $end,
        public readonly string $twilight,
        public readonly float $latitude,
        public readonly float $lstStart,
        public readonly ?array $astronomical = null,
    ) {
    }

    /**
     * The window of the night that starts on the evening of $night (Y-m-d in
     * $timezone), or null when it does not get darker than nautical twilight.
     * With $twilights ['nautical'] the window runs between the ends of
     * nautical twilight (the Sun 12° below the horizon) in any case. A
     * window shorter than MIN_ASTRONOMICAL_MINUTES gives way to the next
     * twilight of the list, when there is one.
     *
     * @param  list<string>  $twilights  the twilights tried, darkest first
     */
    public static function for(float $latitude, float $longitude, string $night, string $timezone, array $twilights = ['astronomical', 'nautical']): ?self
    {
        $evening  = Carbon::parse($night.' 12:00', $timezone);
        $today    = date_sun_info($evening->getTimestamp(), $latitude, $longitude);
        $tomorrow = date_sun_info($evening->copy()->addDay()->getTimestamp(), $latitude, $longitude);

        $astronomical = null;
        foreach (array_values($twilights) as $i => $twilight) {
            $start = $today[$twilight.'_twilight_end']      ?? null;
            $end   = $tomorrow[$twilight.'_twilight_begin'] ?? null;
            if (! is_int($start) || ! is_int($end) || $end <= $start) {
                continue;
            }
            if ($twilight === 'astronomical' && $i < count($twilights) - 1 && $end - $start < self::MIN_ASTRONOMICAL_MINUTES * 60) {
                $astronomical = [$start, $end];

                continue;
            }

            return self::span($latitude, $longitude, $start, $end, $twilight, $astronomical);
        }

        return null;
    }

    /**
     * The window between two moments (timestamps) at the location, however
     * dark the sky is then, such as the hours an observer chose; $twilight
     * is 'hours' then.
     *
     * @param  array{0: int, 1: int}|null  $astronomical
     */
    public static function span(float $latitude, float $longitude, int $start, int $end, string $twilight = 'hours', ?array $astronomical = null): self
    {
        $sidereal = Time::meanSiderialTime(Carbon::createFromTimestamp($start, 'UTC'), new GeographicalCoordinates($longitude, $latitude));

        return new self($start, $end, $twilight, $latitude, ($sidereal->hour + $sidereal->minute / 60 + $sidereal->second / 3600) * 15.0, $astronomical);
    }

    /**
     * The part of the window between two moments (timestamps), or null when
     * they do not overlap it.
     */
    public function between(int $start, int $end): ?self
    {
        $start = max($start, $this->start);
        $end   = min($end, $this->end);

        return $end > $start ? new self($start, $end, $this->twilight, $this->latitude, $this->siderealTime($start), $this->astronomical) : null;
    }

    /**
     * The local sidereal time in degrees at the moment (a timestamp).
     */
    public function siderealTime(int $timestamp): float
    {
        return $this->lstStart + self::SIDEREAL_DEGREES_PER_DAY * ($timestamp - $this->start) / 86400.0;
    }

    /**
     * The highest altitude (degrees, without refraction) of the point of the
     * sky during the window and the moment (a timestamp) it is reached: at
     * the transit when that falls in the window, else at its start or end.
     *
     * @return array{altitude: float, timestamp: int}
     */
    public function highest(float $raDeg, float $decDeg): array
    {
        $span = $this->siderealTime($this->end) - $this->lstStart;
        // The hour angle at the start, in [0, 360): the transit (a multiple of
        // 360) is the first one after it.
        $hourAngle = fmod(fmod($this->lstStart - $raDeg, 360.0) + 360.0, 360.0);
        if ($hourAngle + $span >= 360.0) {
            $timestamp = $this->start + (int) round((360.0 - $hourAngle) / $span * ($this->end - $this->start));
        } else {
            $timestamp = cos(deg2rad($hourAngle)) >= cos(deg2rad($hourAngle + $span)) ? $this->start : $this->end;
        }

        return ['altitude' => $this->altitude($raDeg, $decDeg, $timestamp), 'timestamp' => $timestamp];
    }

    /**
     * The altitude in degrees of the point of the sky at the moment.
     */
    public function altitude(float $raDeg, float $decDeg, int $timestamp): float
    {
        $hourAngle = deg2rad($this->siderealTime($timestamp) - $raDeg);
        $dec       = deg2rad($decDeg);
        $lat       = deg2rad($this->latitude);

        return rad2deg(asin(max(-1.0, min(1.0, sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($hourAngle)))));
    }
}
