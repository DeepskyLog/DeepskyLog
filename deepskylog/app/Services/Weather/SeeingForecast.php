<?php

namespace App\Services\Weather;

use App\Services\NightWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The seeing forecast of the coming nights, from two sources:
 *
 * - AstrArome (seeingthesky.com, by Clément Brustel, used with his
 *   permission): the seeing at the zenith in arcseconds, hour by hour in the
 *   night, from the AROME model of Météo-France calibrated on seeing
 *   monitors; only in the AROME domain (France and its neighbours: Benelux,
 *   western Germany, Denmark, Ireland, southern England, northern and
 *   central Spain) and about 34 hours ahead. As he asked: a cell's points
 *   are only asked again when /api/runs/latest has a new run (checked every
 *   RUN_CHECK_SECONDS), at most one request per services.astrarome.delay ms,
 *   with the User-Agent USER_AGENT.
 * - 7Timer's ASTRO product (free, without a key; from the GFS model, every 3
 *   hours for 72 hours), whose seeing index runs from 1 (better than 0.5″)
 *   to 8 (worse than 2.5″): everywhere else and for the later nights.
 *
 * A night takes AstrArome when it covers at least MIN_AROME_COVERAGE of its
 * hours, else 7Timer; the arcseconds of AstrArome are turned into the same
 * index (indexOf()), so the classes mean the same for both. Planets and
 * close double stars need steady air far more than clear air alone;
 * NightForecast shows it per night and TonightsBest favours them on a
 * steady night.
 *
 * Cached per cell of GRID degrees (7Timer, from the coarse GFS model) and
 * AROME_GRID degrees (AstrArome, from a model of 1.3 km; about 5 km, which
 * Clément agreed to: one point per cell, not an average). With $fetch false a forecast that is not
 * cached yet (or an AstrArome run that may be outdated) is fetched after the
 * response (defer()).
 */
class SeeingForecast
{
    /** The upper bound (arcseconds) of the seeing of each index; 8 is worse than 2.5″. */
    public const ARCSEC = [1 => 0.5, 2 => 0.75, 3 => 1.0, 4 => 1.25, 5 => 1.5, 6 => 2.0, 7 => 2.5];

    /**
     * A night whose average index is below this (rounded at most 4: better
     * than 1.25″) is steady,
     */
    public const STEADY_INDEX = 4.5;

    /** from this (rounded at least 7: worse than 2″) it is poor. */
    public const POOR_INDEX = 6.5;

    public const CLASSES = ['steady', 'average', 'poor'];

    /** Each forecast point stands for the hours this far before and after it. */
    private const POINT_SECONDS = 5400;

    private const GRID = 0.25;

    private const AROME_GRID = 0.05;

    private const CACHE_SECONDS = 3 * 3600;

    /** After a failed request (7Timer is often slow or down), nothing is asked for this long; */
    private const FAILURE_SECONDS = 900;

    /** after a refusal (rate limit), this long. */
    private const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'seeing_forecast:paused';

    public const SOURCES = ['astrarome', '7timer'];

    /** AstrArome's points are hourly: each stands for this far before and after it. */
    private const AROME_POINT_SECONDS = 1800;

    /** A night takes AstrArome when its points cover at least this part of the hours. */
    private const MIN_AROME_COVERAGE = 0.5;

    /** How long the latest AstrArome run is trusted before /api/runs/latest is asked again. */
    private const RUN_CHECK_SECONDS = 900;

    /** A cell's AstrArome points are kept this long (the forecast reaches about 34 hours). */
    private const AROME_KEEP_SECONDS = 36 * 3600;

    /** A cell outside the AROME domain is not asked again for this long. */
    private const AROME_OUTSIDE_SECONDS = 30 * 86400;

    /** How long a request waits for its turn before giving up (the cached forecast stays). */
    private const AROME_WAIT_SECONDS = 3;

    private const AROME_PAUSE_KEY = 'seeing_forecast:astrarome:paused';

    private const AROME_RUN_KEY = 'seeing_forecast:astrarome:run';

    private const AROME_LAST_KEY = 'seeing_forecast:astrarome:last_request';

    public const USER_AGENT = 'DeepskyLog (https://www.deepskylog.org)';

    /**
     * Whether any seeing forecast is on.
     */
    public static function enabled(): bool
    {
        return self::sevenTimerEnabled() || self::aromeEnabled();
    }

    public static function sevenTimerEnabled(): bool
    {
        return (bool) config('services.seven_timer.enabled', true);
    }

