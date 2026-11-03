<?php

namespace App\Services;

use App\Jobs\ComputeNextForYou;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Next for you": the deep-sky objects an observer has not seen that the
 * observers who saw the same objects saw too (AlsoSeenObjects), that their
 * standard telescope shows at their standard location. A third route next
 * to Tonight's best and the beginner path: it follows the observer's taste,
 * not popularity alone.
 *
 * An object's score is the sum of its scores with every object the observer
 * has seen; the BECAUSE seen objects that add most are shown as the reason.
 * The ranking reads the neighbours of every seen object (340,000 rows for an
 * observer of 17,000 objects), so it is queued (ComputeNextForYou) and cached
 * under a key that changes with the observer's observations and every
 * rebuild of the neighbours. The telescope filter is applied when shown, from
 * the stored contrast reserves (user_object_metrics; missing ones are queued
 * with ObjectMetricsQueue), and for double stars from whether the telescope
 * splits them (DoubleStarResolvability), so new metrics or equipment show
 * at once; with DetectionThreshold::offsetFor() added to the contrast
 * reserves when the observer chose to use their own threshold.
 */
class NextForYou
{
    /** The best candidates kept in the cache, before the telescope filter. */
    public const MAX_CANDIDATES = 1000;

    /** The objects shown and put on a list. */
    public const MAX_OBJECTS = 100;

    /** The objects an observer needs to have seen. */
    public const MIN_SEEN = 3;

    /** The seen objects named as the reason of a suggestion. */
    public const BECAUSE = 3;

    /** Not suggested: they are not for the contrast reserve, or change (supernovae). */
    public const EXCLUDED_TYPES = ['AA1STAR', 'QUASR', 'SNOVA', 'NONEX'];

    /** The verdicts of DoubleStarResolvability that count as shown. */
    public const DOUBLE_STAR_VERDICTS = ['easy', 'split', 'difficult'];

    public const CACHE_DAYS = 7;

    /** As EquipmentGain::MAX_ATTEMPTS. */
    public const MAX_ATTEMPTS = 3;

    /** Part of the cache key: raise it when the cached result changes shape or calculation. */
    private const RESULT_VERSION = 1;

    private ?string $key = null;

    private function __construct(
        public readonly User $user,
        public readonly ?Instrument $instrument,
        public readonly ?Location $location,
        public readonly ?int $lensId
    ) {
    }

    /**
     * For the observer's standard telescope (with its lens) and location,
     * the equipment of their stored contrast reserves.
     */
    public static function for(User $user): self
    {
        $instrument = $user->stdtelescope ? Instrument::where('user_id', $user->id)->find($user->stdtelescope) : null;
        $location   = $user->stdlocation ? Location::find($user->stdlocation) : null;

        return new self($user, $instrument, $location, $instrument ? $user->standardLensId() : null);
    }

    /**
     * Why nothing can be suggested, as a translation key; null when it can.
     */
    public function unavailable(): ?string
    {
        if (! $this->instrument) {
            return 'Set your standard instrument to get suggestions it shows.';
        }
        if (! $this->location) {
            return 'Set your standard location to get suggestions you can see there.';
        }
        if (! DB::table(AlsoSeenObjects::TABLE)->exists()) {
            return 'The suggestions have not been calculated yet. Please come back later.';
        }
        if (AlsoSeenObjects::seenBy($this->user)->distinct()->count('observations.different_object_key') < self::MIN_SEEN) {
            return 'Log a few observations first: the suggestions come from the objects you have seen.';
        }

        return null;
    }

    /**
     * The cached ranking, or null when it is not calculated yet.
     *
     * @return array{objects: list<array{name: string, score: float, because: list<string>}>, seen: int}|null
     */
    public function result(): ?array
    {
        return Cache::get($this->key());
    }

    /**
     * Queues the calculation of a result not cached yet; false when it is
     * already waiting or has failed().
     */
    public function queue(): bool
    {
        if ($this->failed() || ! Cache::add($this->key().':pending', true, now()->addHour())) {
            return false;
        }
        Cache::add($this->key().':attempts', 0, now()->addHour());
        Cache::increment($this->key().':attempts');
        ComputeNextForYou::dispatch($this->user->id);

        return true;
    }

