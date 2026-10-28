<?php

namespace App\Services;

use App\Models\Location;
use App\Support\EphemerisDate;
use Carbon\Carbon;

/**
 * The months a fixed point of the sky is well placed from a location: for
 * the night of the 15th of each month, how long it stands at least
 * MIN_ALTITUDE high in the dark hours (NightWindow). It is well placed in a
 * month with at least MIN_HOURS of that. A point that never stays that long
 * that high but does rise is well placed in the months it transits, at its
 * highest altitude, in the dark hours.
 *
 * The time above the altitude follows from the hour angle at which the point
 * crosses it, so a list of thousands of objects takes a few milliseconds.
 * Planets, comets and asteroids move, so this is for deep-sky objects only.
 */
class SeasonalVisibility
{
    /** The altitude an object has to reach, as for Tonight's best. */
    public const MIN_ALTITUDE = TonightsBest::MIN_ALTITUDE;

    /** The hours above MIN_ALTITUDE in the dark that make a month count. */
    public const MIN_HOURS = 1.0;

    /** The hour angle advances this many degrees an hour. */
    private const SIDEREAL_DEGREES_PER_HOUR = 360.98564736629 / 24.0;

    /** The day of the month whose night stands for the month. */
    private const SAMPLE_DAY = 15;

    /** @var array<string, self> */
    private static array $instances = [];

    /**
     * @param  array<int, NightWindow|null>  $windows  the window of each month (1–12)
     */
    private function __construct(
        public readonly ?Location $location,
        public readonly float $latitude,
        public readonly int $currentMonth,
        private readonly array $windows,
    ) {
    }

    /**
     * The months for the viewer: their standard location, else the default
     * location of the ephemerides, in the year of the ephemeris date. Null
     * when there is no location with valid coordinates.
     */
    public static function forViewer(): ?self
    {
        $location = Location::forEphemerides(auth()->user());
        if (! $location || ! $location->hasValidCoordinates()) {
            return null;
        }

        $timezone = EphemerisDate::timezone($location);
        $date     = Carbon::parse(EphemerisDate::current($timezone), $timezone);

        return self::for($location, $date->year, $date->month, $timezone);
    }

    public static function for(Location $location, int $year, int $currentMonth, string $timezone): self
    {
        $key = implode('|', [$location->id, (float) $location->latitude, (float) $location->longitude, $year, $currentMonth, $timezone]);

        return self::$instances[$key] ??= self::build($location, (float) $location->latitude, (float) $location->longitude, $year, $currentMonth, $timezone);
    }

    /**
     * The months for a latitude and longitude, without a stored location.
     */
    public static function at(float $latitude, float $longitude, int $year, int $currentMonth, string $timezone = 'UTC'): self
    {
        return self::build(null, $latitude, $longitude, $year, $currentMonth, $timezone);
    }

    private static function build(?Location $location, float $latitude, float $longitude, int $year, int $currentMonth, string $timezone): self
    {
        $windows = [];
        for ($month = 1; $month <= 12; $month++) {
            $windows[$month] = NightWindow::for($latitude, $longitude, sprintf('%04d-%02d-%02d', $year, $month, self::SAMPLE_DAY), $timezone);
        }

        return new self($location, $latitude, $currentMonth, $windows);
    }

    /**
     * The hours the point (RA and declination in degrees) stands at least
     * MIN_ALTITUDE high in the dark hours of each month (1–12), null for a
     * month without dark hours.
     *
     * @return array<int, float|null>
     */
    public function hours(float $raDeg, float $decDeg): array
    {
        $hours = [];
        foreach ($this->windows as $month => $window) {
            $hours[$month] = $window ? $this->hoursAbove($window, $raDeg, $decDeg) : null;
        }

        return $hours;
    }

    /**
     * Per month (1–12): the hours above MIN_ALTITUDE in the dark hours (null
     * without dark hours), and whether the point is well placed.
     *
     * @return array<int, array{hours: float|null, well: bool}>
     */
    public function months(float $raDeg, float $decDeg): array
    {
        $months = [];
        foreach ($this->windows as $month => $window) {
            $months[$month] = $this->month($window, $raDeg, $decDeg);
        }

        return $months;
    }