    public static function aromeEnabled(): bool
    {
        return (bool) config('services.astrarome.enabled', true);
    }

    /**
     * The credit of the sources (SOURCES) as HTML: "Seeing by <link>, <link>".
     * Without $sources the ones turned on; empty when there is none.
     *
     * @param  list<string>|null  $sources
     */
    public static function credit(?array $sources = null): string
    {
        $sources ??= array_values(array_filter(self::SOURCES, fn (string $source) => $source === 'astrarome' ? self::aromeEnabled() : self::sevenTimerEnabled()));
        $links = [];
        foreach (self::SOURCES as $source) {
            if (in_array($source, $sources, true)) {
                $links[] = $source === 'astrarome'
                    ? '<a href="https://seeingthesky.com/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">AstrArome</a> (AROME, Météo-France)'
                    : '<a href="https://www.7timer.info/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">7Timer!</a>';
            }
        }

        return $links === [] ? '' : __('Seeing by :link', ['link' => implode(', ', $links)]);
    }

    /**
     * The sources of the seeing of the nights (as NightForecast gives them).
     *
     * @param  iterable<array<string, mixed>>  $nights
     * @return list<string>
     */
    public static function sourcesOf(iterable $nights): array
    {
        $sources = [];
        foreach ($nights as $night) {
            if ($source = $night['seeing']['source'] ?? null) {
                $sources[$source] = true;
            }
        }

        return array_keys($sources);
    }

    /**
     * The seeing in the dark hours of a night (Y-m-d of its evening in
     * $timezone), as between() gives it; null without a forecast for them.
     *
     * @return array{index: float, class: string, arcsec: string}|null
     */
    public static function forNight(float $latitude, float $longitude, string $night, string $timezone, bool $fetch = true): ?array
    {
        $window = NightWindow::for($latitude, $longitude, $night, $timezone);
        $points = $window ? self::points($latitude, $longitude, $fetch) : null;

        return $points === null ? null : self::between($points, $window->start, $window->end);
    }

    /**
     * The average seeing index of the forecast points in the hours from
     * $from until $until (timestamps), each weighed by the part of those
     * hours it stands for, with its class and the seeing in arcseconds; null
     * when the forecast does not reach them. AstrArome when it covers at
     * least MIN_AROME_COVERAGE of them (the average of its arcseconds), else
     * 7Timer; 'source' says which.
     *
     * @param  array<string, mixed>  $points  as points() gives them
     * @return array{index: float, class: string, arcsec: string, source: string}|null
     */
    public static function between(array $points, int $from, int $until): ?array
    {
        $arome = $points['astrarome'] ?? null;
        if ($arome && $until > $from) {
            $sum     = 0.0;
            $seconds = 0;
            foreach ($arome['time'] as $i => $timestamp) {
                $overlap = min($timestamp + self::AROME_POINT_SECONDS, $until) - max($timestamp - self::AROME_POINT_SECONDS, $from);
                if ($overlap > 0) {
                    $sum     += $arome['arcsec'][$i] * $overlap;
                    $seconds += $overlap;
                }
            }
            if ($seconds > 0 && $seconds >= self::MIN_AROME_COVERAGE * ($until - $from)) {
                $arcsec = $sum / $seconds;
                $index  = round(self::indexOf($arcsec), 1);

                return ['index' => $index, 'class' => self::classOf($index), 'arcsec' => self::arcsecText($arcsec), 'source' => 'astrarome'];
            }
        }

        $sum     = 0.0;
        $seconds = 0;
        foreach ($points['time'] ?? [] as $i => $timestamp) {
            $overlap = min($timestamp + self::POINT_SECONDS, $until) - max($timestamp - self::POINT_SECONDS, $from);
            if ($overlap > 0) {
                $sum     += $points['seeing'][$i] * $overlap;
                $seconds += $overlap;
            }
        }
        if ($seconds === 0) {
            return null;
        }
        $index = round($sum / $seconds, 1);

        return ['index' => $index, 'class' => self::classOf($index), 'arcsec' => self::arcsec($index), 'source' => '7timer'];
    }

    /**
     * The seeing index of the forecast point that stands for the moment
     * (AstrArome's, as indexOf() its arcseconds, else 7Timer's), or null.
     *
     * @param  array<string, mixed>|null  $points  as points() gives them
     */
    public static function at(?array $points, int $timestamp): int|float|null
    {
        $arcsec = self::arcsecAt($points, $timestamp);
        if ($arcsec !== null) {
            return round(self::indexOf($arcsec), 1);
        }
        foreach ($points['time'] ?? [] as $i => $time) {
            if (abs($time - $timestamp) <= self::POINT_SECONDS) {
                return $points['seeing'][$i];
            }
        }

        return null;
    }

