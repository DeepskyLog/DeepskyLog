<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The times of an observing list for one night: proposed for the hours the
 * observer chose (else the dark hours, NightWindow) at a location, with
 * NightPlanner, so each object is observed for $minutes while it stands at
 * least $minAltitude high, with $gap minutes to move to the next one. Saving them gives each item its time and window
 * (observing_list_items.planned_*), puts the items in that order and keeps
 * the night, time zone and location on the list; saveAsNew() saves them as
 * a new list instead. Objects without a position
 * (an unknown name, a comet without orbital elements), never high enough in
 * those hours or for which there is no time left get no time.
 */
class ObservingListTimes
{
    public const MIN_ALTITUDE = 20;

    public const MINUTES_PER_OBJECT = 10;

    /** The default minutes to move to the next object and find it. */
    public const MINUTES_BETWEEN = 0;

    /**
     * The window of the night (Y-m-d, its evening) at the location: from and
     * until the local times (H:i) given, else the dark hours. Null when it
     * does not get dark and no hours are given.
     */
    public static function window(Location $location, string $night, ?string $from = null, ?string $until = null): ?NightWindow
    {
        $timezone = EphemerisDate::timezone($location);
        if ($from === null || $until === null) {
            return NightWindow::for((float) $location->latitude, (float) $location->longitude, $night, $timezone);
        }

        $start = self::moment($night, $from, $timezone);
        $end   = self::moment($night, $until, $timezone);
        if ($end <= $start) {
            $end += 86400;
        }

        return NightWindow::span((float) $location->latitude, (float) $location->longitude, $start, $end);
    }

    /**
     * The moment (timestamp) of a local time (H:i) in the night: a time
     * before noon is in the morning after the evening of $night.
     */
    public static function moment(string $night, string $time, string $timezone): int
    {
        $moment = Carbon::parse($night.' '.$time, $timezone);
        if ((int) substr($time, 0, 2) < 12) {
            $moment->addDay();
        }

        return $moment->getTimestamp();
    }

    /**
     * The proposed times: for each object with a time, in the order to
     * observe them, the moment, its window and its altitude then; the
     * objects without one with the reason ('unknown', 'never' or 'time').
     * Null when there is no window.
     *
     * @return array{night: string, start: int, end: int, timezone: string, objects: list<array{name: string, at: int, from: int, until: int, altitude: float}>, missed: list<array{name: string, reason: string}>}|null
     */
    public static function propose(ObservingList $list, Location $location, string $night, ?string $from, ?string $until, float $minAltitude = self::MIN_ALTITUDE, int $minutes = self::MINUTES_PER_OBJECT, int $gap = self::MINUTES_BETWEEN): ?array
    {
        $window = self::window($location, $night, $from, $until);
        if ($window === null) {
            return null;
        }

        $names     = $list->items()->orderBy('sort_order')->orderBy('id')->pluck('object_name')->map(fn ($name) => (string) $name)->all();
        $middle    = Carbon::createFromTimestamp(intdiv($window->start + $window->end, 2), 'UTC');
        $positions = ListTargetPositions::at($names, (float) $location->latitude, (float) $location->longitude, $middle);

        $points = [];
        foreach ($names as $index => $name) {
            if (isset($positions[$name])) {
                $points[$index] = $positions[$name];
            }
        }
        $planner    = new NightPlanner((float) $location->latitude, $minAltitude, $minutes + $gap);
        $visibility = $planner->visibility($window, $points);
        $schedule   = $planner->schedule($window, $visibility, $points);

        $objects = [];
        foreach ($schedule['order'] as [$index, $at, [$start, $end]]) {
            $objects[] = [
                'name'     => $names[$index],
                'at'       => $at,
                'from'     => $start,
                'until'    => $end,
                'altitude' => $window->altitude($points[$index]['ra'], $points[$index]['dec'], $at),
            ];
        }

        $missed = [];
        $timed  = array_flip(array_column($schedule['order'], 0));
        foreach ($names as $index => $name) {
            if (! isset($timed[$index])) {
                $missed[] = ['name' => $name, 'reason' => ! isset($points[$index]) ? 'unknown' : ($visibility[$index]['intervals'] === [] ? 'never' : 'time')];
            }
        }

        return [
            'night'    => $night,
            'start'    => $window->start,
            'end'      => $window->end,
            'timezone' => EphemerisDate::timezone($location),
            'objects'  => $objects,
            'missed'   => $missed,
        ];
    }

