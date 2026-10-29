<?php

namespace App\Services;

use App\Jobs\ComputeRevisitObjects;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Revisit with your telescope": the deep-sky objects an observer has seen,
 * but only with a much smaller instrument (APERTURE_RATIO) or never drew,
 * that their telescope (by default the standard one) shows much better at
 * one of their locations: a contrast reserve at least MIN_GAIN above the best
 * of the instruments they saw it with, ranked by that gain. Where "What's
 * new for me" (EquipmentGain) is about objects never seen, this is about
 * objects worth a second look.
 *
 * Both sides are calculated with ContrastReserveCalculator at the same
 * location and with the observer's current eyepieces, so only the telescope
 * differs. A run calculates every seen object with every instrument it was
 * seen with, which takes a few seconds for an observer with thousands of
 * objects, so it is queued (ComputeRevisitObjects) and its result cached
 * under a key that changes with the equipment, the location and the
 * observer's observations.
 */
class RevisitObjects
{
    /** The telescope is "much larger": at least this times the aperture (more than twice the light). */
    public const APERTURE_RATIO = 1.5;

    /**
     * The least gain in contrast reserve worth a second look, about a step in
     * visibility: 80 mm to 400 mm gains some 0.8, 300 mm to 400 mm 0.1.
     */
    public const MIN_GAIN = 0.1;

    /** The objects shown and put on a list, of the largest gain. */
    public const MAX_OBJECTS = 200;

    public const CACHE_DAYS = 7;

    /** As EquipmentGain::MAX_ATTEMPTS. */
    public const MAX_ATTEMPTS = 3;

    /** Why an object is listed. */
    public const SMALLER = 'smaller';

    public const UNDRAWN = 'undrawn';

    /** Part of the cache key: raise it when the cached result changes shape or calculation. */
    private const RESULT_VERSION = 1;

    private ?string $key = null;

    private function __construct(
        public readonly User $user,
        public readonly ?Instrument $instrument,
        public readonly ?Location $location
    ) {
    }

    /**
     * For one of the observer's active instruments (else their standard
     * telescope) at one of their locations (EquipmentGain::location()).
     */
    public static function for(User $user, ?int $instrumentId = null, ?int $locationId = null): self
    {
        $instruments = self::instruments($user);
        $instrument  = $instruments->firstWhere('id', $instrumentId)
            ?? $instruments->firstWhere('id', (int) $user->stdtelescope);

        return new self($user, $instrument, EquipmentGain::location($user, $locationId));
    }

    /**
     * The observer's active instruments with an aperture, the largest first.
     *
     * @return Collection<int, Instrument>
     */
    public static function instruments(User $user)
    {
        return Instrument::where('user_id', $user->id)
            ->where('active', true)
            ->where('aperture_mm', '>', 0)
            ->orderByDesc('aperture_mm')
            ->get();
    }

    /**
     * Why nothing can be compared, as a translation key; null when it can.
     */
    public function unavailable(): ?string
    {
        if (! $this->instrument) {
            return 'Set your standard instrument to see which objects it shows better.';
        }
        if (! $this->location) {
            return 'Add a location to see which objects you can see.';
        }
        if (! is_numeric($this->location->getSqm())) {
            return 'Give the location a sky brightness (SQM) or limiting magnitude to see which objects you can see.';
        }

        return null;
    }

    /**
     * The cached result, or null when it is not calculated yet.
     *
     * @return array{objects: list<array{name: string, contrast_reserve: float, category: string, magnification: ?int, before: float, gain: float, aperture: float, drawn: bool, reason: string}>, sqm: float}|null
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
        ComputeRevisitObjects::dispatch($this->user->id, (int) $this->instrument->id, (int) $this->location->id);

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
     * Calculates and caches the result.
     */
    public function compute(): array
    {
        $result = $this->calculate();
        Cache::put($this->key(), $result, now()->addDays(self::CACHE_DAYS));
        Cache::forget($this->key().':attempts');

        return $result;
    }

