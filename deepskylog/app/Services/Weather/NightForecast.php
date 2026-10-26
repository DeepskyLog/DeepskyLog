<?php

namespace App\Services\Weather;

use App\Models\Location;
use App\Models\StarParty;
use App\Services\MoonlightSky;
use App\Services\NightWindow;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The cloud forecast of the coming nights at a location: the hourly forecast
 * of Open-Meteo (free, without a key, CC BY 4.0) in the dark hours of each
 * night (NightWindow), with the Moon in them (MoonlightSky), and a verdict
 * per night.
 *
 * The forecast is cached per cell of GRID degrees for CACHE_SECONDS. Pages
 * pass $fetch = false: a forecast that is not cached yet is then fetched
 * after the response (defer()), so a page never waits for Open-Meteo.
 */
class NightForecast
{
    /** The number of nights shown. */
    public const NIGHTS = 5;

    /** An hour with at most this cloud cover (%) is clear. */
    public const CLEAR_PERCENT = 25;

    /** A night is clear with this many clear dark hours in a row. */
    public const MIN_CLEAR_MINUTES = 120;

    /** A Moon lit less than this fraction does not spoil a dark night. */
    public const MOON_FRACTION = 0.25;

    /** An hour this humid (%) or more brings dew. */
    public const DEW_HUMIDITY = 90;

    public const VERDICTS = ['clear_dark', 'clear_moon', 'partly', 'cloudy', 'no_dark', 'unknown'];

    private const FORECAST_DAYS = 7;

    private const GRID = 0.1;

    private const CACHE_SECONDS = 3 * 3600;

    /** A failed request is not repeated for this long. */
    private const FAILURE_SECONDS = 900;

    /** After Open-Meteo refused a request (rate limit), nothing is asked for this long. */
    private const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'night_forecast:paused';

    private const HOURLY = ['cloud_cover', 'cloud_cover_low', 'cloud_cover_mid', 'cloud_cover_high', 'relative_humidity_2m', 'wind_speed_10m'];

    public static function enabled(): bool
    {
        return (bool) config('services.open_meteo.enabled', true);
    }

    /**
     * The coming nights at the location, or null when there is no forecast
     * (turned off, the location has no coordinates, Open-Meteo failed, or
     * with $fetch false, not cached yet).
     *
     * @return list<array<string, mixed>>|null
     */
    public static function forLocation(Location $location, bool $fetch = true, int $nights = self::NIGHTS): ?array
    {
        if (! $location->hasValidCoordinates()) {
            return null;
        }

        return self::nights(
            (float) $location->latitude,
            (float) $location->longitude,
            (float) ($location->elevation ?? 0.0),
            EphemerisDate::timezone($location),
            $fetch,
            $nights,
        );
    }

    /**
     * The coming $nights nights from firstNight().
     *
     * @return list<array<string, mixed>>|null
     */
    public static function nights(float $latitude, float $longitude, float $elevation, string $timezone, bool $fetch = true, int $nights = self::NIGHTS): ?array
    {
        $first = Carbon::parse(self::firstNight($latitude, $longitude, $timezone), $timezone);
        $dates = array_map(fn (int $i) => $first->copy()->addDays($i)->toDateString(), range(0, $nights - 1));

        $result = self::forNights($latitude, $longitude, $elevation, $timezone, $dates, $fetch);

        return $result === null ? null : array_values($result);
    }

