<?php

namespace App\Services\Weather;

use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Services\NightWindow;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The weather of past nights: the hourly temperature, humidity, dew point,
 * cloud cover and wind of Open-Meteo's historical archive (ERA5 reanalysis,
 * free, without a key, CC BY 4.0; the provider of NightForecast), stored in
 * night_weather per UTC day and cell of GRID degrees, as the past does not
 * change. A night is summarised over its dark hours (NightWindow), or over
 * the hours of a session that gives them.
 *
 * Shown as "Conditions that night" on the observation page, and on the
 * session page when the observer wrote no weather; the session form fills
 * the empty weather field with text(). Pages fetch a missing night in the
 * lazy request of App\Livewire\NightConditions; FillNightWeather fetches
 * those of all sessions and observations ahead, WINDOW_DAYS per request.
 */
class NightWeather
{
    /** The cell of the archive (ERA5 has 0.25°). */
    public const GRID = 0.25;

    /** The archive holds a day this many days after it. */
    public const DELAY_DAYS = 5;

    /** The archive starts here. */
    public const FIRST_DATE = '1940-01-01';

    /** The days asked in one request (Open-Meteo counts a longer range as more calls). */
    public const WINDOW_DAYS = 14;

    /** The nights of a session shown at most. */
    public const MAX_NIGHTS = 7;

    /** A day with missing hours is stored once it is this old: the archive will not fill them. */
    private const COMPLETE_AFTER_DAYS = 14;

    /** Without darkness (polar summer) a night is these local hours. */
    private const NO_DARK_HOURS = ['21:00', '03:00'];

    public const HOURLY = ['temperature_2m', 'relative_humidity_2m', 'dew_point_2m', 'cloud_cover', 'wind_speed_10m'];

    /** A failed request is not repeated for this long. */
    private const FAILURE_SECONDS = 900;

    /** After Open-Meteo refused a request (rate limit), nothing is asked for this long. */
    private const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'night_weather:paused';

    public static function enabled(): bool
    {
        return (bool) config('services.open_meteo.archive_enabled', true);
    }

    public static function paused(): bool
    {
        return Cache::has(self::PAUSE_KEY);
    }

    /**
     * The cell of a point: latitude and longitude / GRID, rounded.
     *
     * @return array{0: int, 1: int}
     */
    public static function cell(float $latitude, float $longitude): array
    {
        return [(int) round($latitude / self::GRID), (int) round($longitude / self::GRID)];
    }

    /**
     * Whether the archive holds the day (Y-m-d, UTC).
     */
    public static function available(string $date): bool
    {
        return $date >= self::FIRST_DATE && $date <= now('UTC')->subDays(self::DELAY_DAYS)->toDateString();
    }

    /**
     * Whether the observation can have the weather of its night: it has a
     * location with coordinates and the archive holds the day after it.
     */
    public static function appliesToObservation(Observation $observation): bool
    {
        if (! self::enabled() || $observation->observed_on === null || ! $observation->location_id) {
            return false;
        }
        $location = Location::find($observation->location_id);

        return $location !== null && $location->hasValidCoordinates()
                                  && self::available($observation->observed_on->toDateString())
                                  && self::available($observation->observed_on->copy()->addDay()->toDateString());
    }

    /**
     * Whether the session can have the weather of its nights: it has a
     * location with coordinates and the archive holds the morning after
     * its last night.
     */
    public static function appliesToSession(ObservationSession $session): bool
    {
        $location = self::sessionLocation($session);
        if (! self::enabled() || ! $location || ! $location->hasValidCoordinates()) {
            return false;
        }
        $nights = self::sessionNights($session, $location);

        return $nights !== [] && self::available($nights[0]['night'])
                              && self::available(Carbon::parse(end($nights)['night'])->addDay()->toDateString());
    }

