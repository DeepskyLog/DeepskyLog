<?php

namespace App\Services\Weather;

use App\Services\NightWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The seeing forecast of the coming nights: the ASTRO product of 7Timer
 * (free, without a key; from the GFS model, every 3 hours for 72 hours),
 * whose seeing index runs from 1 (better than 0.5″) to 8 (worse than 2.5″).
 * Planets and close double stars need steady air far more than clear air
 * alone; NightForecast shows it per night and TonightsBest favours them on
 * a steady night.
 *
 * Cached per cell of GRID degrees for CACHE_SECONDS. With $fetch false a
 * forecast that is not cached yet is fetched after the response (defer()).
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

    private const CACHE_SECONDS = 3 * 3600;

    /** After a failed request (7Timer is often slow or down), nothing is asked for this long; */
    private const FAILURE_SECONDS = 900;

    /** after a refusal (rate limit), this long. */
    private const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'seeing_forecast:paused';

    public static function enabled(): bool
    {
        return (bool) config('services.seven_timer.enabled', true);
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
     * when the forecast does not reach them.
     *
     * @param  array<string, mixed>  $points  as points() gives them
     * @return array{index: float, class: string, arcsec: string}|null
     */
    public static function between(array $points, int $from, int $until): ?array
    {
        $sum     = 0.0;
        $seconds = 0;
        foreach ($points['time'] as $i => $timestamp) {
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

        return ['index' => $index, 'class' => self::classOf($index), 'arcsec' => self::arcsec($index)];
    }

    /**
     * The seeing index of the forecast point that stands for the moment, or
     * null.
     *
     * @param  array<string, mixed>|null  $points  as points() gives them
     */
    public static function at(?array $points, int $timestamp): ?int
    {
        foreach ($points['time'] ?? [] as $i => $time) {
            if (abs($time - $timestamp) <= self::POINT_SECONDS) {
                return $points['seeing'][$i];
            }
        }

        return null;
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
     * the points) and 'seeing' (index 1–8), and 'fetched_at'; null when it
     * can't be had.
     *
     * @return array{fetched_at: int, time: list<int>, seeing: list<int>}|null
     */
    public static function points(float $latitude, float $longitude, bool $fetch = true): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        $latitude  = round(round($latitude / self::GRID) * self::GRID, 2);
        $longitude = round(round($longitude / self::GRID) * self::GRID, 2);
        $key       = 'seeing_forecast:points:'.$latitude.':'.$longitude;

        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        if (! $fetch) {
            defer(fn () => self::points($latitude, $longitude), 'seeing_forecast:'.$key);

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
