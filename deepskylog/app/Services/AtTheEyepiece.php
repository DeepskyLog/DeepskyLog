<?php

namespace App\Services;

use App\Models\Constellation;
use App\Models\DeepskyObject;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportResult;
use App\Services\Observations\ObservationCardData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The page of an observing list at the telescope
 * (/observing-list/{slug}/eyepiece): the object to observe now, the next one
 * and the button that logs an observation of it.
 *
 * Of a list with times, the objects with a time are taken in the order of
 * their times. A list planned for another night is moved to tonight by whole
 * days (the objects then stand a few minutes per day earlier than the times
 * say, which the page tells). The current object is the last one whose time
 * has come, the first one before the night starts. A list without times is
 * taken in its own order, starting with the first object not observed
 * tonight; the page suggests planning the times.
 *
 * "Log it" opens the observation form with the date and time of now at the
 * location, the location and equipment of the observer's last observation of
 * the night (else the session of the night, the list's location and the
 * standard equipment). An observation logged on the page itself (offline,
 * kept by the page until the connection returns) is saved with the same
 * location and equipment through CsvObservationImporter::importOne().
 */
class AtTheEyepiece
{
    /** An observation at most this long before the moment gives the location and equipment. */
    public const RECENT_HOURS = 12;

    /** The longest description of an observation logged on the page. */
    public const MAX_DESCRIPTION = 5000;

    /** Types whose visibility scale differs (observations.visibility_oc / visibility_ds). */
    public const OPEN_CLUSTER_TYPES = ['OPNCL', 'LMCOC', 'SMCOC'];

    /**
     * The objects of the page: of a list with times those with a time, in
     * time order; of a list without times all, in the order of the list.
     *
     * @return Collection<int, ObservingListItem>
     */
    public static function sequence(ObservingList $list, bool $timed): Collection
    {
        return $timed
            ? $list->items()->whereNotNull('planned_at')->orderBy('planned_at')->orderBy('sort_order')->orderBy('id')->get()
            : $list->items()->orderBy('sort_order')->orderBy('id')->get();
    }

    /** The time zone of the times of the list. */
    public static function timezone(ObservingList $list, ?Location $location = null): string
    {
        $timezone = $list->planned_timezone ?: $location?->timezone;

        return $timezone && in_array($timezone, \DateTimeZone::listIdentifiers(), true) ? $timezone : config('app.timezone', 'UTC');
    }

    /** The night (Y-m-d of its evening) of a moment: before noon belongs to the night before. */
    public static function nightOf(Carbon $moment, string $timezone): string
    {
        return $moment->copy()->setTimezone($timezone)->subHours(12)->toDateString();
    }

    /**
     * Everything the page shows.
     *
     * @return array<string, mixed>
     */
    public static function page(ObservingList $list, User $user, ?int $itemId = null, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $location = $list->planned_location_id ? Location::find($list->planned_location_id) : null;
        $location ??= $user->stdlocation ? Location::find($user->stdlocation) : null;
        $timezone = self::timezone($list, $location);
        $timed = $list->hasPlannedTimes();
        $items = self::sequence($list, $timed);

        $tonight = self::nightOf($now, $timezone);
        $plannedNight = $timed ? ($list->planned_night?->toDateString() ?? self::nightOf($items->first()->planned_at, $timezone)) : $tonight;
        $shiftDays = (int) Carbon::parse($plannedNight)->diffInDays(Carbon::parse($tonight), false);

        $done = self::observedTonight($user, $tonight, $timezone);
        $objects = self::objects($items);
        $entries = $items->values()->map(fn (ObservingListItem $item) => self::entry($item, $timezone, $shiftDays, $done, $objects));

        $index = null;
        if ($itemId !== null) {
            $index = $entries->search(fn (array $entry) => $entry['item']->id === $itemId);
            $index = $index === false ? null : $index;
        }
        // Only a list with times follows the clock
        $following = $timed && $index === null;
        if ($index === null && $entries->isNotEmpty()) {
            $index = $timed ? self::currentIndex($entries, $now) : self::firstNotDone($entries);
        }

        $current = $index !== null ? $entries[$index] : null;

        return [
            'list' => $list,
            'location' => $location,
            'timezone' => $timezone,
            'now' => $now->copy()->setTimezone($timezone),
            'timed' => $timed,
            'entries' => $entries,
            'index' => $index,
            'current' => $current,
            'previous' => $index !== null && $index > 0 ? $entries[$index - 1] : null,
            'next' => $index !== null ? ($entries[$index + 1] ?? null) : null,
            'following' => $following,
            'planned_night' => $plannedNight,
            'shift_days' => $shiftDays,
            'untimed' => $timed ? $list->items()->whereNull('planned_at')->count() : 0,
            'session' => self::session($user, $now, $timezone),
        ];
    }