    /**
     * The weather of the night of an observation at its location: 'night'
     * as night() gives it and 'at', the hour nearest to its moment (null
     * without a time). Null without a location, for a night the archive
     * does not hold (yet), or when it could not be fetched.
     *
     * @return array{night: array<string, mixed>, at: array<string, mixed>|null}|null
     */
    public static function forObservation(Observation $observation, bool $fetch = true): ?array
    {
        $location = $observation->location_id ? Location::find($observation->location_id) : null;
        if (! $location || ! $location->hasValidCoordinates() || $observation->observed_on === null) {
            return null;
        }

        $timezone = EphemerisDate::timezone($location);
        $local    = $observation->localObservedAt($location);
        $night    = $local === null
            ? $observation->observed_on->toDateString()
            : ($local->hour < 12 ? $local->copy()->subDay() : $local)->toDateString();

        $at     = $local?->copy()->utc()->addMinutes(30)->startOfHour()->getTimestamp();
        $result = self::night((float) $location->latitude, (float) $location->longitude, $timezone, $night, null, $fetch, $at);
        if ($result === null) {
            return null;
        }

        $hour = $result['at'];
        unset($result['at']);

        return ['night' => $result, 'at' => $hour];
    }

    /**
     * The weather of the nights of a session at its location (at most
     * MAX_NIGHTS), as night() gives them; null when there is none.
     *
     * @return list<array<string, mixed>>|null
     */
    public static function forSession(ObservationSession $session, bool $fetch = true): ?array
    {
        return self::forNights(self::sessionLocation($session), self::sessionNights($session), $fetch);
    }

    /**
     * The weather of the nights at a location; null when one of them has none.
     *
     * @param  list<array{night: string, hours: array{0: int, 1: int}|null}>  $nights  as sessionNights() gives them
     * @return list<array<string, mixed>>|null
     */
    public static function forNights(?Location $location, array $nights, bool $fetch = true): ?array
    {
        if (! $location || ! $location->hasValidCoordinates() || $nights === []) {
            return null;
        }

        $timezone  = EphemerisDate::timezone($location);
        $latitude  = (float) $location->latitude;
        $longitude = (float) $location->longitude;

        // One request for all nights of the session
        if ($fetch) {
            $days = [];
            foreach ($nights as $night) {
                $days = array_merge($days, self::days(...self::window($latitude, $longitude, $timezone, $night['night'], $night['hours'])));
            }
            self::ensure($latitude, $longitude, $days);
        }

        $result = [];
        foreach ($nights as $night) {
            $weather = self::night($latitude, $longitude, $timezone, $night['night'], $night['hours'], false);
            if ($weather === null) {
                return null;
            }
            unset($weather['at']);
            $result[] = $weather;
        }

        return $result;
    }

    /**
     * The location of a session, or null.
     */
    public static function sessionLocation(ObservationSession $session): ?Location
    {
        return $session->locationid ? Location::find($session->locationid) : null;
    }

    /**
     * The nights of a session (Y-m-d of their evening) and, for a session of
     * one night with times, its hours (timestamps; else null: the dark
     * hours). The form stores dates only, as 00:00:00 to 23:59:59: such a
     * session over several dates ends on the morning of its last date.
     *
     * @return list<array{night: string, hours: array{0: int, 1: int}|null}>
     */
    public static function sessionNights(ObservationSession $session, ?Location $location = null): array
    {
        $attributes = $session->getAttributes();
        $begin      = $attributes['begindate'] ?? null;
        if (empty($begin)) {
            return [];
        }
        $end = ($attributes['enddate'] ?? null) ?: $begin;
        $location ??= self::sessionLocation($session);
        $timezone = EphemerisDate::timezone($location);

        try {
            $begin = Carbon::parse($begin, $timezone);
            $end   = Carbon::parse($end, $timezone);
        } catch (\Throwable) {
            return [];
        }
        if ($end->lt($begin)) {
            $end = $begin->copy();
        }

        $dateOnly = $begin->format('H:i:s') === '00:00:00' && in_array($end->format('H:i:s'), ['00:00:00', '23:59:59'], true);
        $evening  = fn (Carbon $moment) => ($moment->hour < 12 ? $moment->copy()->subDay() : $moment)->toDateString();

        if ($dateOnly) {
            $first = $begin->toDateString();
            $last  = $end->toDateString() > $first ? $end->copy()->subDay()->toDateString() : $first;
        } else {
            $first = $evening($begin);
            $last  = max($first, $evening($end));
        }

        $hours  = ! $dateOnly && $first === $last && $end->gt($begin) ? [$begin->getTimestamp(), $end->getTimestamp()] : null;
        $nights = [];
        for ($night = Carbon::parse($first); $night->toDateString() <= $last && count($nights) < self::MAX_NIGHTS; $night->addDay()) {
            $nights[] = ['night' => $night->toDateString(), 'hours' => $hours];
        }

        return $nights;
    }

