<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Location;
use App\Models\NewsItem;
use App\Models\ObservingChallenge;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\News\EventTexts;
use App\Services\News\LocalCircumstances;
use App\Services\StarParties\StarPartyCalendar;
use App\Services\Weather\NightForecast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The personal sky calendar of an observer: an iCalendar feed (RFC 5545) a
 * phone or computer calendar subscribes to with the secret
 * users.calendar_feed_token, in the observer's language. It holds
 *
 * - the automatic news events (#960) seen from the observer's standard
 *   location (LocalCircumstances): an eclipse, occultation, conjunction or
 *   shadow transit from its local begin to end, only when it can be seen
 *   there, the other kinds (opposition, meteor shower, ...) as a day;
 * - the nights NightForecast calls clear and dark at the standard location,
 *   from the begin to the end of the clear and dark hours;
 * - the starparties the observer organises, comes or may come to
 *   (StarPartyCalendar, cancelled ones as cancelled);
 * - the nights of the observer's own observing lists with planned times
 *   (ObservingListTimes), from the first to the last object;
 * - the start and end of the challenges the observer joined, and the start
 *   of the others open to them.
 *
 * From PAST_DAYS ago until DAYS_AHEAD ahead; cached CACHE_MINUTES per
 * observer (calendar apps poll it every few hours).
 */
class SkyCalendar
{
    public const PAST_DAYS = 30;

    public const DAYS_AHEAD = 120;

    public const CACHE_MINUTES = 30;

    /** Minutes: the length of an event at one moment, such as the best moment of a conjunction. */
    private const MOMENT_MINUTES = 30;

    /** The kinds of news items that are no event at a time in the sky. */
    private const SKIPPED_KINDS = [NewsItem::MANUAL, 'challenge', 'supernova', 'nova'];

    /** The kinds that last a day, not a time of it. */
    private const DAY_KINDS = ['opposition', 'greatest_elongation', 'meteor_shower', 'comet'];

    public static function ics(User $user): string
    {
        return Cache::remember(self::cacheKey($user), now()->addMinutes(self::CACHE_MINUTES), function () use ($user) {
            $locale = app()->getLocale();
            app()->setLocale($user->language ?: config('app.locale'));
            try {
                $lines = [
                    'BEGIN:VCALENDAR',
                    'VERSION:2.0',
                    'PRODID:-//DeepskyLog//Sky calendar//EN',
                    'CALSCALE:GREGORIAN',
                    'METHOD:PUBLISH',
                    'X-WR-CALNAME:'.StarPartyCalendar::escape(__('DeepskyLog sky calendar')),
                    'X-PUBLISHED-TTL:PT6H',
                    'REFRESH-INTERVAL;VALUE=DURATION:PT6H',
                    ...self::events($user),
                    'END:VCALENDAR',
                ];
            } finally {
                app()->setLocale($locale);
            }

            return implode("\r\n", array_map([StarPartyCalendar::class, 'fold'], $lines))."\r\n";
        });
    }

    /** Forget the cached calendar, after the observer changed something in it. */
    public static function forget(User $user): void
    {
        Cache::forget(self::cacheKey($user));
    }

    /**
     * The VEVENT lines of all events of the calendar.
     *
     * @return list<string>
     */
    public static function events(User $user): array
    {
        $location = $user->standardLocation;
        if ($location !== null && ! $location->hasValidCoordinates()) {
            $location = null;
        }

        return [
            ...self::newsEvents($location),
            ...self::forecastEvents($user, $location),
            ...self::starPartyEvents($user),
            ...self::listEvents($user),
            ...self::challengeEvents($user),
        ];
    }

    /**
     * The astronomical events of the news, with the local circumstances at
     * the location (geocentric without one).
     *
     * @return list<string>
     */
    public static function newsEvents(?Location $location): array
    {
        $items = NewsItem::query()
            ->where('hidden', false)
            ->whereNotIn('kind', self::SKIPPED_KINDS)
            ->whereBetween('event_at', [now()->subDays(self::PAST_DAYS), now()->addDays(self::DAYS_AHEAD)])
            ->orderBy('event_at')
            ->get();

        $lines = [];
        foreach ($items as $item) {
            $when = self::newsMoments($item, $location);
            if ($when === null) {
                continue;
            }
            $description = trim(EventTexts::summary($item, $location)."\n\n".route('news.show', $item));
            $lines       = [...$lines, ...self::event(
                'news-'.$item->id,
                $when,
                EventTexts::title($item, $location),
                $description,
                route('news.show', $item),
                $item->updated_at,
                $location?->name,
            )];
        }

        return $lines;
    }

    /**
     * When the event of a news item takes place: [begin, end] timestamps, a
     * date (Y-m-d) for a day, or null when it can't be seen at the location.
     *
     * @return array{0: int, 1: int}|string|null
     */
    public static function newsMoments(NewsItem $item, ?Location $location): array|string|null
    {
        $at   = $item->event_at->getTimestamp();
        $data = $item->data ?? [];
        if (in_array($item->kind, self::DAY_KINDS, true)) {
            return $item->event_at->copy()->setTimezone(EventTexts::timezone($location))->toDateString();
        }

        if ($location === null) {
            return match ($item->kind) {
                'lunar_eclipse'                                 => self::span(array_filter($data['contacts'] ?? [])) ?? [$at, $at],
                'double_shadow_transit', 'titan_shadow_transit' => [(int) ($data['start'] ?? $at), (int) ($data['end'] ?? $at)],
                default                                         => [$at, $at + self::MOMENT_MINUTES * 60],
            };
        }

        $local = LocalCircumstances::for($item, $location);
        if ($local === null) {
            return null;
        }

        switch ($item->kind) {
            case 'solar_eclipse':
                $eclipse = $local['eclipse'] ?? null;

                return $eclipse !== null && $eclipse['visible'] ? [(int) $eclipse['begin'], (int) $eclipse['end']] : null;
            case 'lunar_eclipse':
                // Seen when the Moon is up at one of the contacts at least
                $altitudes = array_filter($local['altitudes'] ?? [], fn ($altitude) => $altitude !== null);
                if ($altitudes === [] || max($altitudes) <= 0) {
                    return null;
                }

                return self::span(array_filter($data['contacts'] ?? []));
            case 'lunar_occultation':
                $occultation = $local['occultation'] ?? null;
                if ($occultation !== null) {
                    if (! $occultation['visible']) {
                        return null;
                    }

                    return self::span(array_filter([$occultation['disappears']['at'] ?? null, $occultation['reappears']['at'] ?? null]));
                }

                return self::bestMoment($local);
            case 'double_shadow_transit':
            case 'titan_shadow_transit':
                $visible = $local['visible'] ?? null;

                return $visible === null ? null : [(int) $visible['from'], (int) $visible['until']];
            default:
                // A conjunction (or the Moon passing a planet it covers there)
                $occultation = $local['occultation'] ?? null;
                if ($occultation !== null && ($occultation['visible'] ?? true)) {
                    $span = self::span(array_filter([$occultation['begin'] ?? null, $occultation['end'] ?? null]));
                    if ($span !== null) {
                        return $span;
                    }
                }

                return self::bestMoment($local);
        }
    }

    /**
     * The nights forecast clear and dark at the location, from the begin to
     * the end of their longest clear and dark stretch.
     *
     * @return list<string>
     */
    public static function forecastEvents(User $user, ?Location $location): array
    {
        if ($location === null || ! NightForecast::enabled()) {
            return [];
        }

        try {
            // Not cached yet: fetched after the response, in the calendar from the next poll
            $nights = NightForecast::forLocation($location, fetch: false);
        } catch (\Throwable $e) {
            report($e);

            return [];
        }

        $timezone = EventTexts::timezone($location);
        $lines    = [];
        foreach ($nights ?? [] as $night) {
            if ($night['verdict'] !== 'clear_dark' || ($night['clear_dark'] ?? null) === null) {
                continue;
            }
            $url   = route('observing-list.night', ['night' => $night['night']]);
            $lines = [...$lines, ...self::event(
                'forecast-'.$user->id.'-'.$night['night'],
                [
                    self::nightTime($night['night'], $night['clear_dark']['from'], $timezone),
                    self::nightTime($night['night'], $night['clear_dark']['until'], $timezone),
                ],
                __('Clear and dark at :location', ['location' => $location->name]),
                NightForecast::summary($night)."\n\n".$url,
                $url,
                null,
                $location->name,
            )];
        }

        return $lines;
    }

    /**
     * The starparties the observer organises, comes or may come to.
     *
     * @return list<string>
     */
    public static function starPartyEvents(User $user): array
    {
        $parties = StarParty::query()
            ->where('end_date', '>=', today()->subDays(self::PAST_DAYS)->toDateString())
            ->where(fn ($query) => $query->where('user_id', $user->id)->orWhereIn(
                'id',
                StarPartyParticipant::select('star_party_id')->where('user_id', $user->id)
                    ->whereIn('status', [StarPartyParticipant::GOING, StarPartyParticipant::MAYBE])
            ))
            ->orderBy('start_date')
            ->get();

        return $parties->flatMap(fn (StarParty $party) => StarPartyCalendar::event($party))->values()->all();
    }

    /**
     * The nights of the observer's own observing lists with planned times.
     *
     * @return list<string>
     */
    public static function listEvents(User $user): array
    {
        $lists = ObservingList::query()
            ->where('owner_user_id', $user->id)
            ->whereNotNull('planned_night')
            ->where('planned_night', '>=', today()->subDays(self::PAST_DAYS)->toDateString())
            ->where('planned_night', '<=', today()->addDays(self::DAYS_AHEAD)->toDateString())
            ->withMin('items as first_planned', 'planned_from')
            ->withMin('items as first_planned_at', 'planned_at')
            ->withMax('items as last_planned', 'planned_until')
            ->withMax('items as last_planned_at', 'planned_at')
            ->get();

        $locations = Location::whereIn('id', $lists->pluck('planned_location_id')->filter()->unique())->get()->keyBy('id');

        $lines = [];
        foreach ($lists as $list) {
            $timestamps = fn (array $moments) => array_map(fn ($moment) => Carbon::parse($moment, 'UTC')->getTimestamp(), array_filter($moments));
            $from       = $timestamps([$list->first_planned, $list->first_planned_at]);
            $until      = $timestamps([$list->last_planned, $list->last_planned_at]);
            if ($from === [] || $until === []) {
                continue;
            }
            $begin = min($from);
            $end   = max($begin + self::MOMENT_MINUTES * 60, max($until));
            $url   = route('observing-list.show', $list);
            $lines = [...$lines, ...self::event(
                'list-'.$list->id,
                [$begin, $end],
                __('Observing list: :name', ['name' => $list->name]),
                $url."\n".route('observing-list.eyepiece', $list),
                $url,
                $list->updated_at,
                $locations->get($list->planned_location_id)?->name,
            )];
        }

        return $lines;
    }

    /**
     * The start and end of the challenges the observer joined, and the start
     * of the others open to them that have not ended.
     *
     * @return list<string>
     */
    public static function challengeEvents(User $user): array
    {
        $joined     = ObservingGoal::where('user_id', $user->id)->whereNotNull('challenge_id')->pluck('challenge_id')->all();
        $challenges = ObservingChallenge::visible()
            ->where('ends_on', '>=', today()->subDays(self::PAST_DAYS)->toDateString())
            ->where('starts_on', '<=', today()->addDays(self::DAYS_AHEAD)->toDateString())
            ->where(fn ($query) => $query->whereIn('id', $joined)
                ->orWhere(fn ($open) => $open->where('ends_on', '>=', today()->toDateString())
                    ->where(fn ($q) => $q->whereNull('club_id')->orWhereIn('club_id', Club::ofMember($user)->select('id')))))
            ->orderBy('starts_on')
            ->get();

        $lines = [];
        foreach ($challenges as $challenge) {
            $url   = route('challenges.show', $challenge);
            $text  = $challenge->task()."\n\n".$url;
            $lines = [...$lines, ...self::event(
                'challenge-'.$challenge->id.'-start',
                $challenge->starts_on->toDateString(),
                __('Observing challenge starts: :name', ['name' => $challenge->name]),
                $text,
                $url,
                $challenge->updated_at,
            )];
            if (in_array($challenge->id, $joined)) {
                $lines = [...$lines, ...self::event(
                    'challenge-'.$challenge->id.'-end',
                    $challenge->ends_on->toDateString(),
                    __('Observing challenge ends: :name', ['name' => $challenge->name]),
                    $text,
                    $url,
                    $challenge->updated_at,
                )];
            }
        }

        return $lines;
    }

    /**
     * The lines of one VEVENT: at a time ([begin, end] timestamps) or on a
     * day (Y-m-d).
     *
     * @param  array{0: int, 1: int}|string  $when
     * @return list<string>
     */
    private static function event(string $uid, array|string $when, string $summary, string $description, string $url, ?Carbon $modified, ?string $location = null): array
    {
        $utc   = fn (int $timestamp) => gmdate('Ymd\THis\Z', $timestamp);
        $lines = [
            'BEGIN:VEVENT',
            'UID:'.$uid.'@'.StarPartyCalendar::host(),
            'DTSTAMP:'.$utc(time()),
        ];
        if ($modified !== null) {
            $lines[] = 'LAST-MODIFIED:'.$utc($modified->getTimestamp());
        }
        if (is_string($when)) {
            $lines[] = 'DTSTART;VALUE=DATE:'.str_replace('-', '', $when);
            $lines[] = 'DTEND;VALUE=DATE:'.Carbon::parse($when)->addDay()->format('Ymd');
        } else {
            $lines[] = 'DTSTART:'.$utc($when[0]);
            $lines[] = 'DTEND:'.$utc(max($when[1], $when[0] + 60));
        }
        $lines[] = 'SUMMARY:'.StarPartyCalendar::escape($summary);
        if ($location !== null && $location !== '') {
            $lines[] = 'LOCATION:'.StarPartyCalendar::escape($location);
        }
        $lines[] = 'DESCRIPTION:'.StarPartyCalendar::escape($description);
        $lines[] = 'URL:'.StarPartyCalendar::escape($url);
        $lines[] = 'TRANSP:TRANSPARENT';
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /**
     * The first and last of the moments, null without any.
     *
     * @param  array<int|string, int|null>  $moments
     * @return array{0: int, 1: int}|null
     */
    private static function span(array $moments): ?array
    {
        $moments = array_map('intval', array_values($moments));

        return $moments === [] ? null : [min($moments), max($moments)];
    }

    /**
     * The best moment of a conjunction at the location, as an event of
     * MOMENT_MINUTES, or null when it can't be seen there.
     *
     * @param  array<string, mixed>  $local
     * @return array{0: int, 1: int}|null
     */
    private static function bestMoment(array $local): ?array
    {
        $best = $local['best'] ?? null;

        return $best === null ? null : [(int) $best['at'], (int) $best['at'] + self::MOMENT_MINUTES * 60];
    }

    /** A local time (H:i) of a night as a timestamp: before noon it is the morning after its evening. */
    private static function nightTime(string $night, string $time, string $timezone): int
    {
        $moment = Carbon::parse($night.' '.$time, $timezone);
        if ($time < '12:00') {
            $moment->addDay();
        }

        return $moment->getTimestamp();
    }

    private static function cacheKey(User $user): string
    {
        return 'sky_calendar:v1:'.$user->id;
    }
}