    /**
     * The objects of the result for $reason (null for both), at most $limit,
     * with their slug, type, constellation and magnitude.
     *
     * @return array{total: int, objects: list<array<string, mixed>>}
     */
    public function objects(array $result, ?string $reason = null, int $limit = self::MAX_OBJECTS): array
    {
        $matching = array_values(array_filter($result['objects'], fn (array $object) => match ($reason) {
            self::SMALLER => (float) $this->instrument->aperture_mm >= $object['aperture'] * self::APERTURE_RATIO,
            self::UNDRAWN => ! $object['drawn'],
            default       => true,
        }));
        $chosen = array_slice($matching, 0, $limit);

        $rows = DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->whereIn('o.name', array_column($chosen, 'name'))
            ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 't.name as type_name', 'c.name as constellation'])
            ->keyBy('name');

        $objects = [];
        foreach ($chosen as $object) {
            $row = $rows[$object['name']] ?? null;
            if (! $row) {
                continue;
            }
            $objects[] = [
                ...$object,
                'slug'          => (string) $row->slug,
                'type'          => $row->type_name ?: (string) $row->type,
                'constellation' => $row->constellation,
                'magnitude'     => is_numeric($row->mag) && (float) $row->mag < 99 ? round((float) $row->mag, 1) : null,
            ];
        }

        return ['total' => count($matching), 'objects' => $objects];
    }

    /**
     * An observing list of the objects shown for $reason.
     */
    public function createList(array $result, ?string $reason = null, ?string $name = null, bool $public = false): ?ObservingList
    {
        $names = array_column($this->objects($result, $reason)['objects'], 'name');
        if ($names === []) {
            return null;
        }
        $description = __('Objects you have already seen that are worth a second look with your :instrument at :location.', ['instrument' => $this->instrumentName(), 'location' => $this->locationName()]);

        return ObservingList::createWithObjects($this->user, trim((string) $name) ?: $this->defaultListName(), $names, $description, $public);
    }

    public function defaultListName(): string
    {
        return __('Revisit with :instrument', ['instrument' => $this->instrumentName()]);
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
     * The seen objects the telescope shows at least MIN_GAIN better than the
     * best of the instruments they were seen with, and that were only seen
     * with a much smaller one or never drawn; the largest gain first.
     */
    private function calculate(): array
    {
        $sqm    = (float) $this->location->getSqm();
        $seen   = $this->seen();
        $others = Instrument::whereIn('id', $seen->pluck('instrument_id')->unique()->filter()->all())
            ->where('aperture_mm', '>', 0)
            ->get()
            ->keyBy('id');
        $side = fn (Instrument $instrument) => [
            'instrument'     => $instrument,
            'lens'           => $lens = ContrastReserveCalculator::lensFactor($instrument->lens_id),
            'magnifications' => ContrastReserveCalculator::magnifications($instrument, $this->user, $lens),
        ];
        $new    = $side($this->instrument);
        $byName = $seen->groupBy(fn ($row) => mb_strtolower((string) $row->target_key));
        // the magnifications of each instrument once
        $sides = [];

        $aperture = (float) $this->instrument->aperture_mm;
        $objects  = [];
        foreach ($this->candidates($seen->pluck('target_key')->unique()->all()) as $object) {
            $rows = $byName[mb_strtolower((string) $object->name)] ?? collect();
            $used = $rows->pluck('instrument_id')->unique()->map(fn ($id) => $others[$id] ?? null)->filter();
            // seen only with instruments of unknown aperture: nothing to compare with
            if ($used->isEmpty()) {
                continue;
            }
            $largest = (float) $used->max('aperture_mm');
            $drawn   = (bool) $rows->max('drawn');
            if ($largest * self::APERTURE_RATIO > $aperture && $drawn) {
                continue;
            }

            $target   = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
            $altitude = ContrastReserveCalculator::highestAltitude($object->decl, $this->location);
            $after    = $this->reserve($target, $new, $altitude);
            if ($after['contrast_reserve'] === null || $after['contrast_reserve'] <= EquipmentGain::VISIBLE_ABOVE) {
                continue;
            }
            $before = null;
            foreach ($used as $instrument) {
                $sides[$instrument->id] ??= $side($instrument);
                $reserve = $this->reserve($target, $sides[$instrument->id], $altitude)['contrast_reserve'];
                if ($reserve !== null && ($before === null || $reserve > $before)) {
                    $before = $reserve;
                }
            }
            if ($before === null || $after['contrast_reserve'] - $before < self::MIN_GAIN) {
                continue;
            }
            $objects[] = [
                'name'             => (string) $object->name,
                'contrast_reserve' => round($after['contrast_reserve'], 2),
                'category'         => ContrastReserveCalculator::category($after['contrast_reserve']),
                'magnification'    => $after['magnification'],
                'before'           => round($before, 2),
                'gain'             => round($after['contrast_reserve'] - $before, 2),
                'aperture'         => $largest,
                'drawn'            => $drawn,
                'reason'           => $largest * self::APERTURE_RATIO <= $aperture ? self::SMALLER : self::UNDRAWN,
            ];
        }

        usort($objects, fn (array $a, array $b) => [$b['gain'], $a['name']] <=> [$a['gain'], $b['name']]);

        return ['objects' => $objects, 'sqm' => $sqm];
    }

    private function reserve($target, array $side, ?float $altitude): array
    {
        return ContrastReserveCalculator::calculate($target, $side['instrument'], $this->location, $this->user, $side['lens'], $side['magnifications'], null, $altitude);
    }

    /**
     * The deep-sky objects the observer has seen (observations that were not
     * "not seen"): one row per object and instrument, with whether one of
     * those observations has a drawing.
     *
     * @return Collection<int, object{target_key: string, instrument_id: ?int, drawn: int}>
     */
    private function seen()
    {
        return $this->seenQuery()
            ->groupBy('target_key', 'instrument_id')
            ->get(['target_key', 'instrument_id', DB::raw('MAX(has_drawing) as drawn')]);
    }

    private function seenQuery(): Builder
    {
        return DB::table('observations')
            ->where('observer_username', $this->user->username)
            ->where('target_table', 'objects')
            ->whereNotExists(function (Builder $details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
            });
    }

    /**
     * The objects of the names with a magnitude that rise at the location,
     * not of EquipmentGain::EXCLUDED_TYPES.
     */
    private function candidates(array $names): iterable
    {
        $latitude = (float) $this->location->latitude;

        return collect(array_chunk($names, 1000))->flatMap(fn (array $chunk) => DB::table('objects as o')
            ->whereIn('o.name', $chunk)
            ->whereNotIn('o.type', EquipmentGain::EXCLUDED_TYPES)
            ->whereNotNull('o.mag')
            ->where('o.mag', '<', 99)
            ->whereBetween('o.decl', [$latitude - 90.0, $latitude + 90.0])
            ->get(['o.name', 'o.mag', 'o.diam1', 'o.diam2', 'o.decl']))
            ->unique('name');
    }

    /**
     * Changes with everything the result depends on: the instruments, the
     * eyepieces, the location and the observer's observations.
     */
    private function key(): string
    {
        return $this->key ??= $this->makeKey();
    }

    private function makeKey(): string
    {
        $instruments = Instrument::where('user_id', $this->user->id)
            ->orderBy('id')
            ->get(['id', 'aperture_mm', 'focal_length_mm', 'fixedMagnification', 'field_of_view_deg', 'lens_id'])
            ->map(fn (Instrument $instrument) => array_values($instrument->getAttributes()))
            ->all();
        $eyepieces = collect(ContrastReserveCalculator::eyepieces($this->user))
            ->map(fn ($eyepiece) => [$eyepiece->id, $eyepiece->focal_length_mm, $eyepiece->field_stop_mm, $eyepiece->apparentFOV])
            ->all();
        $location     = $this->location ? [$this->location->id, $this->location->latitude, $this->location->getSqm()] : null;
        $observations = (array) $this->seenQuery()->selectRaw('COUNT(*) as count, MAX(updated_at) as updated')->first();

        return 'revisit_objects:v'.self::RESULT_VERSION.':'.$this->user->id.':'.(int) $this->instrument?->id.':'
            .md5(json_encode([$instruments, $eyepieces, $location, $observations]));
    }
}