    /**
     * The weather of one night (Y-m-d of its evening in $timezone) over its
     * dark hours, or over $hours (timestamps): the hours with their cloud
     * cover, temperature, humidity, dew point and wind, the average cloud
     * cover and the clear minutes (at most NightForecast::CLEAR_PERCENT),
     * the lowest and highest temperature, the average humidity and wind,
     * the strongest wind, and whether dew was likely; with $at (a timestamp
     * of a whole hour) also the values of that hour as 'at'. Null when the
     * archive does not hold the night (yet) or it could not be fetched.
     *
     * @param  array{0: int, 1: int}|null  $hours
     * @return array<string, mixed>|null
     */
    public static function night(float $latitude, float $longitude, string $timezone, string $night, ?array $hours = null, bool $fetch = true, ?int $at = null): ?array
    {
        [$start, $end, $kind] = self::window($latitude, $longitude, $timezone, $night, $hours);
        $days                 = self::days($start, $end);
        if ($at !== null) {
            $days[] = gmdate('Y-m-d', $at);
        }
        $days = array_values(array_unique($days));

        if ($fetch) {
            self::ensure($latitude, $longitude, $days);
        }
        $values = self::hourly($latitude, $longitude, $days);
        if ($values === null) {
            return null;
        }

        $time   = fn (int $timestamp) => Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');
        $result = [
            'night'     => $night, 'from' => $time($start), 'until' => $time($end), 'kind' => $kind, 'hours' => [],
            'cloud'     => null, 'clear_minutes' => 0, 'minutes' => 0, 'temperature' => null, 'humidity' => null,
            'dew_point' => null, 'wind' => null, 'wind_max' => null, 'dew' => false, 'at' => null,
        ];

        if ($at !== null && isset($values[$at]) && $values[$at]['cloud_cover'] !== null) {
            $result['at'] = ['time' => $time($at)] + self::hour($values[$at]);
        }

        // Each hour stands for the half hour before and after it, clipped to the night.
        $weights = [];
        foreach ($values as $timestamp => $value) {
            $from  = max($timestamp - 1800, $start);
            $until = min($timestamp + 1800, $end);
            if ($until <= $from || $value['cloud_cover'] === null) {
                continue;
            }
            $result['hours'][] = ['time' => $time($timestamp)] + self::hour($value);
            $weights[]         = $until - $from;
        }
        if ($result['hours'] === []) {
            return null;
        }

        $total   = array_sum($weights);
        $average = function (string $field) use ($result, $weights, $total) {
            $sum    = 0.0;
            $weight = 0;
            foreach ($result['hours'] as $i => $hour) {
                if ($hour[$field] !== null) {
                    $sum    += $hour[$field] * $weights[$i];
                    $weight += $weights[$i];
                }
            }

            return $weight > 0 ? $sum / $weight : null;
        };
        $column = fn (string $field) => array_values(array_filter(array_column($result['hours'], $field), fn ($value) => $value !== null));

        $result['minutes']       = intdiv($total, 60);
        $result['cloud']         = (int) round($average('cloud'));
        $result['clear_minutes'] = intdiv(array_sum(array_map(fn (array $hour, int $weight) => $hour['cloud'] <= NightForecast::CLEAR_PERCENT ? $weight : 0, $result['hours'], $weights)), 60);

        $temperatures = $column('temperature');
        if ($temperatures !== []) {
            $result['temperature'] = ['min' => min($temperatures), 'max' => max($temperatures)];
        }
        $humidity            = $average('humidity');
        $result['humidity']  = $humidity === null ? null : (int) round($humidity);
        $dewPoints           = $column('dew_point');
        $result['dew_point'] = $dewPoints === [] ? null : min($dewPoints);
        $wind                = $average('wind');
        $result['wind']      = $wind === null ? null : (int) round($wind);
        $winds               = $column('wind');
        $result['wind_max']  = $winds === [] ? null : max($winds);
        $result['dew']       = collect($result['hours'])->contains(fn (array $hour) => $hour['humidity'] !== null && $hour['humidity'] >= NightForecast::DEW_HUMIDITY);

        return $result;
    }

