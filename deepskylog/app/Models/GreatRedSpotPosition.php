<?php

namespace App\Models;

use App\Services\News\SkyPositions;
use App\Services\Planets\PlanetMoonEvents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * A measurement of the System II longitude of Jupiter's Great Red Spot,
 * entered on /admin/great-red-spot, with the drift (degrees a year) that
 * carries it forward. The newest measurement (current()) gives the moments
 * the spot crosses the central meridian; without one PlanetMoonEvents uses
 * its GRS_* constants. The administrators and database experts are reminded
 * every month to check it (planets:grs-reminder).
 */
class GreatRedSpotPosition extends Model
{
    private const CACHE_KEY = 'great_red_spot_position:current';

    protected $fillable = ['longitude', 'measured_on', 'drift', 'source', 'user_id'];

    protected $casts = [
        'longitude'   => 'float',
        'drift'       => 'float',
        'measured_on' => 'date',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The newest measurement as [longitude, timestamp of the date, drift,
     * source, date], or the constants of PlanetMoonEvents when there is none
     * (or no table yet). Cached until a measurement is saved or deleted.
     *
     * @return array{longitude: float, epoch: int, drift: float, source: ?string, measured_on: string}
     */
    public static function current(): array
    {
        $fallback = [
            'longitude'   => PlanetMoonEvents::GRS_LONGITUDE,
            'epoch'       => Carbon::parse(PlanetMoonEvents::GRS_EPOCH, 'UTC')->getTimestamp(),
            'drift'       => PlanetMoonEvents::GRS_DRIFT,
            'source'      => null,
            'measured_on' => PlanetMoonEvents::GRS_EPOCH,
        ];

        try {
            return Cache::rememberForever(self::CACHE_KEY, function () use ($fallback) {
                $latest = self::orderByDesc('measured_on')->orderByDesc('id')->first();

                return $latest === null ? $fallback : [
                    'longitude'   => $latest->longitude,
                    'epoch'       => Carbon::parse($latest->measured_on->toDateString(), 'UTC')->getTimestamp(),
                    'drift'       => $latest->drift,
                    'source'      => $latest->source,
                    'measured_on' => $latest->measured_on->toDateString(),
                ];
            });
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /** The longitude this measurement predicts at a moment. */
    public function longitudeAt(int $timestamp): float
    {
        $years = ($timestamp - Carbon::parse($this->measured_on->toDateString(), 'UTC')->getTimestamp()) / (365.25 * 86400);

        return SkyPositions::wrap($this->longitude + $this->drift * $years);
    }
}