    /**
     * The index of the object to observe at the moment: the last one whose
     * time has come, else the first.
     *
     * @param  Collection<int, array<string, mixed>>  $entries
     */
    public static function currentIndex(Collection $entries, Carbon $now): int
    {
        $index = 0;
        foreach ($entries as $i => $entry) {
            if ($entry['at']->lte($now)) {
                $index = $i;
            }
        }

        return $index;
    }

    /**
     * The index of the first object not observed tonight, else the first
     * (a list without times).
     *
     * @param  Collection<int, array<string, mixed>>  $entries
     */
    public static function firstNotDone(Collection $entries): int
    {
        $index = $entries->search(fn (array $entry) => ! $entry['done']);

        return $index === false ? 0 : $index;
    }

    /**
     * The deep-sky objects of the items, by lower-case name, in a few
     * queries (a list can hold thousands of objects).
     *
     * @param  Collection<int, ObservingListItem>  $items
     * @return Collection<string, DeepskyObject>
     */
    private static function objects(Collection $items): Collection
    {
        $objects = collect();
        foreach ($items->pluck('object_name')->filter()->unique()->chunk(1000) as $names) {
            $objects = $objects->merge(DeepskyObject::whereIn('name', $names->all())->get()
                ->keyBy(fn (DeepskyObject $object) => mb_strtolower((string) $object->name)));
        }

        return $objects;
    }

    /**
     * One object of the sequence, with its time (if any) moved to tonight.
     *
     * @param  array<string, true>  $done
     * @param  Collection<string, DeepskyObject>  $objects
     * @return array<string, mixed>
     */
    private static function entry(ObservingListItem $item, string $timezone, int $shiftDays, array $done, Collection $objects): array
    {
        $shift = fn (?Carbon $moment) => $moment?->copy()->addDays($shiftDays)->setTimezone($timezone);
        $name = (string) $item->object_name;
        $object = $objects->get(mb_strtolower($name));

        return [
            'item' => $item,
            'name' => $name,
            'object' => $object,
            'slug' => $object?->slug ?: Str::slug($name),
            'at' => $shift($item->planned_at),
            'from' => $shift($item->planned_from),
            'until' => $shift($item->planned_until),
            'note' => $item->item_description,
            'done' => isset($done[mb_strtolower($name)]),
        ];
    }

    /**
     * The type, constellation, magnitude and size of a deep-sky object.
     *
     * @return array{type: ?string, constellation: ?string, magnitude: ?float, size: ?string}
     */
    public static function details(?DeepskyObject $object): array
    {
        if ($object === null) {
            return ['type' => null, 'constellation' => null, 'magnitude' => null, 'size' => null];
        }

        $typeName = ObservationCardData::instance()->targetTypeName($object->type);
        $constellation = $object->con ? Constellation::where('id', $object->con)->value('name') : null;
        $magnitude = is_numeric($object->mag) && (float) $object->mag < 99 && (float) $object->mag != 0.0 ? round((float) $object->mag, 1) : null;

        $size = null;
        $diam1 = (float) $object->diam1;
        $diam2 = (float) $object->diam2;
        if ($object->type === 'DS' && $diam1 > 0) {
            // A double star stores its separation in diam1
            $size = __('Separation').' '.round($diam1, 1).'″';
        } elseif ($diam1 > 0) {
            $format = fn (float $arcsec) => $arcsec >= 60 ? round($arcsec / 60, 1).'′' : round($arcsec).'″';
            $size = $format($diam1).($diam2 > 0 && abs($diam2 - $diam1) > 0.01 ? ' × '.$format($diam2) : '');
        }

        return [
            'type' => $typeName !== null ? __($typeName) : null,
            'constellation' => $constellation ? __($constellation) : null,
            'magnitude' => $magnitude,
            'size' => $size,
        ];
    }

