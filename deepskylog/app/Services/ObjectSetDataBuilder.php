<?php

namespace App\Services;

use App\Models\Constellation;
use App\Models\DeepskyObject;
use App\Models\DeepskyType;
use App\Models\Location;
use App\Models\ObjectName;
use App\Models\Observation;
use App\Models\ObservingListItem;
use App\Models\User;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use deepskylog\AstronomyLibrary\Time;

/**
 * The facts printed about an object in an object set: its data page, and the
 * short summary under the photos when there is no data page.
 */
class ObjectSetDataBuilder
{
    /** @var array<string, string>|null */
    private ?array $typeNames = null;

    /** @var array<string, string>|null */
    private ?array $constellationNames = null;

    /**
     * @return array<string, mixed>
     */
    public function objectData(DeepskyObject $object, User $user, ?int $listId = null): array
    {
        $name = (string) $object->name;

        $altNames = ObjectName::where('objectname', $name)
            ->pluck('altname')
            ->map(fn ($alt) => trim((string) $alt))
            ->filter(fn (string $alt) => $alt !== '' && strcasecmp($alt, $name) !== 0)
            ->unique()
            ->values()
            ->all();

        $observations = Observation::ofDeepskyObject($name);
        $yours = (clone $observations)->where('observer_user_id', $user->id);
        $lastSeen = (clone $yours)->max('observed_on');

        $description = null;
        if ($listId !== null) {
            $description = ObservingListItem::where('observing_list_id', $listId)
                ->where('object_name', $name)
                ->value('item_description');
        }
        if (trim((string) $description) === '') {
            $description = $object->description;
        }

        return [
            'name' => $name,
            'alt_names' => $altNames,
            'ra' => DeepskyObject::formatRa($object->ra) ?? '-',
            'decl' => DeepskyObject::formatDec($object->decl) ?? '-',
            'type' => $this->typeName((string) $object->type),
            'constellation' => $this->constellationName((string) $object->con),
            'mag' => self::knownValue($object->mag),
            'subr' => self::knownValue($object->subr),
            'size' => self::formatSize($object->diam1, $object->diam2),
            'pa' => (is_numeric($object->pa) && (int) round((float) $object->pa) !== 999) ? ((int) round((float) $object->pa)) . '°' : '-',
            'seen' => (clone $observations)->count(),
            'your_seen' => (clone $yours)->count(),
            'last_seen' => $lastSeen ? Carbon::parse($lastSeen)->toDateString() : '-',
            'description' => trim(html_entity_decode(strip_tags((string) $description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
        ];
    }

    /**
     * Tonight's ephemerides at the observer's standard location, in local time.
     *
     * @return array<string, mixed>|null  null when the observer has no standard location
     */
    public function nightEphemerides(DeepskyObject $object, User $user, Carbon $date): ?array
    {
        $night = $this->nightConditions($user, $date);
        $objectEphemerides = $this->objectNightEphemerides($object, $user, $date);

        return $night === null || $objectEphemerides === null ? null : $night + $objectEphemerides;
    }

    /**
     * The sun and the moon tonight at the observer's standard location, in
     * local time.
     *
     * @return array<string, string>|null  null when the observer has no standard location
     */
    public function nightConditions(User $user, Carbon $date): ?array
    {
        $location = $this->location($user);
        if ($location === null) {
            return null;
        }

        $tz = $this->timezone($location);
        $day = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString() . ' 12:00', $tz);
        $geo = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
        $sun = date_sun_info($day->timestamp, (float) $location->latitude, (float) $location->longitude);
        // The night runs into the next morning, so the morning twilight and
        // sunrise are those of the day after.
        $nextSun = date_sun_info($day->copy()->addDay()->timestamp, (float) $location->latitude, (float) $location->longitude);

        [$moonRise, $moonSet] = $this->moonRiseSet($geo, $location, $day, $tz);

        return [
            'location' => (string) $location->name,
            'date' => $day->toDateString(),
            'next_date' => $day->copy()->addDay()->toDateString(),
            'sunset' => self::sunTime($sun, 'sunset', $tz),
            'sunrise' => self::sunTime($nextSun, 'sunrise', $tz),
            'nautical_end' => self::sunTime($sun, 'nautical_twilight_end', $tz),
            'nautical_begin' => self::sunTime($nextSun, 'nautical_twilight_begin', $tz),
            'astronomical_end' => self::sunTime($sun, 'astronomical_twilight_end', $tz),
            'astronomical_begin' => self::sunTime($nextSun, 'astronomical_twilight_begin', $tz),
            'moon_rise' => $moonRise,
            'moon_set' => $moonSet,
        ];
    }

    /**
     * The object tonight at the observer's standard location, in local time.
     *
     * @return array{rise: string, transit: string, set: string, max_altitude: string, max_altitude_night: string, best_time: string}|null
     */
    public function objectNightEphemerides(DeepskyObject $object, User $user, Carbon $date): ?array
    {
        $location = $this->location($user);
        if ($location === null) {
            return null;
        }

        $tz = $this->timezone($location);
        $day = Carbon::createFromFormat('Y-m-d H:i', $date->toDateString() . ' 12:00', $tz);
        $geo = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);

        return $this->objectEphemerides($object, $geo, $day, $tz);
    }

    /**
     * The object through the year: on the 1st and the 15th of every month,
     * its altitude, transit, rise and set, and the astronomical night.
     *
     * @return array{location: string, year: int, dates: array<int, array<string, string>>}|null
     */
    public function yearEphemerides(DeepskyObject $object, User $user, Carbon $date): ?array
    {
        $location = $this->location($user);
        if ($location === null) {
            return null;
        }

        $tz = $this->timezone($location);
        $geo = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
        $year = (int) $date->year;
        $dates = [];

        for ($month = 1; $month <= 12; $month++) {
            foreach ([1, 15] as $dayOfMonth) {
                $day = Carbon::create($year, $month, $dayOfMonth, 12, 0, 0, $tz);
                $sun = date_sun_info($day->timestamp, (float) $location->latitude, (float) $location->longitude);
                $nextSun = date_sun_info($day->copy()->addDay()->timestamp, (float) $location->latitude, (float) $location->longitude);
                $eph = $this->objectEphemerides($object, $geo, $day, $tz);

                $nightEnd = self::sunTime($sun, 'astronomical_twilight_end', $tz);
                $nightBegin = self::sunTime($nextSun, 'astronomical_twilight_begin', $tz);

                $dates[] = [
                    'label' => $day->isoFormat('D MMM'),
                    'max_alt' => $eph['max_altitude'],
                    'max_alt_night' => $eph['max_altitude_night'],
                    'transit' => $eph['transit'],
                    'rise' => $eph['rise'],
                    'set' => $eph['set'],
                    'night' => ($nightEnd === '-' || $nightBegin === '-') ? '-' : $nightEnd . ' - ' . $nightBegin,
                ];
            }
        }

        return [
            'location' => (string) $location->name,
            'year' => $year,
            'dates' => $dates,
        ];
    }

    /**
     * @return array{rise: string, transit: string, set: string, max_altitude: string, max_altitude_night: string, best_time: string}
     */
    private function objectEphemerides(DeepskyObject $object, GeographicalCoordinates $geo, Carbon $day, string $tz): array
    {
        $result = [
            'rise' => '-',
            'transit' => '-',
            'set' => '-',
            'max_altitude' => '-',
            'max_altitude_night' => '-',
            'best_time' => '-',
        ];

        try {
            $target = new AstroTarget();
            $target->setEquatorialCoordinates(new EquatorialCoordinates($object->raHours(), $object->declDegrees()));
            $target->calculateEphemerides($geo, Time::apparentSiderialTimeGreenwich($day), Time::deltaT($day));

            $result['transit'] = self::localTime(fn () => $target->getTransit(), $tz);
            $result['rise'] = self::localTime(fn () => $target->getRising(), $tz);
            $result['set'] = self::localTime(fn () => $target->getSetting(), $tz);
            $result['best_time'] = self::localTime(fn () => $target->getBestTimeToObserve(), $tz);
            $result['max_altitude'] = self::altitude(fn () => $target->getMaxHeight());
            $result['max_altitude_night'] = self::altitude(fn () => $target->getMaxHeightAtNight());
        } catch (\Throwable $_) {
            // An object that never rises or never sets has no such times; the
            // dashes above already say so.
        }

        return $result;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function moonRiseSet(GeographicalCoordinates $geo, Location $location, Carbon $day, string $tz): array
    {
        try {
            $moon = new AstroMoon();
            $moon->calculateEquatorialCoordinates($day->copy(), $geo, (float) ($location->elevation ?? 0.0));
            $moon->calculateEphemerides($geo, Time::apparentSiderialTimeGreenwich($day), Time::deltaT($day));

            return [
                self::localTime(fn () => $moon->getRising(), $tz),
                self::localTime(fn () => $moon->getSetting(), $tz),
            ];
        } catch (\Throwable $_) {
            return ['-', '-'];
        }
    }

    private function location(User $user): ?Location
    {
        $location = $user->standardLocation ?? null;

        if (! $location instanceof Location || ! is_numeric($location->latitude) || ! is_numeric($location->longitude)) {
            return null;
        }

        return $location;
    }

    private function timezone(Location $location): string
    {
        $tz = trim((string) ($location->timezone ?? ''));

        return ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) ? $tz : (string) config('app.timezone', 'UTC');
    }

    private function typeName(string $code): string
    {
        $this->typeNames ??= DeepskyType::pluck('name', 'code')->all();

        $name = $this->typeNames[$code] ?? $code;

        return $name === '' ? '-' : __($name);
    }

    private function constellationName(string $code): string
    {
        $this->constellationNames ??= Constellation::pluck('name', 'id')->all();

        $name = $this->constellationNames[$code] ?? $code;

        return $name === '' ? '-' : __($name);
    }

    /**
     * Type name for an object type code, for the index of a chart.
     */
    public function typeNameFor(?string $code): string
    {
        return $this->typeName((string) $code);
    }

    /**
     * Constellation name for a constellation code.
     */
    public function constellationNameFor(?string $code): string
    {
        return $this->constellationName((string) $code);
    }

    /**
     * A magnitude or surface brightness, or a dash for the 99.9 placeholder.
     */
    public static function knownValue($value): string
    {
        if (! is_numeric($value)) {
            return '-';
        }

        $v = (float) $value;
        if ($v == 0.0 || abs($v - 99.9) < 0.001 || $v < -900) {
            return '-';
        }

        return number_format($v, 1);
    }

    /**
     * Object size from diameters in arcseconds, e.g. 8.0'x4.0'.
     */
    public static function formatSize($diam1, $diam2): string
    {
        $d1 = is_numeric($diam1) ? (float) $diam1 : 0.0;
        $d2 = is_numeric($diam2) ? (float) $diam2 : 0.0;

        if ($d1 <= 0.0 && $d2 <= 0.0) {
            return '-';
        }

        $format = fn (float $arcsec): string => $arcsec >= 60.0
            ? number_format($arcsec / 60.0, 1) . "'"
            : number_format($arcsec, 0) . '"';

        if ($d1 > 0.0 && $d2 > 0.0) {
            return $format($d1) . 'x' . $format($d2);
        }

        return $format(max($d1, $d2));
    }

    private static function localTime(callable $get, string $tz): string
    {
        try {
            $time = $get();
        } catch (\Throwable $_) {
            return '-';
        }

        return $time instanceof \DateTimeInterface
            ? Carbon::instance($time)->timezone($tz)->isoFormat('HH:mm')
            : '-';
    }

    private static function altitude(callable $get): string
    {
        try {
            $alt = $get();
        } catch (\Throwable $_) {
            return '-';
        }

        if (is_object($alt) && method_exists($alt, 'getCoordinate')) {
            $alt = $alt->getCoordinate();
        }

        return is_numeric($alt) && (float) $alt > 0.0 ? ((int) round((float) $alt)) . '°' : '-';
    }

    /**
     * @param  array<string, int|bool>  $sun
     */
    private static function sunTime(array $sun, string $key, string $tz): string
    {
        $value = $sun[$key] ?? null;

        return is_int($value) ? Carbon::createFromTimestamp($value)->timezone($tz)->isoFormat('HH:mm') : '-';
    }
}