    /**
     * Save the proposed times: the objects with a time first, in their
     * order, then the others in the order they had, without a time.
     */
    public static function save(ObservingList $list, Location $location, array $proposal): void
    {
        $moment = fn (int $timestamp) => Carbon::createFromTimestamp($timestamp, 'UTC')->format('Y-m-d H:i:s');
        $times  = [];
        foreach ($proposal['objects'] as $object) {
            $times[mb_strtolower($object['name'])] = $object;
        }

        DB::transaction(function () use ($list, $location, $proposal, $moment, $times) {
            $items = $list->items()->orderBy('sort_order')->orderBy('id')->get();
            $order = $items->sortBy(fn ($item) => isset($times[mb_strtolower((string) $item->object_name)])
                ? [0, $times[mb_strtolower((string) $item->object_name)]['at']]
                : [1, (int) $item->sort_order])->values();

            foreach ($order as $position => $item) {
                $time = $times[mb_strtolower((string) $item->object_name)] ?? null;
                DB::table('observing_list_items')->where('id', $item->id)->update([
                    'sort_order'    => $position + 1,
                    'planned_at'    => $time ? $moment($time['at']) : null,
                    'planned_from'  => $time ? $moment($time['from']) : null,
                    'planned_until' => $time ? $moment($time['until']) : null,
                    'updated_at'    => now(),
                ]);
            }

            $list->update([
                'planned_night'       => $proposal['night'],
                'planned_timezone'    => $proposal['timezone'],
                'planned_location_id' => $location->id,
            ]);
        });
    }

    /**
     * Save the list with the proposed times as a new list of the observer,
     * in the same order as save(); the list itself does not change.
     */
    public static function saveAsNew(User $owner, ObservingList $list, Location $location, array $proposal, ?string $name = null): ObservingList
    {
        $times = [];
        foreach ($proposal['objects'] as $object) {
            $times[mb_strtolower($object['name'])] = $object;
        }
        $names = $list->items()->orderBy('sort_order')->orderBy('id')->get(['object_name', 'item_description', 'sort_order'])
            ->sortBy(fn ($item) => isset($times[mb_strtolower((string) $item->object_name)])
                ? [0, $times[mb_strtolower((string) $item->object_name)]['at']]
                : [1, (int) $item->sort_order])
            ->values();

        $details = [];
        foreach ($names as $item) {
            $time                                 = $times[mb_strtolower((string) $item->object_name)] ?? null;
            $details[(string) $item->object_name] = ['note' => $item->item_description] + ($time ? ['at' => $time['at'], 'from' => $time['from'], 'until' => $time['until']] : []);
        }

        $listName = html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $date     = Carbon::parse($proposal['night'])->locale(app()->getLocale())->isoFormat('LL');

        return ObservingList::createWithObjects(
            $owner,
            trim((string) $name) ?: self::newListName($list, $proposal['night']),
            $names->pluck('object_name')->all(),
            __('The objects of :list with their times for the night of :date.', ['list' => $listName, 'date' => $date]),
            false,
            $details,
            ['night' => $proposal['night'], 'timezone' => $proposal['timezone'], 'location_id' => $location->id],
        );
    }

    /**
     * The default name of a new list with the times of the night (Y-m-d).
     */
    public static function newListName(ObservingList $list, string $night): string
    {
        return __(':list, night of :date', [
            'list' => html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'date' => Carbon::parse($night)->locale(app()->getLocale())->isoFormat('LL'),
        ]);
    }

    /**
     * Remove the times of all objects of the list, and its night.
     */
    public static function clear(ObservingList $list): void
    {
        DB::transaction(function () use ($list) {
            $list->items()->update(['planned_at' => null, 'planned_from' => null, 'planned_until' => null]);
            $list->update(['planned_night' => null, 'planned_timezone' => null, 'planned_location_id' => null]);
        });
    }

    /**
     * Give one object of the list a time (H:i) in the night of the list, or
     * none with null; the list gets the night (Y-m-d) and time zone when it
     * has none yet. The window of the object stays as it was.
     */
    public static function setTime(ObservingList $list, ObservingListItem $item, ?string $time, string $night, string $timezone): void
    {
        if ($time === null) {
            $item->update(['planned_at' => null, 'planned_from' => null, 'planned_until' => null]);

            return;
        }

        DB::transaction(function () use ($list, $item, $time, $night, $timezone) {
            if (! $list->planned_night) {
                $list->update(['planned_night' => $night, 'planned_timezone' => $list->planned_timezone ?: $timezone]);
            }
            $at = self::moment($list->planned_night->toDateString(), $time, $list->planned_timezone);
            $item->update(['planned_at' => Carbon::createFromTimestamp($at, 'UTC')]);
        });
    }
}
