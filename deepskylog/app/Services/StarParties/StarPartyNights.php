<?php

namespace App\Services\StarParties;

use App\Models\StarParty;
use App\Services\MoonlightSky;
use App\Services\NightWindow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The nights of a starparty (#315) at its site: the dark hours (between the
 * ends of astronomical twilight, else nautical twilight), the Moon in them,
 * and how long it is dark without the Moon. Cached per site and night.
 */
class StarPartyNights
{
    private const CACHE_SECONDS = 86400 * 7;

    /**
     * @return list<array{night: string, dark: ?array{from: string, until: string, twilight: string},
     *     moon: ?array{fraction: int, from: string, until: string}, moonless_minutes: int}>
     */
    public static function for(StarParty $party): array
    {
        return array_map(fn (string $night) => self::night($party, $night), $party->nights());
    }

    /**
     * @return array{night: string, dark: ?array{from: string, until: string, twilight: string},
     *     moon: ?array{fraction: int, from: string, until: string}, moonless_minutes: int}
     */
    public static function night(StarParty $party, string $night): array
    {
        $timezone = $party->timezone ?: 'UTC';
        $key      = 'star_party_night:'.md5(json_encode([round($party->latitude, 4), round($party->longitude, 4), $timezone, $night]));

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($party, $night, $timezone) {
            $result = ['night' => $night, 'dark' => null, 'moon' => null, 'moonless_minutes' => 0];
            $window = NightWindow::for($party->latitude, $party->longitude, $night, $timezone);
            if ($window === null) {
                return $result;
            }

            $time           = fn (int $timestamp) => Carbon::createFromTimestamp($timestamp, $timezone)->format('H:i');
            $result['dark'] = ['from' => $time($window->start), 'until' => $time($window->end), 'twilight' => $window->twilight];
            $moonless       = $window->end - $window->start;

            $moon = new MoonlightSky($window, $party->longitude, (float) ($party->elevation ?? 0.0));
            if ($up = $moon->upBetween()) {
                $result['moon'] = ['fraction' => (int) round($moon->illuminatedFraction() * 100), 'from' => $time($up[0]), 'until' => $time($up[1])];
                $moonless -= $up[1] - $up[0];
            }
            $result['moonless_minutes'] = max(0, intdiv($moonless, 60));

            return $result;
        });
    }
}