    /**
     * The seeing in arcseconds of AstrArome's point for the moment, or null.
     *
     * @param  array<string, mixed>|null  $points  as points() gives them
     */
    public static function arcsecAt(?array $points, int $timestamp): ?float
    {
        foreach ($points['astrarome']['time'] ?? [] as $i => $time) {
            if (abs($time - $timestamp) <= self::AROME_POINT_SECONDS) {
                return $points['astrarome']['arcsec'][$i];
            }
        }

        return null;
    }

    /**
     * The seeing index (1–8, fractional) of a seeing in arcseconds: within
     * the bin of ARCSEC it falls in (index k from ARCSEC[k-1] to ARCSEC[k])
     * linearly from k − 0.5 to k + 0.5, so the classes have the same bounds
     * in arcseconds as for 7Timer's index (steady up to 1.25″, poor from 2″).
     */
    public static function indexOf(float $arcsec): float
    {
        $low = 0.0;
        foreach (self::ARCSEC as $index => $up) {
            if ($arcsec <= $up) {
                return max(1.0, $index - 0.5 + ($arcsec - $low) / ($up - $low));
            }
            $low = $up;
        }

        // worse than 2.5″: index 8 from 2.5″ to 3″
        return min(8.0, 7.5 + ($arcsec - $low) / 0.5);
    }

    /**
     * A seeing in arcseconds as text: '1.3″'.
     */
    public static function arcsecText(float $arcsec): string
    {
        return number_format($arcsec, 1, '.', '').'″';
    }

    public static function classOf(float $index): string
    {
        return match (true) {
            $index < self::STEADY_INDEX => 'steady',
            $index >= self::POOR_INDEX  => 'poor',
            default                     => 'average',
        };
    }

    /**
     * The seeing of an index in arcseconds: '1–1.25″', '< 0.5″', '> 2.5″'.
     */
    public static function arcsec(float $index): string
    {
        $index = max(1, min(8, (int) round($index)));
        $up    = self::ARCSEC[$index]     ?? null;
        $low   = self::ARCSEC[$index - 1] ?? null;
        $text  = fn (float $value) => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return match (true) {
            $low === null => '< '.$text($up).'″',
            $up  === null => '> '.$text($low).'″',
            default       => $text($low).'–'.$text($up).'″',
        };
    }

    /**
     * The class in words.
     */
    public static function label(string $class, ?string $locale = null): string
    {
        return match ($class) {
            'steady' => __('Steady seeing', [], $locale),
            'poor'   => __('Poor seeing', [], $locale),
            default  => __('Average seeing', [], $locale),
        };
    }

    /**
     * The Tailwind background class of the class's badge.
     */
    public static function badgeClass(string $class): string
    {
        return match ($class) {
            'steady' => 'bg-green-900',
            'poor'   => 'bg-red-900',
            default  => 'bg-gray-600',
        };
    }

    /**
     * The seeing forecast of the cell of the point: 'time' (timestamps of
     * the points) and 'seeing' (index 1–8) of 7Timer (empty without its
     * forecast), 'astrarome' (null, or 'run', 'time' and 'arcsec' of
     * AstrArome's hourly points), and 'fetched_at' (the latest of both);
     * null when neither can be had.
     *
     * @return array{fetched_at: int, time: list<int>, seeing: list<int>, astrarome: ?array{run: int, time: list<int>, arcsec: list<float>}}|null
     */
    public static function points(float $latitude, float $longitude, bool $fetch = true): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        $cell  = fn (float $degrees, float $grid) => round(round($degrees / $grid) * $grid, 2);
        $seven = self::sevenTimerPoints($cell($latitude, self::GRID), $cell($longitude, self::GRID), $fetch);
        $arome = self::aromePoints($cell($latitude, self::AROME_GRID), $cell($longitude, self::AROME_GRID), $fetch);
        if ($seven === null && $arome === null) {
            return null;
        }