    /**
     * The weather of a night in one line of text: the clouds, the clear
     * hours, the temperature, the humidity, the wind and the risk of dew.
     *
     * @param  array<string, mixed>  $night  as night() gives it
     */
    public static function summary(array $night, ?string $locale = null): string
    {
        $parts = [__('Clouds :percent%', ['percent' => $night['cloud']], $locale)];
        if ($night['minutes'] >= 30) {
            $parts[] = __('clear :clear of :total h', [
                'clear' => (int) round($night['clear_minutes'] / 60),
                'total' => (int) round($night['minutes'] / 60),
            ], $locale);
        }
        if ($night['temperature'] !== null) {
            $parts[] = (int) round($night['temperature']['min']) === (int) round($night['temperature']['max'])
                ? self::degrees($night['temperature']['min'])
                : __(':min to :max', ['min' => self::degrees($night['temperature']['min']), 'max' => self::degrees($night['temperature']['max'])], $locale);
        }
        if ($night['humidity'] !== null) {
            $parts[] = __('Humidity :percent%', ['percent' => $night['humidity']], $locale);
        }
        if ($night['wind'] !== null) {
            $parts[] = $night['wind_max'] !== null && (int) round($night['wind_max']) > $night['wind']
                ? __('Wind :speed km/h, up to :max km/h', ['speed' => $night['wind'], 'max' => (int) round($night['wind_max'])], $locale)
                : __('Wind :speed km/h', ['speed' => $night['wind']], $locale);
        }
        if ($night['dew']) {
            $parts[] = __('Risk of dew', [], $locale);
        }

        return implode(' · ', $parts);
    }

    /**
     * The values of one hour in one line of text.
     *
     * @param  array<string, mixed>  $hour  as night() gives its hours
     */
    public static function hourSummary(array $hour, ?string $locale = null): string
    {
        $parts = [__('Clouds :percent%', ['percent' => $hour['cloud']], $locale)];
        if ($hour['temperature'] !== null) {
            $parts[] = self::degrees($hour['temperature']);
        }
        if ($hour['humidity'] !== null) {
            $parts[] = __('Humidity :percent%', ['percent' => $hour['humidity']], $locale);
        }
        if ($hour['wind'] !== null) {
            $parts[] = __('Wind :speed km/h', ['speed' => (int) round($hour['wind'])], $locale);
        }

        return implode(' · ', $parts);
    }

    /**
     * The weather of the nights as HTML for the weather field of a session:
     * a paragraph per night and the credit Open-Meteo's licence asks for.
     *
     * @param  list<array<string, mixed>>  $nights  as night() gives them
     */
    public static function text(array $nights, ?string $locale = null): string
    {
        $lines = array_map(fn (array $night) => '<p>'.e(
            (count($nights) > 1 ? Carbon::parse($night['night'])->locale($locale ?? app()->getLocale())->translatedFormat('j M').', ' : '')
            .$night['from'].'–'.$night['until'].': '.self::summary($night, $locale)
        ).'</p>', $nights);
        $lines[] = '<p><em>'.e(__('Weather of that night from the historical archive of Open-Meteo.com', [], $locale)).'</em></p>';

        return implode("\n", $lines);
    }