    public function pending(): bool
    {
        return Cache::has($this->key().':pending');
    }

    public function forgetPending(): void
    {
        Cache::forget($this->key().':pending');
    }

    /**
     * Whether MAX_ATTEMPTS calculations ended without a result (for an hour,
     * or until retry()).
     */
    public function failed(): bool
    {
        return ! Cache::has($this->key())
            && ! $this->pending()
            && (int) Cache::get($this->key().':attempts', 0) >= self::MAX_ATTEMPTS;
    }

    public function retry(): void
    {
        Cache::forget($this->key().':attempts');
    }

    /**
     * Calculates and caches the ranking.
     */
    public function compute(): array
    {
        $result = $this->calculate();
        Cache::put($this->key(), $result, now()->addDays(self::CACHE_DAYS));
        Cache::forget($this->key().':attempts');

        return $result;
    }

    /**
     * The candidates of the result the telescope shows, the best first, at
     * most $limit, with their slug, type, constellation, magnitude and
     * visibility; queues the contrast reserves still missing.
     *
     * @return array{total: int, pending: bool, objects: list<array<string, mixed>>}
     */
    public function objects(array $result, int $limit = self::MAX_OBJECTS): array
    {
        $candidates = $result['objects'];
        $names      = array_column($candidates, 'name');
        $because    = array_merge(...array_column($candidates, 'because') ?: [[]]);
        $rows       = collect(array_chunk(array_values(array_unique([...$names, ...$because])), 1000))
            ->flatMap(fn (array $chunk) => DB::table('objects as o')
                ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
                ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
                ->whereIn('o.name', $chunk)
                ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 'o.subr', 'o.pa', 'o.diam1', 'o.diam2', 'o.decl', 't.name as type_name', 'c.name as constellation']))
            ->keyBy(fn ($row) => mb_strtolower((string) $row->name));
        $metrics = collect(array_chunk($names, 1000))
            ->flatMap(fn (array $chunk) => DB::table('user_object_metrics')
                ->where('user_id', $this->user->id)
                ->where('instrument_id', $this->instrument->id)
                ->where('location_id', $this->location->id)
                ->when($this->lensId === null, fn ($query) => $query->whereNull('lens_id'), fn ($query) => $query->where('lens_id', $this->lensId))
                ->whereIn('object_name', $chunk)
                ->get(['object_name', 'contrast_reserve', 'optimum_detection_magnification']))
            ->keyBy(fn ($row) => mb_strtolower((string) $row->object_name));

        $magnifications = null;
        $offset         = null;
        $missing        = [];
        $shown          = [];
        foreach ($candidates as $candidate) {
            $row = $rows[mb_strtolower($candidate['name'])] ?? null;
            if (! $row || in_array($row->type, self::EXCLUDED_TYPES, true)) {
                continue;
            }

            if (! ContrastReserveCalculator::appliesTo($row->type)) {
                $magnifications ??= ContrastReserveCalculator::magnifications($this->instrument, $this->user, ContrastReserveCalculator::lensFactor($this->lensId));
                $split = DoubleStarResolvability::forObject($row, $this->instrument, $this->location, $this->user, 1.0, $magnifications);
                if (! in_array($split['verdict'] ?? null, self::DOUBLE_STAR_VERDICTS, true) || ($split['not_visible'] ?? false)) {
                    continue;
                }
                $visibility = ['verdict' => $split['verdict'], 'magnification' => $split['magnification']];
            } else {
                $metric = $metrics[mb_strtolower($candidate['name'])] ?? null;
                if (! $metric) {
                    $missing[] = (string) $row->name;

                    continue;
                }
                if (! is_numeric($metric->contrast_reserve)) {
                    continue;
                }
                // with the observer's own detection threshold, when they chose to use it
                $reserve = (float) $metric->contrast_reserve + ($offset ??= DetectionThreshold::offsetFor($this->user));
                if ($reserve <= EquipmentGain::VISIBLE_ABOVE) {
                    continue;
                }
                $visibility = [
                    'contrast_reserve' => round($reserve, 2),
                    'category'         => ContrastReserveCalculator::category($reserve),
                    'magnification'    => $metric->optimum_detection_magnification ? (int) $metric->optimum_detection_magnification : null,
                ];
            }

            $shown[] = [
                'name'          => (string) $row->name,
                'slug'          => (string) $row->slug,
                'type'          => $row->type_name ?: (string) $row->type,
                'constellation' => $row->constellation,
                'magnitude'     => is_numeric($row->mag) && (float) $row->mag < 99 ? round((float) $row->mag, 1) : null,
                'score'         => $candidate['score'],
                'because'       => array_values(array_filter(array_map(function (string $name) use ($rows) {
                    $seen = $rows[mb_strtolower($name)] ?? null;

                    return $seen ? ['name' => (string) $seen->name, 'slug' => (string) $seen->slug] : null;
                }, $candidate['because']))),
                ...$visibility,
            ];
        }

        $token = $missing ? ObjectMetricsQueue::queueMissing($this->user->id, $this->instrument->id, $this->location->id, $this->lensId, $missing) : null;

        return ['total' => count($shown), 'pending' => ObjectMetricsQueue::pending($token), 'objects' => array_slice($shown, 0, $limit)];
    }

    /**
     * An observing list of the objects shown.
     */
    public function createList(array $result, ?string $name = null, bool $public = false): ?ObservingList
    {
        $names = array_column($this->objects($result)['objects'], 'name');
        if ($names === []) {
            return null;
        }
        $description = __('Objects seen by the observers who saw the same objects as you, that your :instrument shows at :location.', ['instrument' => $this->instrumentName(), 'location' => $this->locationName()]);

        return ObservingList::createWithObjects($this->user, trim((string) $name) ?: $this->defaultListName(), $names, $description, $public);
    }

    public function defaultListName(): string
    {
        return __('Next for you');
    }

    public function instrumentName(): string
    {
        return $this->instrument ? html_entity_decode((string) $this->instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    }

    public function locationName(): string
    {
        return $this->location ? html_entity_decode((string) $this->location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
    }

    /**
     * The objects the observer has not seen, by the sum of their scores with
     * the objects they have seen, the best MAX_CANDIDATES.
     */
    private function calculate(): array
    {
        $seen = AlsoSeenObjects::seenBy($this->user)
            ->distinct()
            ->pluck('observations.different_object_key')
            ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => (string) $name])
            ->all();

        $scores  = [];
        $reasons = [];
        foreach (array_chunk(array_values($seen), 1000) as $chunk) {
            $rows = DB::table(AlsoSeenObjects::TABLE)
                ->whereIn('object_name', $chunk)
                ->get(['object_name', 'similar_name', 'score']);
            foreach ($rows as $row) {
                $name = (string) $row->similar_name;
                $key  = mb_strtolower($name);
                if (isset($seen[$key])) {
                    continue;
                }
                $scores[$name]                              = ($scores[$name] ?? 0.0) + (float) $row->score;
                $reasons[$name][(string) $row->object_name] = (float) $row->score;
            }
        }

        arsort($scores);
        $objects = [];
        foreach (array_slice($scores, 0, self::MAX_CANDIDATES, true) as $name => $score) {
            arsort($reasons[$name]);
            $objects[] = [
                'name'    => (string) $name,
                'score'   => round($score, 3),
                'because' => array_map('strval', array_keys(array_slice($reasons[$name], 0, self::BECAUSE, true))),
            ];
        }

        return ['objects' => $objects, 'seen' => count($seen)];
    }

    /**
     * Changes with the observer's observations and every rebuild of the
     * neighbours.
     */
    private function key(): string
    {
        return $this->key ??= 'next_for_you:v'.self::RESULT_VERSION.':'.$this->user->id.':'.md5(json_encode([
            (array) AlsoSeenObjects::seenBy($this->user)->selectRaw('COUNT(*) as count, MAX(observations.updated_at) as updated')->first(),
            AlsoSeenObjects::version(),
        ]));
    }
}