        return [
            'fetched_at' => max($seven['fetched_at'] ?? 0, $arome['fetched_at'] ?? 0),
            'time'       => $seven['time']   ?? [],
            'seeing'     => $seven['seeing'] ?? [],
            'astrarome'  => $arome === null ? null : ['run' => $arome['run'], 'time' => $arome['time'], 'arcsec' => $arome['arcsec']],
        ];
    }

    /**
     * 7Timer's forecast of the cell, cached CACHE_SECONDS.
     *
     * @return array{fetched_at: int, time: list<int>, seeing: list<int>}|null
     */
    private static function sevenTimerPoints(float $latitude, float $longitude, bool $fetch): ?array
    {
        if (! self::sevenTimerEnabled()) {
            return null;
        }

        $key    = 'seeing_forecast:points:'.$latitude.':'.$longitude;
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        if (! $fetch) {
            defer(fn () => self::sevenTimerPoints($latitude, $longitude, true), 'seeing_forecast:'.$key);

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
     * AstrArome's forecast of the cell: the cached one while the latest run
     * is the one it was asked for, else asked again (after the response
     * without $fetch, keeping the cached one meanwhile); null outside the
     * AROME domain or without a forecast.
     *
     * @return array{fetched_at: int, run: int, checked_run: int, time: list<int>, arcsec: list<float>}|null
     */
    private static function aromePoints(float $latitude, float $longitude, bool $fetch): ?array
    {
        if (! self::aromeEnabled()) {
            return null;
        }

        $key    = 'seeing_forecast:astrarome:points:'.$latitude.':'.$longitude;
        $cached = Cache::get($key);
        if (($cached['outside'] ?? false) === true) {
            return null;
        }
        $cached = is_array($cached) ? $cached : null;

        $run = self::latestRun($fetch);
        if ($cached !== null && $run !== null && $cached['checked_run'] >= $run) {
            return $cached;
        }

        if (! $fetch) {
            defer(fn () => self::aromePoints($latitude, $longitude, true), 'seeing_forecast:'.$key);

            return $cached;
        }

        if ($run === null || Cache::has(self::AROME_PAUSE_KEY)) {
            return $cached;
        }

        $forecast = self::fetchArome($latitude, $longitude, $run);
        if ($forecast === null) {
            return $cached;
        }
        if ($forecast === 'outside') {
            Cache::put($key, ['outside' => true], self::AROME_OUTSIDE_SECONDS);

            return null;
        }
        Cache::put($key, $forecast, self::AROME_KEEP_SECONDS);

        return $forecast;
    }

    /**
     * The start (timestamp) of AstrArome's latest run, checked every
     * RUN_CHECK_SECONDS; null when unknown (without $fetch: not checked
     * recently).
     */
    private static function latestRun(bool $fetch): ?int
    {
        $run = Cache::get(self::AROME_RUN_KEY);
        if ($run !== null || ! $fetch || Cache::has(self::AROME_PAUSE_KEY)) {
            return is_int($run) ? $run : null;
        }

        $response = self::aromeRequest('/api/runs/latest');
        $data     = $response?->successful() ? $response->json() : null;
        try {
            $run = is_array($data) && is_string($data['run_time'] ?? null) ? Carbon::parse($data['run_time'])->getTimestamp() : null;
        } catch (\Throwable) {
            $run = null;
        }
        if ($run === null) {
            // no turn to ask (null): not a failure, a later call asks again
            if ($response !== null) {
                Log::warning('AstrArome latest run failed', ['status' => $response->status()]);
                Cache::put(self::AROME_PAUSE_KEY, true, self::FAILURE_SECONDS);
            }

            return null;
        }
        Cache::put(self::AROME_RUN_KEY, $run, self::RUN_CHECK_SECONDS);

        return $run;
    }

    /**
     * AstrArome's hourly seeing at the point, 'outside' when it lies outside
     * the AROME domain, null on a failure.
     *
     * @return array{fetched_at: int, run: int, checked_run: int, time: list<int>, arcsec: list<float>}|'outside'|null
     */
    private static function fetchArome(float $latitude, float $longitude, int $latestRun): array|string|null
    {
        $response = self::aromeRequest('/api/point', ['lat' => number_format($latitude, 4, '.', ''), 'lon' => number_format($longitude, 4, '.', '')]);
        if ($response === null) {
            return null;
        }
        if ($response->status() === 404) {
            return 'outside';
        }

        $data = $response->successful() ? $response->json() : null;
        try {
            $run = is_array($data) && is_string($data['run_time'] ?? null) ? Carbon::parse($data['run_time'])->getTimestamp() : null;
        } catch (\Throwable) {
            $run = null;
        }
        if ($run === null || ! is_array($data['points'] ?? null)) {
            Log::warning('AstrArome seeing forecast failed', ['status' => $response->status()]);

            return null;
        }

        $forecast = ['fetched_at' => now()->getTimestamp(), 'run' => $run, 'checked_run' => max($run, $latestRun), 'time' => [], 'arcsec' => []];
        foreach ($data['points'] as $point) {
            $arcsec = $point['seeing_arcsec'] ?? null;
            try {
                $time = is_string($point['valid_time'] ?? null) ? Carbon::parse($point['valid_time'])->getTimestamp() : null;
            } catch (\Throwable) {
                $time = null;
            }
            // the open sea away from the coast is not computed
            if ($time !== null && is_numeric($arcsec) && $arcsec > 0) {
                $forecast['time'][]   = $time;
                $forecast['arcsec'][] = round((float) $arcsec, 3);
            }
        }

        return $forecast;
    }

    /**
     * A GET request to AstrArome in its turn: at most one per
     * services.astrarome.delay ms over all processes (a cache lock and the
     * moment of the last request), giving up (null) after waiting
     * AROME_WAIT_SECONDS. A failure or refusal pauses all requests.
     *
     * @param  array<string, string>  $query
     */
    private static function aromeRequest(string $path, array $query = []): ?\Illuminate\Http\Client\Response
    {
        $lock = Cache::lock('seeing_forecast:astrarome:lock', 30);
        if (! $lock->block(self::AROME_WAIT_SECONDS)) {
            return null;
        }

        try {
            $wait = (float) Cache::get(self::AROME_LAST_KEY, 0) + config('services.astrarome.delay', 1000) / 1000 - microtime(true);
            if ($wait > 0) {
                usleep((int) ($wait * 1_000_000));
            }

            try {
                $response = Http::timeout(10)->withUserAgent(self::USER_AGENT)->acceptJson()
                    ->get(rtrim(config('services.astrarome.url'), '/').$path, $query);
            } catch (\Throwable $e) {
                Log::warning('AstrArome seeing forecast failed', ['error' => $e->getMessage()]);
                Cache::put(self::AROME_PAUSE_KEY, true, self::FAILURE_SECONDS);

                return null;
            } finally {
                Cache::put(self::AROME_LAST_KEY, microtime(true), 60);
            }

            if (in_array($response->status(), [403, 429], true)) {
                Cache::put(self::AROME_PAUSE_KEY, true, self::PAUSE_SECONDS);
            } elseif ($response->serverError()) {
                Cache::put(self::AROME_PAUSE_KEY, true, self::FAILURE_SECONDS);
            }

            return $response;
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array{fetched_at: int, time: list<int>, seeing: list<int>}|null
     */
    private static function fetch(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(10)->get(config('services.seven_timer.url'), [
                'lon'     => $longitude,
                'lat'     => $latitude,
                'ac'      => 0,
                'unit'    => 'metric',
                'output'  => 'json',
                'tzshift' => 0,
            ]);
        } catch (\Throwable $e) {
            Log::warning('7Timer seeing forecast failed', ['error' => $e->getMessage()]);
            Cache::put(self::PAUSE_KEY, true, self::FAILURE_SECONDS);

            return null;
        }

        if ($response->status() === 429) {
            Cache::put(self::PAUSE_KEY, true, self::PAUSE_SECONDS);
        } elseif ($response->serverError()) {
            Cache::put(self::PAUSE_KEY, true, self::FAILURE_SECONDS);
        }

        // 7Timer answers JSON as text/plain; the start of the run is YYYYMMDDHH in UT
        $data = $response->successful() ? json_decode($response->body(), true) : null;
        try {
            $init = is_array($data) && is_string($data['init'] ?? null) ? Carbon::createFromFormat('YmdH|', $data['init'], 'UTC') : null;
        } catch (\Throwable) {
            $init = null;
        }
        if (! $init || ! is_array($data['dataseries'] ?? null)) {
            Log::warning('7Timer seeing forecast failed', ['status' => $response->status()]);

            return null;
        }

        $forecast = ['fetched_at' => now()->getTimestamp(), 'time' => [], 'seeing' => []];
        foreach ($data['dataseries'] as $point) {
            $seeing = $point['seeing'] ?? null;
            // -9999: no value
            if (is_numeric($point['timepoint'] ?? null) && is_int($seeing) && $seeing >= 1 && $seeing <= 8) {
                $forecast['time'][]   = $init->copy()->addHours((int) $point['timepoint'])->getTimestamp();
                $forecast['seeing'][] = $seeing;
            }
        }

        return $forecast['time'] === [] ? null : $forecast;
    }
}