    /**
     * Makes sure the days of the point are stored, fetching the missing ones
     * the archive holds; false when one could not be fetched.
     *
     * @param  list<string>  $days
     */
    public static function ensure(float $latitude, float $longitude, array $days): bool
    {
        [$cellLat, $cellLon] = self::cell($latitude, $longitude);
        $missing             = self::missing($cellLat, $cellLon, $days);
        if ($missing === [] || ! self::enabled()) {
            return $missing === [];
        }

        $ok = true;
        foreach (self::windows($missing) as [$from, $until]) {
            $ok = self::fetch($cellLat, $cellLon, $from, $until) && $ok;
        }

        return $ok;
    }

    /**
     * The days of the cell the archive holds that are not stored.
     *
     * @param  list<string>  $days
     * @return list<string>
     */
    public static function missing(int $cellLat, int $cellLon, array $days): array
    {
        $days = array_values(array_unique(array_filter($days, fn (string $day) => self::available($day))));
        if ($days === []) {
            return [];
        }
        sort($days);

        $stored = DB::table('night_weather')
            ->where('cell_lat', $cellLat)->where('cell_lon', $cellLon)
            ->whereBetween('date', [$days[0], end($days)])
            ->pluck('date')
            ->map(fn ($date) => substr((string) $date, 0, 10))
            ->all();

        return array_values(array_diff($days, $stored));
    }

    /**
     * Sorted days grouped into ranges of at most WINDOW_DAYS days, one
     * request each.
     *
     * @param  list<string>  $days
     * @return list<array{0: string, 1: string}>
     */
    public static function windows(array $days): array
    {
        sort($days);
        $windows = [];
        foreach ($days as $day) {
            $last = array_key_last($windows);
            if ($last !== null && Carbon::parse($windows[$last][0])->diffInDays(Carbon::parse($day)) < self::WINDOW_DAYS) {
                $windows[$last][1] = $day;
            } else {
                $windows[] = [$day, $day];
            }
        }

        return $windows;
    }

