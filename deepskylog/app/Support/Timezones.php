<?php

namespace App\Support;

/**
 * Time zone names for locations, which store an IANA identifier
 * ("Europe/Brussels"). OAL files give a site's time zone as an offset in
 * minutes from UT instead.
 */
class Timezones
{
    /**
     * Whether the value is a time zone name. Carbon also accepts a bare number
     * ("60") as an offset in hours, which a location must not have.
     */
    public static function isIdentifier(?string $timezone): bool
    {
        $timezone = trim((string) $timezone);
        if ($timezone === '' || preg_match('/^[+-]?\d/', $timezone) === 1) {
            return false;
        }

        // A zone PHP knows by name ("Europe/Brussels", also an abbreviation
        // such as "CET"), not an offset (type 1).
        try {
            return json_decode(json_encode(new \DateTimeZone($timezone)))->timezone_type !== 1;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * The identifier, or UTC when it is not one.
     */
    public static function orUtc(?string $timezone): string
    {
        return self::isIdentifier($timezone) ? trim((string) $timezone) : 'UTC';
    }

    /**
     * The time zone of a site from its offset in minutes from UT (in January,
     * as OAL exports write it) and its position: of the zones with that offset,
     * the one whose reference city is nearest. Without a zone with that offset,
     * the nearest zone; without a position, UTC for offset 0, else an Etc/GMT
     * zone for a whole number of hours, else UTC.
     */
    public static function fromOffset(int $minutes, ?float $latitude = null, ?float $longitude = null): string
    {
        $hasPosition = $latitude !== null && $longitude !== null && ($latitude != 0.0 || $longitude != 0.0);
        $january = new \DateTimeImmutable('first day of january ' . date('Y'), new \DateTimeZone('UTC'));

        $nearest = null;
        $nearestWithOffset = null;
        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $zone = new \DateTimeZone($identifier);
            $location = $zone->getLocation();
            if (!$hasPosition || $location === false || $identifier === 'UTC') {
                continue;
            }
            $distance = self::distance($latitude, $longitude, (float) $location['latitude'], (float) $location['longitude']);
            if ($nearest === null || $distance < $nearest[1]) {
                $nearest = [$identifier, $distance];
            }
            if (intdiv($zone->getOffset($january), 60) === $minutes && ($nearestWithOffset === null || $distance < $nearestWithOffset[1])) {
                $nearestWithOffset = [$identifier, $distance];
            }
        }

        if ($nearestWithOffset !== null) {
            return $nearestWithOffset[0];
        }
        if ($nearest !== null) {
            return $nearest[0];
        }
        if ($minutes === 0) {
            return 'UTC';
        }
        if ($minutes % 60 === 0 && abs($minutes) <= 12 * 60) {
            // Etc/GMT has the sign the other way round: Etc/GMT-1 is UT+1.
            $hours = intdiv($minutes, 60);

            return 'Etc/GMT' . ($hours > 0 ? '-' : '+') . abs($hours);
        }

        return 'UTC';
    }

    /**
     * The angle in degrees between two positions on the sphere.
     */
    private static function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        [$lat1, $lon1, $lat2, $lon2] = array_map('deg2rad', [$lat1, $lon1, $lat2, $lon2]);
        $cos = sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon1 - $lon2);

        return rad2deg(acos(max(-1.0, min(1.0, $cos))));
    }
}