    /**
     * The forecast of the nights (Y-m-d of their evening in $timezone), keyed
     * by night; a night beyond the forecast gets the verdict 'unknown'.
     *
     * @param  list<string>  $nights
     * @return array<string, array<string, mixed>>|null
     */
    public static function forNights(float $latitude, float $longitude, float $elevation, string $timezone, array $nights, bool $fetch = true): ?array
    {
        $hourly = self::hourly($latitude, $longitude, $fetch);
        if ($hourly === null) {
            return null;
        }

        $key = 'night_forecast:nights:'.md5(json_encode([round($latitude, 4), round($longitude, 4), round($elevation), $timezone, $nights, $hourly['fetched_at']]));

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($hourly, $latitude, $longitude, $elevation, $timezone, $nights) {
            $result = [];
            foreach ($nights as $night) {
                $result[$night] = self::night($hourly, $latitude, $longitude, $elevation, $timezone, $night);
            }

            return $result;
        });
    }

    /**
     * The forecast of the nights of a starparty at its site that the forecast
     * reaches, keyed by night; null without a forecast.
     *
     * @return array<string, array<string, mixed>>|null
     */
    public static function forStarParty(StarParty $party, bool $fetch = true): ?array
    {
        $site = $party->site();
        if (! $site->hasValidCoordinates()) {
            return null;
        }

        $timezone = EphemerisDate::timezone($site);
        $first    = self::firstNight((float) $site->latitude, (float) $site->longitude, $timezone);
        $last     = Carbon::parse($first, $timezone)->addDays(self::FORECAST_DAYS - 1)->toDateString();
        $nights   = array_values(array_filter($party->nights(), fn (string $night) => $night >= $first && $night <= $last));
        if ($nights === []) {
            return null;
        }

        $result = self::forNights((float) $site->latitude, (float) $site->longitude, (float) ($site->elevation ?? 0.0), $timezone, $nights, $fetch);

        return $result === null ? null : array_filter($result, fn (array $night) => $night['verdict'] !== 'unknown');
    }

    /**
     * The verdict in words.
     */
    public static function verdictLabel(string $verdict, ?string $locale = null): string
    {
        return match ($verdict) {
            'clear_dark' => __('Clear and dark', [], $locale),
            'clear_moon' => __('Clear, with Moon', [], $locale),
            'partly'     => __('Partly cloudy', [], $locale),
            'cloudy'     => __('Cloudy', [], $locale),
            'no_dark'    => __('Not dark', [], $locale),
            default      => __('No forecast yet', [], $locale),
        };
    }

    /**
     * The Tailwind background class of the verdict's badge.
     */
    public static function verdictClass(string $verdict): string
    {
        return match ($verdict) {
            'clear_dark' => 'bg-green-700',
            'clear_moon' => 'bg-yellow-700',
            'partly'     => 'bg-blue-700',
            'cloudy'     => 'bg-gray-600',
            default      => 'bg-gray-700',
        };
    }

    /**
     * The forecast of a night in one line of text: the verdict, the clouds,
     * the clear hours and the risk of dew.
     *
     * @param  array<string, mixed>  $night  as night() gives it
     */
    public static function summary(array $night, ?string $locale = null): string
    {
        $parts = [self::verdictLabel($night['verdict'], $locale)];
        if ($night['cloud'] !== null) {
            $parts[] = __('Clouds :percent%', ['percent' => $night['cloud']], $locale);
        }
        if (($night['clear_dark']['minutes'] ?? 0) >= 60) {
            $parts[] = __('Clear and dark :from–:until', $night['clear_dark'], $locale);
        } elseif (($night['clear']['minutes'] ?? 0) >= 60) {
            $parts[] = __('Clear :from–:until', $night['clear'], $locale);
        }
        if ($night['dew']) {
            $parts[] = __('Risk of dew', [], $locale);
        }

        return implode(' · ', $parts);
    }

    /**
     * The night (Y-m-d of its evening in $timezone) that is coming or going
     * on: the night of EphemerisDate::today(), unless its dark hours are over.
     */
    public static function firstNight(float $latitude, float $longitude, string $timezone): string
    {
        $night  = EphemerisDate::today($timezone);
        $window = NightWindow::for($latitude, $longitude, $night, $timezone);
        $end    = $window?->end ?? Carbon::parse($night.' 12:00', $timezone)->addDay()->getTimestamp();

        return $end <= now()->getTimestamp() ? Carbon::parse($night, $timezone)->addDay()->toDateString() : $night;
    }

    /**
     * The forecast of one night: its dark hours, the Moon, the forecast hours
     * in them, the average cloud cover, the longest clear stretch with and
     * without a bright Moon, and the verdict.
     *
     * @param  array<string, mixed>  $hourly  as hourly() gives it
     * @return array<string, mixed>
     */
    public static function night(array $hourly, float $latitude, float $longitude, float $elevation, string $timezone, string $night): array
    {
        $result = [
            'night' => $night, 'dark' => null, 'moon' => null, 'hours' => [], 'cloud' => null,
            'clear' => null, 'clear_dark' => null, 'dew' => false, 'verdict' => 'no_dark',
        ];

        $window = NightWindow::for($latitude, $longitude, $night, $timezone);
        if ($window === null) {
            return $result;
        }

        $time           = fn (int $timestamp) => Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');
        $result['dark'] = ['from' => $time($window->start), 'until' => $time($window->end), 'twilight' => $window->twilight];

        // Each forecast hour stands for the half hour before and after it,
        // clipped to the dark hours.
        foreach ($hourly['time'] as $i => $timestamp) {
            $from  = max($timestamp - 1800, $window->start);
            $until = min($timestamp + 1800, $window->end);
            if ($until <= $from || $hourly['cloud_cover'][$i] === null) {
                continue;
            }

            $result['hours'][] = [
                'time'     => $time($timestamp),
                'from'     => $from,
                'until'    => $until,
                'cloud'    => (int) $hourly['cloud_cover'][$i],
                'low'      => (int) ($hourly['cloud_cover_low'][$i] ?? 0),
                'mid'      => (int) ($hourly['cloud_cover_mid'][$i] ?? 0),
                'high'     => (int) ($hourly['cloud_cover_high'][$i] ?? 0),
                'humidity' => (int) ($hourly['relative_humidity_2m'][$i] ?? 0),
                'wind'     => (int) round($hourly['wind_speed_10m'][$i] ?? 0),
            ];
        }

        // A night beyond the forecast: no need for the Moon
        if ($result['hours'] === []) {
            $result['verdict'] = 'unknown';

            return $result;
        }

        $moon     = new MoonlightSky($window, $longitude, $elevation);
        $fraction = $moon->illuminatedFraction();
        $up       = $moon->upBetween();
        if ($up) {
            $result['moon'] = ['fraction' => (int) round($fraction * 100), 'from' => $time($up[0]), 'until' => $time($up[1])];
        }
        $brightMoon = $up !== null && $fraction >= self::MOON_FRACTION;
        foreach ($result['hours'] as &$hour) {
            $hour['moon'] = $brightMoon && $hour['until'] > $up[0] && $hour['from'] < $up[1];
        }
        unset($hour);

        $seconds         = array_sum(array_map(fn (array $hour) => $hour['until'] - $hour['from'], $result['hours']));
        $result['cloud'] = (int) round(array_sum(array_map(fn (array $hour) => $hour['cloud'] * ($hour['until'] - $hour['from']), $result['hours'])) / $seconds);

        $clear                = fn (array $hour) => $hour['cloud'] <= self::CLEAR_PERCENT;
        $result['clear']      = self::longest($result['hours'], $clear, $time);
        $result['clear_dark'] = self::longest($result['hours'], fn (array $hour) => $clear($hour) && ! $hour['moon'], $time);
        $result['dew']        = collect($result['hours'])->contains(fn (array $hour) => $clear($hour) && $hour['humidity'] >= self::DEW_HUMIDITY);

        $result['verdict'] = match (true) {
            ($result['clear_dark']['minutes'] ?? 0) >= self::MIN_CLEAR_MINUTES     => 'clear_dark',
            ($result['clear']['minutes'] ?? 0)      >= self::MIN_CLEAR_MINUTES     => 'clear_moon',
            ($result['clear']['minutes'] ?? 0)      >= 60 || $result['cloud'] < 60 => 'partly',
            default                                                                => 'cloudy',
        };

        foreach ($result['hours'] as &$hour) {
            unset($hour['from'], $hour['until']);
        }

        return $result;
    }

    /**
     * The hourly forecast of the cell of the point: 'time' (timestamps) and
     * the HOURLY values, and 'fetched_at'; null when it can't be had.
     *
     * @return array<string, mixed>|null
     */
    public static function hourly(float $latitude, float $longitude, bool $fetch = true): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        $latitude  = round(round($latitude / self::GRID) * self::GRID, 2);
        $longitude = round(round($longitude / self::GRID) * self::GRID, 2);
        $key       = 'night_forecast:hourly:'.$latitude.':'.$longitude;

        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        if (! $fetch) {
            defer(fn () => self::hourly($latitude, $longitude), 'night_forecast:'.$key);

            return null;
        }

        if (Cache::has(self::PAUSE_KEY)) {
            return null;
        }

        $forecast = self::fetch($latitude, $longitude);
        Cache::put($key, $forecast ?? false, $forecast ? self::CACHE_SECONDS : self::FAILURE_SECONDS);

        return $forecast;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fetch(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(10)->acceptJson()->get(config('services.open_meteo.url'), [
                'latitude'      => $latitude,
                'longitude'     => $longitude,
                'hourly'        => implode(',', self::HOURLY),
                'forecast_days' => self::FORECAST_DAYS,
                'timeformat'    => 'unixtime',
                'timezone'      => 'UTC',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Open-Meteo forecast failed', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->status() === 429) {
            Cache::put(self::PAUSE_KEY, true, self::PAUSE_SECONDS);
        }

        $hourly = $response->successful() ? $response->json('hourly') : null;
        if (! is_array($hourly) || ! is_array($hourly['time'] ?? null) || ! is_array($hourly['cloud_cover'] ?? null)) {
            Log::warning('Open-Meteo forecast failed', ['status' => $response->status()]);

            return null;
        }

        $forecast = ['fetched_at' => now()->getTimestamp(), 'time' => array_map('intval', $hourly['time'])];
        foreach (self::HOURLY as $field) {
            $forecast[$field] = $hourly[$field] ?? [];
        }

        return $forecast;
    }

    /**
     * The longest run of hours that pass the test, as from/until (H:i) and
     * minutes, or null when no hour does.
     *
     * @param  list<array<string, mixed>>  $hours
     * @return array{from: string, until: string, minutes: int}|null
     */
    private static function longest(array $hours, callable $test, callable $time): ?array
    {
        $best = null;
        $run  = null;
        foreach ($hours as $hour) {
            if (! $test($hour)) {
                $run = null;

                continue;
            }
            $run = $run === null || $run['until'] !== $hour['from'] ? ['from' => $hour['from'], 'until' => $hour['until']] : ['from' => $run['from'], 'until' => $hour['until']];
            if ($best === null || $run['until'] - $run['from'] > $best['until'] - $best['from']) {
                $best = $run;
            }
        }

        return $best === null ? null : ['from' => $time($best['from']), 'until' => $time($best['until']), 'minutes' => intdiv($best['until'] - $best['from'], 60)];
    }
}