    /**
     * The names (lower case) of the objects the observer observed in the night.
     *
     * @return array<string, true>
     */
    public static function observedTonight(User $user, string $night, string $timezone): array
    {
        $start = Carbon::parse($night.' 12:00', $timezone)->utc();
        $end = $start->copy()->addDay();

        $names = [];
        $rows = Observation::where('observer_user_id', $user->id)
            ->whereBetween('observed_on', [$start->toDateString(), $end->toDateString()])
            ->get(['observed_on', 'observed_time', 'target_name']);
        foreach ($rows as $row) {
            $moment = Carbon::parse(substr((string) $row->observed_on, 0, 10).' '.($row->observed_time ?: '00:00:00'), 'UTC');
            if ($moment->betweenIncluded($start, $end) && $row->target_name) {
                $names[mb_strtolower((string) $row->target_name)] = true;
            }
        }

        return $names;
    }

    /** The observer's own active session holding the moment, else one that starts in its night. */
    public static function session(User $user, Carbon $moment, string $timezone): ?ObservationSession
    {
        $local = $moment->copy()->setTimezone($timezone)->format('Y-m-d H:i:s');
        $sessions = ObservationSession::where('observerid', $user->username)->where('active', 1);

        return (clone $sessions)->where('begindate', '<=', $local)->where('enddate', '>=', $local)->orderByDesc('begindate')->first()
            ?? (clone $sessions)->whereDate('begindate', self::nightOf($moment, $timezone))->orderByDesc('begindate')->first();
    }

    /**
     * The location and equipment of an observation at the moment: those of
     * the observer's last observation in the hours before, else the location
     * of the session of the night, the list or the observer and the standard
     * equipment. Only the observer's own active location and equipment.
     *
     * @return array{location_id: ?int, instrument_id: ?int, eyepiece_id: ?int, lens_id: ?int, filter_id: ?int}
     */
    public static function equipment(User $user, ?ObservingList $list, Carbon $moment): array
    {
        $own = fn (string $model, mixed $id) => $id && $model::where('user_id', $user->id)->where('active', 1)->whereKey($id)->exists() ? (int) $id : null;

        $recent = self::recentObservation($user, $moment);
        $timezone = $list ? self::timezone($list) : config('app.timezone', 'UTC');
        $session = self::session($user, $moment, $timezone);

        $location = $own(Location::class, $recent?->location_id)
            ?? $own(Location::class, $session?->locationid)
            ?? $own(Location::class, $list?->planned_location_id)
            ?? $own(Location::class, $user->stdlocation);

        if ($recent !== null && $own(Instrument::class, $recent->instrument_id)) {
            return [
                'location_id' => $location,
                'instrument_id' => (int) $recent->instrument_id,
                'eyepiece_id' => $own(Eyepiece::class, $recent->eyepiece_id),
                'lens_id' => $own(Lens::class, $recent->lens_id),
                'filter_id' => $own(Filter::class, $recent->filter_id),
            ];
        }

        return [
            'location_id' => $location,
            'instrument_id' => $own(Instrument::class, $user->stdtelescope),
            'eyepiece_id' => $own(Eyepiece::class, $user->stdeyepiece),
            'lens_id' => $own(Lens::class, $user->standardLensId()),
            'filter_id' => null,
        ];
    }