    /**
     * Fetches the days $from to $until of a cell from the archive and stores
     * them; false when the request failed.
     */
    public static function fetch(int $cellLat, int $cellLon, string $from, string $until): bool
    {
        $failedKey = 'night_weather:failed:'.$cellLat.':'.$cellLon.':'.$from.':'.$until;
        if (self::paused() || Cache::has($failedKey)) {
            return false;
        }

        try {
            $response = Http::timeout(15)->acceptJson()->get(config('services.open_meteo.archive_url'), [
                'latitude'   => round($cellLat * self::GRID, 2),
                'longitude'  => round($cellLon * self::GRID, 2),
                'start_date' => $from,
                'end_date'   => $until,
                'hourly'     => implode(',', self::HOURLY),
                'timeformat' => 'unixtime',
                'timezone'   => 'UTC',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Open-Meteo archive failed', ['error' => $e->getMessage()]);
            Cache::put($failedKey, true, self::FAILURE_SECONDS);

            return false;
        }

        if ($response->status() === 429) {
            Cache::put(self::PAUSE_KEY, true, self::PAUSE_SECONDS);
        }

        $hourly = $response->successful() ? $response->json('hourly') : null;
        if (! is_array($hourly) || ! is_array($hourly['time'] ?? null)) {
            Log::warning('Open-Meteo archive failed', ['status' => $response->status()]);
            Cache::put($failedKey, true, self::FAILURE_SECONDS);

            return false;
        }

        $days = [];
        foreach ($hourly['time'] as $i => $timestamp) {
            $day  = gmdate('Y-m-d', (int) $timestamp);
            $hour = (int) gmdate('G', (int) $timestamp);
            foreach (self::HOURLY as $field) {
                $days[$day][$field] ??= array_fill(0, 24, null);
                $value                     = $hourly[$field][$i] ?? null;
                $days[$day][$field][$hour] = is_numeric($value) ? round((float) $value, 1) : null;
            }
        }

        $complete = now('UTC')->subDays(self::COMPLETE_AFTER_DAYS)->toDateString();
        $rows     = [];
        foreach ($days as $day => $values) {
            if ($day < $from || $day > $until) {
                continue;
            }
            $hasAll  = ! in_array(null, $values['cloud_cover'], true);
            $hasNone = array_filter($values['cloud_cover'], fn ($value) => $value !== null) === [];
            // The archive fills in the last days later: keep only what it will not change.
            if (! $hasAll && $day > $complete) {
                continue;
            }
            $rows[] = [
                'cell_lat'   => $cellLat,
                'cell_lon'   => $cellLon,
                'date'       => $day,
                'hourly'     => $hasNone ? null : json_encode($values),
                'fetched_at' => now(),
            ];
        }
        if ($rows !== []) {
            DB::table('night_weather')->upsert($rows, ['cell_lat', 'cell_lon', 'date'], ['hourly', 'fetched_at']);
        }

        return true;
    }

    /**
     * The window of a night: the dark hours (NightWindow), the given hours,
     * or NO_DARK_HOURS, as start and end timestamps and its kind
     * (astronomical, nautical, session or none).
     *
     * @param  array{0: int, 1: int}|null  $hours
     * @return array{0: int, 1: int, 2: string}
     */
    public static function window(float $latitude, float $longitude, string $timezone, string $night, ?array $hours = null): array
    {
        if ($hours !== null) {
            return [$hours[0], $hours[1], 'session'];
        }

        $window = NightWindow::for($latitude, $longitude, $night, $timezone);
        if ($window !== null) {
            return [$window->start, $window->end, $window->twilight];
        }

        $start = Carbon::parse($night.' '.self::NO_DARK_HOURS[0], $timezone);
        $end   = Carbon::parse($night.' '.self::NO_DARK_HOURS[1], $timezone)->addDay();

        return [$start->getTimestamp(), $end->getTimestamp(), 'none'];
    }

    /**
     * The UTC days from $start to $end (timestamps).
     *
     * @return list<string>
     */
    public static function days(int $start, int $end): array
    {
        $days = [];
        for ($day = Carbon::createFromTimestamp($start, 'UTC')->startOfDay(); $day->getTimestamp() <= $end; $day->addDay()) {
            $days[] = $day->toDateString();
        }

        return $days;
    }

    /**
     * The stored hours of the days, keyed by timestamp; null when one of
     * the days is not stored or the archive has nothing for it.
     *
     * @param  list<string>  $days
     * @return array<int, array<string, float|null>>|null
     */
    private static function hourly(float $latitude, float $longitude, array $days): ?array
    {
        [$cellLat, $cellLon] = self::cell($latitude, $longitude);
        $rows                = DB::table('night_weather')
            ->where('cell_lat', $cellLat)->where('cell_lon', $cellLon)
            ->whereIn('date', $days)
            ->get(['date', 'hourly'])
            ->keyBy(fn ($row) => substr((string) $row->date, 0, 10));

        $values = [];
        foreach ($days as $day) {
            $row = $rows->get($day);
            if ($row === null || $row->hourly === null) {
                return null;
            }
            $hourly   = json_decode($row->hourly, true);
            $midnight = Carbon::parse($day, 'UTC')->getTimestamp();
            for ($hour = 0; $hour < 24; $hour++) {
                foreach (self::HOURLY as $field) {
                    $values[$midnight + 3600 * $hour][$field] = $hourly[$field][$hour] ?? null;
                }
            }
        }
        ksort($values);

        return $values;
    }

    /**
     * @param  array<string, float|null>  $value
     * @return array{cloud: int, temperature: float|null, humidity: int|null, dew_point: float|null, wind: float|null}
     */
    private static function hour(array $value): array
    {
        return [
            'cloud'       => (int) round($value['cloud_cover']),
            'temperature' => $value['temperature_2m'],
            'humidity'    => $value['relative_humidity_2m'] === null ? null : (int) round($value['relative_humidity_2m']),
            'dew_point'   => $value['dew_point_2m'],
            'wind'        => $value['wind_speed_10m'],
        ];
    }

    private static function degrees(float $celsius): string
    {
        return (int) round($celsius).' °C';
    }
}
