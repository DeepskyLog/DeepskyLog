<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "On this night", the home page box with what the observer observed in the
 * night of this date in earlier years: per year the observations of that night
 * (the evening date of ObserverBlog::night()), the different objects seen and
 * a drawing, linked to the blog page starting with that night.
 *
 * The observations are read with the (observer_username, observed_on) index:
 * observed_on is a UT date, so a night can be stored on the day before (far
 * east) or after (west, after midnight UT) its evening; those dates are read
 * and ObserverBlog::night() keeps the observations of the night itself.
 */
class OnThisNight
{
    /** At most this many earlier years are shown, the most recent first. */
    public const MAX_YEARS = 5;

    /** The objects named per year. */
    public const OBJECTS_SHOWN = 6;

    /** Before this local hour, "tonight" is still the night that began yesterday. */
    private const MORNING_ENDS = 12;

    /**
     * The earlier nights of the observer on tonight's date, newest first, or
     * null when there are none.
     *
     * @return array{night: string, years: list<array{year: int, night: string, years_ago: int, observations: int, objects: list<array{name: string, url: ?string}>, more: int, drawing: ?Observation, blog_url: string}>}|null
     */
    public static function forHomePage(User $user): ?array
    {
        $night = self::tonight($user);
        $years = self::years($user, $night);

        return $years === [] ? null : ['night' => $night->toDateString(), 'years' => $years];
    }

    /**
     * The evening date of the current night in the time zone of the observer's
     * standard location (else the site's).
     */
    public static function tonight(User $user): Carbon
    {
        $now = now()->setTimezone($user->standardLocation?->timezone ?: config('app.timezone'));

        return ($now->hour < self::MORNING_ENDS ? $now->copy()->subDay() : $now)->startOfDay();
    }

    /**
     * @return list<array{year: int, night: string, years_ago: int, observations: int, objects: list<array{name: string, url: ?string}>, more: int, drawing: ?Observation, blog_url: string}>
     */
    public static function years(User $user, Carbon $night): array
    {
        $first = DB::table('observations')->where('observer_username', $user->username)->whereNotNull('observed_on')->min('observed_on');
        if ($first === null) {
            return [];
        }

        // The night's date in every earlier year it exists (29 February only
        // in leap years), with the day before and after.
        $nights = [];
        for ($year = $night->year - 1; $year >= (int) substr((string) $first, 0, 4) - 1; $year--) {
            if (checkdate($night->month, $night->day, $year)) {
                $nights[] = Carbon::create($year, $night->month, $night->day)->toDateString();
            }
        }
        if ($nights === []) {
            return [];
        }
        $dates = collect($nights)
            ->flatMap(fn (string $date) => [
                Carbon::parse($date)->subDay()->toDateString(),
                $date,
                Carbon::parse($date)->addDay()->toDateString(),
            ])
            ->all();

        $observations = Observation::query()
            ->where('observations.observer_username', $user->username)
            ->whereIn('observations.observed_on', $dates)
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->select('observations.*', 'details.visibility as night_visibility')
            ->orderBy('observations.observed_on')
            ->orderBy('observations.observed_time')
            ->orderBy('observations.id')
            ->get();
        if ($observations->isEmpty()) {
            return [];
        }

        $cards = ObservationCardData::instance()->preload($observations);
        $blog  = new ObserverBlog($user);

        return $observations->toBase()
            ->groupBy(fn (Observation $observation) => $blog->night($observation, $cards->location($observation->location_id)))
            ->only($nights)
            ->sortKeysDesc()
            ->take(self::MAX_YEARS)
            ->map(fn (Collection $observations, string $date) => self::year($user, $night, $date, $observations, $cards))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Observation>  $observations
     */
    private static function year(User $user, Carbon $tonight, string $date, Collection $observations, ObservationCardData $cards): array
    {
        $objects = [];
        foreach ($observations as $observation) {
            if ((int) $observation->night_visibility === Observation::VISIBILITY_NOT_SEEN) {
                continue;
            }
            $key = $observation->different_object_key ?? $observation->target_type . ':' . $observation->target_key;
            if (isset($objects[$key])) {
                continue;
            }
            $comet         = $observation->target_type === Observation::TYPE_COMET && is_numeric($observation->target_key)
                ? $cards->comet($observation->target_key)
                : null;
            $objects[$key] = [
                'name' => html_entity_decode($comet?->name ?? (string) $observation->target_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url'  => $observation->detailUrl($user->slug),
            ];
        }

        $year = (int) substr($date, 0, 4);

        return [
            'year'         => $year,
            'night'        => $date,
            'years_ago'    => $tonight->year - $year,
            'observations' => $observations->count(),
            'objects'      => array_slice(array_values($objects), 0, self::OBJECTS_SHOWN),
            'more'         => max(0, count($objects) - self::OBJECTS_SHOWN),
            'drawing'      => $observations->first(fn (Observation $observation) => (bool) $observation->has_drawing),
            'blog_url'     => route('observer.blog', [
                'observer' => $user->slug,
                'before'   => Carbon::parse($date)->addDay()->toDateString(),
            ]),
        ];
    }
}
