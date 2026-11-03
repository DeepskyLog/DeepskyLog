<?php

namespace App\Services\Weather;

use App\Models\Location;
use App\Models\StarParty;
use App\Models\User;
use App\Services\LocationSkyHistory;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Which of an observer's sites is best in each of the coming nights: the
 * forecast of NightForecast (clouds, Moon, seeing) at each of their active
 * locations, with the typical sky of the site, as a score from 0 to 100.
 *
 * For deep-sky objects (MODE_DEEPSKY) the score is the clear hours of the
 * night (an hour with a bright Moon or in twilight counts for
 * MOONLIT_WEIGHT: NightForecast's clear_dark_minutes leave both out), up to
 * FULL_HOURS, times the darkness of the sky, linear in the SQM from
 * SQM_POOR to SQM_DARK. For planets and double stars (MODE_PLANETS) the
 * Moon and the sky do not matter: the clear hours times SEEING_WEIGHTS.
 *
 * The sky of a site is the median SQM the observer recorded at it in the
 * last RECENT_YEARS (LocationSkyHistory, at least MIN_NIGHTS nights), else
 * the SQM or NELM of the location, else DEFAULT_SQM (source 'unknown').
 */
class SiteComparison
{
    public const MODE_DEEPSKY = 'deepsky';

    public const MODE_PLANETS = 'planets';

    public const MODES = [self::MODE_DEEPSKY, self::MODE_PLANETS];

    /** The clear hours of a perfect night. */
    public const FULL_HOURS = 6;

    /** What a clear hour with a bright Moon is worth for deep-sky objects. */
    public const MOONLIT_WEIGHT = 0.3;

    /** The SQM of a sky too bright to be worth more (darkness 0.1), */
    public const SQM_POOR = 18.0;

    /** and of a sky as dark as it gets (darkness 1). */
    public const SQM_DARK = 21.75;

    /** The sky of a site without any. */
    public const DEFAULT_SQM = 20.0;

    public const RECENT_YEARS = 5;

    public const MIN_NIGHTS = 3;

    /** The seeing for planets; a night without a seeing forecast counts as average. */
    public const SEEING_WEIGHTS = ['steady' => 1.0, 'average' => 0.7, 'poor' => 0.4];

    /** The sites compared, nearest to the standard location first. */
    public const MAX_SITES = 20;

    /** The distances (km from the standard location) the page offers; null is all. */
    public const RADII = [50, 100, 250, 500];

    /**
     * The observer's active locations and their standard location, with
     * coordinates, nearest to the standard location first (it first), at
     * most MAX_SITES, within $radiusKm of it when given.
     *
     * @return Collection<int, array{location: Location, distance: ?float}>
     */
    public static function sites(User $user, ?float $radiusKm = null): Collection
    {
        $home = self::home($user);

        return Location::where('user_id', $user->id)
            ->where(fn ($query) => $query->where('active', true)->orWhere('id', $home?->id))
            ->get()
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->map(fn (Location $location) => [
                'location' => $location,
                'distance' => $home ? StarParty::distanceKm((float) $home->latitude, (float) $home->longitude, (float) $location->latitude, (float) $location->longitude) : null,
            ])
            ->filter(fn (array $site) => $radiusKm === null || $site['distance'] === null || $site['distance'] <= $radiusKm)
            ->sortBy(fn (array $site) => [$site['location']->id === $home?->id ? 0 : 1, $site['distance'] ?? 0, $site['location']->name])
            ->take(self::MAX_SITES)
            ->values();
    }

    /** The standard location, when it has coordinates. */
    public static function home(User $user): ?Location
    {
        $home = $user->standardLocation;

        return $home && $home->hasValidCoordinates() ? $home : null;
    }

    /**
     * The coming nights (Y-m-d of their evening) at the standard location,
     * else at the first site.
     *
     * @return list<string>
     */
    public static function nights(User $user, ?Location $reference = null): array
    {
        $reference ??= self::home($user) ?? self::sites($user)->first()['location'] ?? null;
        if (! $reference) {
            return [];
        }

        $timezone = EphemerisDate::timezone($reference);
        $first    = Carbon::parse(NightForecast::firstNight((float) $reference->latitude, (float) $reference->longitude, $timezone), $timezone);

        return array_map(fn (int $i) => $first->copy()->addDays($i)->toDateString(), range(0, NightForecast::NIGHTS - 1));
    }

    /**
     * The sites with their sky and, per night, the forecast and score. A
     * site whose forecast is not there (with $fetch false: not cached yet)
     * has null nights.
     *
     * @param  list<string>  $nights
     * @return list<array{location: Location, distance: ?float, sky: array{sqm: float, source: string}, nights: ?array<string, array{forecast: array<string, mixed>, score: ?int}>}>
     */
    public static function compare(User $user, array $nights, string $mode = self::MODE_DEEPSKY, bool $fetch = false, ?float $radiusKm = null): array
    {
        return self::sites($user, $radiusKm)
            ->map(function (array $site) use ($nights, $mode, $fetch) {
                $location = $site['location'];
                $sky      = self::sky($location);
                $forecast = NightForecast::forNights(
                    (float) $location->latitude,
                    (float) $location->longitude,
                    (float) ($location->elevation ?? 0.0),
                    EphemerisDate::timezone($location),
                    $nights,
                    $fetch,
                );

                return $site + [
                    'sky'    => $sky,
                    'nights' => $forecast === null ? null : array_map(
                        fn (array $night) => ['forecast' => $night, 'score' => self::score($night, $sky['sqm'], $mode)],
                        $forecast,
                    ),
                ];
            })
            ->all();
    }

    /**
     * The sites of compare() that have a score in the night, best first
     * (on a tie the nearer one).
     *
     * @param  list<array<string, mixed>>  $sites  as compare() gives them
     * @return list<array<string, mixed>>
     */
    public static function ranked(array $sites, string $night): array
    {
        $scored = array_values(array_filter($sites, fn (array $site) => ($site['nights'][$night]['score'] ?? null) !== null));
        usort($scored, fn (array $a, array $b) => [$b['nights'][$night]['score'], $a['distance'] ?? 0] <=> [$a['nights'][$night]['score'], $b['distance'] ?? 0]);

        return $scored;
    }

    /**
     * The score of a night (0–100) at a site with the sky $sqm, or null
     * beyond the forecast.
     *
     * @param  array<string, mixed>  $night  as NightForecast::night() gives it
     */
    public static function score(array $night, float $sqm, string $mode = self::MODE_DEEPSKY): ?int
    {
        if ($night['verdict'] === 'unknown') {
            return null;
        }
        if ($night['verdict'] === 'no_dark') {
            return 0;
        }

        $clear = ($night['clear_minutes'] ?? 0) / 60;
        if ($mode === self::MODE_PLANETS) {
            $seeing = self::SEEING_WEIGHTS[$night['seeing']['class'] ?? 'average'] ?? self::SEEING_WEIGHTS['average'];

            return (int) round(100 * min(1, $clear / self::FULL_HOURS) * $seeing);
        }

        $dark     = ($night['clear_dark_minutes'] ?? 0) / 60;
        $hours    = $dark + self::MOONLIT_WEIGHT * max(0, $clear - $dark);
        $darkness = max(0.1, min(1, ($sqm - self::SQM_POOR) / (self::SQM_DARK - self::SQM_POOR)));

        return (int) round(100 * min(1, $hours / self::FULL_HOURS) * $darkness);
    }

    /**
     * The typical sky of a site: the SQM and where it comes from ('measured',
     * 'location' or 'unknown').
     *
     * @return array{sqm: float, source: string}
     */
    public static function sky(Location $location): array
    {
        $from   = (int) now()->year - self::RECENT_YEARS + 1;
        $values = array_map(
            fn (array $night) => $night['sqm'],
            array_filter(LocationSkyHistory::forLocation($location)->nights, fn (array $night) => (int) substr($night['date'], 0, 4) >= $from && $night['sqm'] !== null),
        );
        if (count($values) >= self::MIN_NIGHTS) {
            return ['sqm' => LocationSkyHistory::median($values, 2), 'source' => 'measured'];
        }

        $sqm = $location->getSqm();
        if (is_numeric($sqm) && $sqm > 0) {
            return ['sqm' => round((float) $sqm, 2), 'source' => 'location'];
        }

        return ['sqm' => self::DEFAULT_SQM, 'source' => 'unknown'];
    }

    /** The Tailwind background class of a score. */
    public static function scoreClass(?int $score): string
    {
        return match (true) {
            $score === null => 'bg-gray-800 text-gray-500',
            $score >= 60    => 'bg-green-700 text-white',
            $score >= 35    => 'bg-yellow-700 text-white',
            $score >= 15    => 'bg-blue-800 text-white',
            default         => 'bg-gray-700 text-gray-300',
        };
    }
}
