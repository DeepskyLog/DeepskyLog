<?php

namespace App\Support;

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * The date of the ephemerides, as chosen in the ephemeris aside.
 *
 * A date without a time ('Y-m-d') is a calendar date of the observer, so it is
 * read in the timezone of the observer's location, at noon to stay clear of
 * the DST changes at night. Parsing it in UTC and converting it to the
 * observer's timezone afterwards moves it back a day west of UTC.
 */
class EphemerisDate
{
    public const SESSION_KEY = 'dsl_ephemeris_date';

    /**
     * The timezone of the location, else of the authenticated user's
     * standard location, else of the application.
     */
    public static function timezone(?Location $location = null): string
    {
        $location ??= Auth::user()?->standardLocation;
        $tz = $location?->timezone;

        if (is_string($tz) && $tz !== '') {
            try {
                new \DateTimeZone($tz);

                return $tz;
            } catch (\Throwable $_) {
            }
        }

        return (string) config('app.timezone', 'UTC');
    }

    /**
     * The moment for the given date in the given timezone. Empty or invalid
     * values give the current moment; a date without a time gives noon.
     */
    public static function parse(mixed $value, ?string $timezone = null): Carbon
    {
        $timezone ??= self::timezone();

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->timezone($timezone);
        }

        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return Carbon::now($timezone);
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('!Y-m-d', $value, $timezone)->setTime(12, 0);
            }

            // Values with a time keep their instant (and offset, when given);
            // values without an offset are local times of the observer.
            return Carbon::parse($value, $timezone)->timezone($timezone);
        } catch (\Throwable $_) {
            return Carbon::now($timezone);
        }
    }

    /**
     * Today's date ('Y-m-d') in the given timezone.
     */
    public static function today(?string $timezone = null): string
    {
        return Carbon::now($timezone ?? self::timezone())->toDateString();
    }

    /**
     * The date ('Y-m-d') chosen in the ephemeris aside for this session, else
     * today in the given timezone.
     */
    public static function current(?string $timezone = null): string
    {
        try {
            $date = session(self::SESSION_KEY);
        } catch (\Throwable $_) {
            $date = null;
        }

        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date))) {
            return trim($date);
        }

        if (! empty($date)) {
            try {
                return self::parse($date, $timezone)->toDateString();
            } catch (\Throwable $_) {
            }
        }

        return self::today($timezone);
    }
}
