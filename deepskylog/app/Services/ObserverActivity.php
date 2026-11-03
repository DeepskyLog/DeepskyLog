<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Moon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The observing activity calendar of the observer page: the nights with
 * observations (the evening dates of ObserverBlog::night()) with the number
 * of different objects seen and of observations, laid out per year as weeks
 * of Monday to Sunday, with the nights of the new Moons.
 *
 * The nights are cached under a key of the count, last id and last change of
 * the observer's observations, so a new, edited or deleted observation shows
 * at once.
 */
class ObserverActivity
{
    /** The nights are kept this long under one key. */
    private const CACHE_DAYS = 7;

    /**
     * A night with at least this many different objects gets level 2, 3 and
     * 4; a night with fewer (also none seen) gets level 1.
     */
    public const LEVELS = [3, 6, 11];

    /** @var array<string, array{objects: int, observations: int}>|null */
    private ?array $nights = null;

    public function __construct(private readonly User $observer)
    {
    }

    /**
     * The nights with observations, oldest first.
     *
     * @return array<string, array{objects: int, observations: int}>
     */
    public function nights(): array
    {
        if ($this->nights !== null) {
            return $this->nights;
        }

        $state = DB::table('observations')
            ->where('observer_username', $this->observer->username)
            ->selectRaw('COUNT(*) AS total, MAX(id) AS last_id, MAX(updated_at) AS changed')
            ->first();
        $key = 'observer_activity:' . $this->observer->id . ':' . md5(implode('|', [$state->total ?? 0, $state->last_id ?? 0, $state->changed ?? '']));

        return $this->nights = Cache::remember($key, now()->addDays(self::CACHE_DAYS), fn () => $this->compute());
    }

    /**
     * @return array<string, array{objects: int, observations: int}>
     */
    private function compute(): array
    {
        $rows = Observation::query()
            ->where('observations.observer_username', $this->observer->username)
            ->whereNotNull('observations.observed_on')
            ->leftJoin('deepsky_observation_details as details', 'details.observation_id', '=', 'observations.id')
            ->get([
                'observations.id', 'observations.observed_on', 'observations.observed_time', 'observations.legacy_time_int',
                'observations.location_id', 'observations.different_object_key', 'details.visibility as activity_visibility',
            ]);

        $locations = Location::whereIn('id', $rows->pluck('location_id')->filter()->unique())->get()->keyBy('id');
        $blog      = new ObserverBlog($this->observer);

        $nights = [];
        foreach ($rows as $row) {
            $night                          = $blog->night($row, $locations->get($row->location_id));
            $nights[$night]['observations'] = ($nights[$night]['observations'] ?? 0) + 1;
            $nights[$night]['objects'] ??= [];
            if ((int) $row->activity_visibility !== Observation::VISIBILITY_NOT_SEEN && $row->different_object_key !== null) {
                $nights[$night]['objects'][$row->different_object_key] = true;
            }
        }
        ksort($nights);

        return array_map(fn (array $night) => [
            'objects'      => count($night['objects']),
            'observations' => $night['observations'],
        ], $nights);
    }

    /**
     * The years with observations, newest first.
     *
     * @return list<int>
     */
    public function years(): array
    {
        $years = array_values(array_unique(array_map(fn (string $night) => (int) substr($night, 0, 4), array_keys($this->nights()))));
        rsort($years);

        return $years;
    }

    /**
     * The calendar of a year: its weeks (Monday to Sunday, the days outside
     * the year null), each day with its night, level (0 without observations)
     * and whether the Moon is new that night; the first day of each month
     * with the week it starts in; and the totals of the year.
     *
     * @return array{weeks: list<list<?array{date: string, objects: int, observations: int, level: int, new_moon: bool}>>, months: list<array{week: int, month: int}>, nights: int, objects: int, observations: int}
     */
    public function year(int $year): array
    {
        $nights   = $this->nights();
        $newMoons = array_flip(self::newMoonNights($year));
        $first    = Carbon::create($year, 1, 1)->startOfDay();
        $last     = Carbon::create($year, 12, 31)->startOfDay();
        $day      = $first->copy()->startOfWeek(Carbon::MONDAY);

        $weeks  = [];
        $months = [];
        $totals = ['nights' => 0, 'objects' => 0, 'observations' => 0];
        while ($day->lte($last)) {
            $week = [];
            for ($weekday = 0; $weekday < 7; $weekday++, $day->addDay()) {
                if ($day->lt($first) || $day->gt($last)) {
                    $week[] = null;

                    continue;
                }
                if ($day->day === 1) {
                    $months[] = ['week' => count($weeks), 'month' => $day->month];
                }
                $date  = $day->toDateString();
                $night = $nights[$date] ?? null;
                if ($night !== null) {
                    $totals['nights']++;
                    $totals['objects']      += $night['objects'];
                    $totals['observations'] += $night['observations'];
                }
                $week[] = [
                    'date'         => $date,
                    'objects'      => $night['objects']      ?? 0,
                    'observations' => $night['observations'] ?? 0,
                    'level'        => $night === null ? 0 : self::level($night['objects']),
                    'new_moon'     => isset($newMoons[$date]),
                ];
            }
            $weeks[] = $week;
        }

        return ['weeks' => $weeks, 'months' => $months] + $totals;
    }

    /**
     * The level (1 to 4) of a night with observations and this many
     * different objects seen.
     */
    public static function level(int $objects): int
    {
        $level = 1;
        foreach (self::LEVELS as $minimum) {
            if ($objects >= $minimum) {
                $level++;
            }
        }

        return $level;
    }

    /**
     * The nights (evening dates) of the new Moons of a year: the UT date of
     * twelve hours before the new Moon, so a new Moon in the morning belongs
     * to the night before. For Europe that is the night it falls in.
     *
     * @return list<string>
     */
    public static function newMoonNights(int $year): array
    {
        return Cache::rememberForever('new_moon_nights:' . $year, function () use ($year) {
            $moon   = new Moon();
            $date   = Carbon::create($year, 1, 1, 12, 0, 0, 'UTC')->subDays(2);
            $end    = Carbon::create($year + 1, 1, 1, 12, 0, 0, 'UTC');
            $nights = [];
            while (true) {
                $newMoon = $moon->newMoonDate($date->copy());
                if ($newMoon->gte($end)) {
                    break;
                }
                $night = $newMoon->copy()->subHours(12)->toDateString();
                if (str_starts_with($night, (string) $year)) {
                    $nights[] = $night;
                }
                $date = $newMoon->copy()->addDay();
            }

            return $nights;
        });
    }

    /**
     * The blog page that starts with a night.
     */
    public function blogUrl(string $night): string
    {
        return route('observer.blog', [
            'observer' => $this->observer->slug,
            'before'   => Carbon::parse($night)->addDay()->toDateString(),
        ]);
    }
}