    /**
     * The months (1–12) the point is well placed in.
     *
     * @return list<int>
     */
    public function wellPlacedMonths(float $raDeg, float $decDeg): array
    {
        return array_keys(array_filter($this->months($raDeg, $decDeg), fn (array $m) => $m['well']));
    }

    public function isWellPlaced(float $raDeg, float $decDeg, int $month): bool
    {
        return $this->month($this->windows[$month] ?? null, $raDeg, $decDeg)['well'];
    }

    /**
     * The highest altitude (degrees, at the transit) the point reaches at the
     * location.
     */
    public function highestAltitude(float $decDeg): float
    {
        return 90.0 - abs($this->latitude - $decDeg);
    }

    /**
     * Whether the point rises but is never MIN_HOURS above MIN_ALTITUDE, not
     * even in a long night: it is then well placed when it transits in the
     * dark hours.
     */
    public function isLow(float $decDeg): bool
    {
        if ($this->highestAltitude($decDeg) <= 0.0) {
            return false;
        }
        $limit = $this->limitHourAngle($this->latitude, $decDeg);

        return $limit === null || 2.0 * $limit / self::SIDEREAL_DEGREES_PER_HOUR < self::MIN_HOURS;
    }

    /**
     * The hour angle (degrees) up to which the point stands at least
     * MIN_ALTITUDE high: 180 when it always does, null when it never does.
     */
    private function limitHourAngle(float $latitude, float $decDeg): ?float
    {
        $lat = deg2rad($latitude);
        $dec = deg2rad($decDeg);
        $cosLimit = (sin(deg2rad(self::MIN_ALTITUDE)) - sin($lat) * sin($dec)) / max(1e-12, cos($lat) * cos($dec));
        if ($cosLimit >= 1.0) {
            return null;
        }

        return $cosLimit <= -1.0 ? 180.0 : rad2deg(acos($cosLimit));
    }

    /**
     * @return array{hours: float|null, well: bool}
     */
    private function month(?NightWindow $window, float $raDeg, float $decDeg): array
    {
        if ($window === null) {
            return ['hours' => null, 'well' => false];
        }

        $hours = $this->hoursAbove($window, $raDeg, $decDeg);
        $well  = $this->isLow($decDeg) ? $this->transitsIn($window, $raDeg) : $hours >= self::MIN_HOURS;

        return ['hours' => $hours, 'well' => $well];
    }

    /**
     * Whether the point crosses the meridian (hour angle 0) in the window.
     */
    private function transitsIn(NightWindow $window, float $raDeg): bool
    {
        $span      = $window->siderealTime($window->end) - $window->lstStart;
        $hourAngle = fmod(fmod($window->lstStart - $raDeg, 360.0) + 360.0, 360.0);

        return $hourAngle == 0.0 || $hourAngle + $span >= 360.0;
    }

    /**
     * Whether the month has dark hours at all (not so in summer far north).
     */
    public function hasDarkHours(int $month): bool
    {
        return ($this->windows[$month] ?? null) !== null;
    }

    private function hoursAbove(NightWindow $window, float $raDeg, float $decDeg): float
    {
        // The point is above the altitude for hour angles in [-limit, limit].
        $limit = $this->limitHourAngle($window->latitude, $decDeg);
        if ($limit === null) {
            return 0.0;
        }

        $span = $window->siderealTime($window->end) - $window->lstStart;
        $from = fmod(fmod($window->lstStart - $raDeg, 360.0) + 360.0, 360.0);
        $to   = $from + $span;

        // The window spans less than a day, so it meets at most the transits
        // at hour angle 0, 360 and 720.
        $above = 0.0;
        foreach ([0.0, 360.0, 720.0] as $transit) {
            $above += max(0.0, min($to, $transit + $limit) - max($from, $transit - $limit));
        }

        return min($span, $above) / $span * ($window->end - $window->start) / 3600.0;
    }
}
