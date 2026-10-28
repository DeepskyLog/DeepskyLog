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
     * When the date chosen in the aside stops applying (ISO 8601). Without it
     * the session date is stale: the aside used to store today's date on
     * every page, which then stayed for the whole session (days).
     */
    public const SESSION_UNTIL_KEY = 'dsl_ephemeris_date_until';

    /**
     * The cookie holding the browser's timezone, set by the layout's script.
     */
    public const TIMEZONE_COOKIE = 'dsl_tz';

    /**
     * The timezone of the location; without one, the timezone of the user's
     * standard location, else (guests, and users without a standard location)
     * the browser's timezone, else the application's (UTC). The ephemerides of
     * guests are calculated for a default location, but "today" is theirs.
     */
    public static function timezone(?Location $location = null): string
    {
        if (! $location) {
            $standard = Auth::user()?->standardLocation;
            if ($standard && $standard->hasValidCoordinates()) {
                $location = $standard;
            } else {
                return self::browserTimezone() ?? (string) config('app.timezone', 'UTC');
            }
        }
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
     * The browser's timezone from the cookie, or null when it is missing or
     * not a valid timezone.
     */
    public static function browserTimezone(): ?string
    {
        try {
            $tz = request()->cookie(self::TIMEZONE_COOKIE);
        } catch (\Throwable $_) {
            return null;
        }

        if (! is_string($tz) || $tz === '' || strlen($tz) > 64 || ! in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
            return null;
        }

        return $tz;
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
     * The date ('Y-m-d') of the current observing night in the given timezone:
     * it changes at noon, not at midnight, so at 01:00 the aside still shows
     * the night that started the evening before.
     */
    public static function today(?string $timezone = null): string
    {
        return Carbon::now($timezone ?? self::timezone())->subHours(12)->toDateString();
    }

    /**
     * Keep the date chosen in the ephemeris aside for this session, until noon
     * of the next day at the observer, so it lasts through the night it was
     * chosen in. Choosing today forgets it, so the aside follows the days.
     */
    public static function choose(mixed $value, ?string $timezone = null): void
    {
        $timezone ??= self::timezone();
        $date = self::parse($value, $timezone)->toDateString();

        try {
            if ($date === self::today($timezone)) {
                session()->forget([self::SESSION_KEY, self::SESSION_UNTIL_KEY]);

                return;
            }

            session()->put(self::SESSION_KEY, $date);
            session()->put(self::SESSION_UNTIL_KEY, Carbon::now($timezone)->addDay()->setTime(12, 0)->toIso8601String());
        } catch (\Throwable $_) {
            // ignore session failures
        }
    }

    /**
     * The date ('Y-m-d') chosen in the ephemeris aside, or null when none was
     * chosen or the choice has expired (which forgets it).
     */
    public static function chosen(?string $timezone = null): ?string
    {
        try {
            $date = session(self::SESSION_KEY);
            $until = session(self::SESSION_UNTIL_KEY);
        } catch (\Throwable $_) {
            return null;
        }

        if (empty($date)) {
            return null;
        }

        try {
            $valid = is_string($until) && Carbon::parse($until)->isFuture();
        } catch (\Throwable $_) {
            $valid = false;
        }

        if (! $valid) {
            try {
                session()->forget([self::SESSION_KEY, self::SESSION_UNTIL_KEY]);
            } catch (\Throwable $_) {
            }

            return null;
        }

        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date))) {
            return trim($date);
        }

        try {
            return self::parse($date, $timezone)->toDateString();
        } catch (\Throwable $_) {
            return null;
        }
    }

    /**
     * The date ('Y-m-d') chosen in the ephemeris aside, else today in the
     * given timezone.
     */
    public static function current(?string $timezone = null): string
    {
        return self::chosen($timezone) ?? self::today($timezone);
    }
}