    /** The observer's last observation in the RECENT_HOURS before the moment (and up to an hour after). */
    private static function recentObservation(User $user, Carbon $moment): ?Observation
    {
        $start = $moment->copy()->utc()->subHours(self::RECENT_HOURS);
        $end = $moment->copy()->utc()->addHour();

        return Observation::where('observer_user_id', $user->id)
            ->whereBetween('observed_on', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('observed_time')
            ->orderByDesc('observed_on')->orderByDesc('observed_time')->orderByDesc('id')
            ->get(['id', 'observed_on', 'observed_time', 'location_id', 'instrument_id', 'eyepiece_id', 'lens_id', 'filter_id'])
            ->first(function (Observation $observation) use ($start, $end) {
                $at = Carbon::parse(substr((string) $observation->observed_on, 0, 10).' '.$observation->observed_time, 'UTC');

                return $at->betweenIncluded($start, $end);
            });
    }

    /**
     * The query of the observation form for an observation of the item now:
     * the object, the local date and time at the location, the location and
     * the equipment, and the page to return to after saving.
     *
     * @return array<string, mixed>
     */
    public static function logParameters(User $user, ObservingList $list, ObservingListItem $item, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $equipment = self::equipment($user, $list, $now);
        $timezone = ($equipment['location_id'] ? Location::whereKey($equipment['location_id'])->value('timezone') : null) ?: self::timezone($list);
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = config('app.timezone', 'UTC');
        }
        $local = $now->copy()->setTimezone($timezone);
        $object = DeepskyObject::where('name', $item->object_name)->first();

        return array_filter([
            'slug' => $object?->slug ?: Str::slug((string) $item->object_name),
            'observed_on' => $local->toDateString(),
            'observed_time' => $local->format('H:i'),
            'location' => $equipment['location_id'],
            'instrument' => $equipment['instrument_id'],
            'eyepiece' => $equipment['eyepiece_id'],
            'lens' => $equipment['lens_id'],
            'filter' => $equipment['filter_id'],
            'return_to' => route('observing-list.eyepiece', ['list' => $list, 'item' => $item->id], false),
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Save an observation logged on the page (also one kept while offline):
     * the object of the item at the moment (UTC), with the location and
     * equipment of that moment.
     */
    public static function store(User $user, ObservingList $list, ObservingListItem $item, Carbon $moment, string $description, ?int $visibility): ImportResult
    {
        $equipment = self::equipment($user, $list, $moment);
        if ($equipment['instrument_id'] === null) {
            $result = new ImportResult;
            $result->addError(__('Choose a standard instrument on your profile page first.'), '', 1);

            return $result;
        }

        $utc = $moment->copy()->utc();
        $language = $user->observationlanguage ?: ($user->language ?: app()->getLocale());

        return (new CsvObservationImporter($user))
            // Nothing the observer wrote at the telescope is lost: below the
            // horizon it is saved with a warning
            ->importBelowHorizon()
            ->importOne([
                'object' => (string) $item->object_name,
                'observed_on' => $utc->toDateString(),
                'observed_time' => $utc->format('H:i:00'),
                'location_id' => $equipment['location_id'],
                'instrument_id' => $equipment['instrument_id'],
                'eyepiece_id' => $equipment['eyepiece_id'],
                'lens_id' => $equipment['lens_id'],
                'filter_id' => $equipment['filter_id'],
                'visibility' => $visibility,
                'language' => strtolower(substr((string) $language, 0, 10)),
                'description' => $description,
            ]);
    }

    /** The visibility scale of the object's type: the key of its labels in observations.php. */
    public static function visibilityScale(?DeepskyObject $object): ?string
    {
        if ($object === null) {
            return null;
        }

        return match (true) {
            $object->type === 'DS' => 'visibility_ds',
            in_array($object->type, self::OPEN_CLUSTER_TYPES, true) => 'visibility_oc',
            default => 'visibility',
        };
    }

    /** The item of the list with this id, or null. */
    public static function itemOf(ObservingList $list, int $itemId): ?ObservingListItem
    {
        return $list->items()->whereKey($itemId)->first();
    }
}
