<?php

namespace App\Services;

use App\Jobs\ComputeEquipmentGain;
use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "What's new for me" after new equipment: the deep-sky objects an
 * observer's telescope or eyepiece shows at one of their locations (a
 * contrast reserve above VISIBLE_ABOVE) that they could not see before:
 * with none of their other active telescopes, or for an eyepiece, with the
 * standard telescope without it. Objects the observer has seen are left out
 * when the result is shown (objects()), the rest are ranked by the number of
 * observations in DeepskyLog.
 *
 * Both sides are calculated with ContrastReserveCalculator, not read from
 * user_object_metrics: those only hold the standard equipment, with all
 * eyepieces. A run takes a few seconds (the old telescopes only for the
 * objects the new one shows), so it is queued (ComputeEquipmentGain) and its
 * result cached under a key that changes with the equipment and location.
 */
class EquipmentGain
{
    public const INSTRUMENT = 'instrument';

    public const EYEPIECE = 'eyepiece';

    public const KINDS = [self::INSTRUMENT, self::EYEPIECE];

    /** Visible: a contrast reserve above this, "difficult" or better (as Tonight's best). */
    public const VISIBLE_ABOVE = 0.1;

    /** The objects shown and put on a list, of the most observed. */
    public const MAX_OBJECTS = 200;

    /** Not deep-sky objects with a contrast reserve, or catalog errors. */
    public const EXCLUDED_TYPES = [...TonightsBest::EXCLUDED_TYPES, 'NONEX'];

    public const CACHE_DAYS = 7;

    /**
     * The calculations queued for one result before the page gives up: a job
     * that ends without it (a worker running older code, an error) would
     * otherwise be queued again on every poll.
     */
    public const MAX_ATTEMPTS = 3;

    /** Part of the cache key: raise it when the cached result changes shape or calculation. */
    private const RESULT_VERSION = 2;

    private ?string $key = null;

    private function __construct(
        public readonly User $user,
        public readonly string $kind,
        public readonly Instrument|Eyepiece $equipment,
        public readonly ?Location $location,
        public readonly ?Instrument $instrument,
        public readonly ?array $compareIds = null
    ) {
    }

    /**
     * The gain of a telescope (or binoculars) of the observer, against
     * the instruments of $compareIds, null for all their other active ones.
     */
    public static function forInstrument(User $user, Instrument $instrument, ?Location $location, ?array $compareIds = null): self
    {
        if ($compareIds !== null) {
            $compareIds = array_values(array_unique(array_map('intval', $compareIds)));
            sort($compareIds);
        }

        return new self($user, self::INSTRUMENT, $instrument, $location, $instrument, $compareIds);
    }

    /**
     * The gain of an eyepiece of the observer, with their standard telescope.
     */
    public static function forEyepiece(User $user, Eyepiece $eyepiece, ?Location $location): self
    {
        return new self($user, self::EYEPIECE, $eyepiece, $location, $user->standardInstrument);
    }

    public static function for(User $user, string $kind, int $equipmentId, ?int $locationId, ?array $compareIds = null): ?self
    {
        $model     = $kind === self::EYEPIECE ? Eyepiece::class : Instrument::class;
        $equipment = $model::where('user_id', $user->id)->find($equipmentId);
        if (! $equipment) {
            return null;
        }
        $location = self::location($user, $locationId);

        return $kind === self::EYEPIECE
            ? self::forEyepiece($user, $equipment, $location)
            : self::forInstrument($user, $equipment, $location, $compareIds);
    }

    /**
     * The chosen location of the observer, else their standard location,
     * else their first active one.
     */
    public static function location(User $user, ?int $locationId): ?Location
    {
        $locations = self::locations($user);

        return $locations->firstWhere('id', $locationId)
            ?? $locations->firstWhere('id', (int) $user->stdlocation)
            ?? $locations->first();
    }

    /**
     * The observer's active locations with coordinates.
     *
     * @return \Illuminate\Support\Collection<int, Location>
     */
    public static function locations(User $user)
    {
        return Location::where('user_id', $user->id)
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->values();
    }

    /**
     * Why nothing can be compared, as a translation key; null when it can.
     */
    public function unavailable(): ?string
    {
        if (! $this->location) {
            return 'Add a location to see which objects you can see.';
        }
        if (! is_numeric($this->location->getSqm())) {
            return 'Give the location a sky brightness (SQM) or limiting magnitude to see which objects you can see.';
        }
        if ($this->kind === self::EYEPIECE) {
            if (! $this->instrument) {
                return 'Set your standard instrument to see what this eyepiece adds to it.';
            }
            if ((float) $this->instrument->fixedMagnification > 0 || (float) $this->instrument->focal_length_mm <= 0) {
                return 'Your standard instrument does not take eyepieces.';
            }
            if ($this->eyepieceMagnification() === null) {
                return 'This eyepiece has no focal length.';
            }
        } elseif ((float) $this->instrument->aperture_mm <= 0) {
            return 'This instrument has no aperture.';
        }

        return null;
    }

    /**
     * The cached result, or null when it is not calculated yet.
     *
     * @return array{total: int, objects: list<array{name: string, contrast_reserve: float, category: string, magnification: ?int, before: ?float}>, compared: list<string>, sqm: float}|null
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
        ComputeEquipmentGain::dispatch($this->user->id, $this->kind, (int) $this->equipment->id, (int) $this->location->id, $this->compareIds);

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

    /**
     * Lets the page queue the calculation again after it failed().
     */
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
     * The objects of the result the observer has not seen yet, the most
     * observed first, at most $limit, with their type, constellation and
     * magnitude.
     *
     * @return array{total: int, objects: list<array<string, mixed>>}
     */
    public function objects(array $result, int $limit = self::MAX_OBJECTS): array
    {
        $seen   = $this->seenNames();
        $unseen = array_values(array_filter($result['objects'], fn (array $object) => ! isset($seen[mb_strtolower($object['name'])])));
        $chosen = array_slice($unseen, 0, $limit);

        $rows = DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->whereIn('o.name', array_column($chosen, 'name'))
            ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 't.name as type_name', 'c.name as constellation', 's.observations'])
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
                'observations'  => (int) ($row->observations ?? 0),
            ];
        }

        return ['total' => count($unseen), 'objects' => $objects];
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
        $equipment   = $this->equipmentName();
        $description = $this->kind === self::EYEPIECE
            ? __('Objects the :eyepiece lets you see with your :instrument at :location that you could not see before.', ['eyepiece' => $equipment, 'instrument' => $this->instrumentName(), 'location' => $this->locationName()])
            : __('Objects the :instrument lets you see at :location that you could not see with your other instruments.', ['instrument' => $equipment, 'location' => $this->locationName()]);

        return ObservingList::createWithObjects($this->user, trim((string) $name) ?: __('New with :equipment', ['equipment' => $equipment]), $names, $description, $public);
    }

    public function equipmentName(): string
    {
        return html_entity_decode((string) ($this->equipment instanceof Instrument ? $this->equipment->fullName() : $this->equipment->name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
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
     * The objects the new side shows and the old one does not, the most
     * observed first, with the names of what they were compared with.
     */
    private function calculate(): array
    {
        [$new, $old] = $this->sides();
        $sqm         = (float) $this->location->getSqm();

        $gained = [];
        foreach ($this->candidates() as $object) {
            $target   = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
            $altitude = ContrastReserveCalculator::highestAltitude($object->decl, $this->location);
            $after    = $this->reserve($target, $new, $altitude);
            if ($after['contrast_reserve'] === null || $after['contrast_reserve'] <= self::VISIBLE_ABOVE) {
                continue;
            }
            // the best of the old equipment, stopping at the first that shows it
            $before = null;
            foreach ($old as $side) {
                $reserve = $this->reserve($target, $side, $altitude)['contrast_reserve'];
                if ($reserve !== null && ($before === null || $reserve > $before)) {
                    $before = $reserve;
                }
                if ($before !== null && $before > self::VISIBLE_ABOVE) {
                    continue 2;
                }
            }
            $gained[] = [
                'name'             => (string) $object->name,
                'contrast_reserve' => round($after['contrast_reserve'], 2),
                // of the reserve before rounding, which may round down to VISIBLE_ABOVE
                'category'      => ContrastReserveCalculator::category($after['contrast_reserve']),
                'magnification' => $after['magnification'],
                'before'        => $before === null ? null : round($before, 2),
                'observations'  => (int) ($object->observations ?? 0),
            ];
        }

        usort($gained, fn (array $a, array $b) => [$b['observations'], $b['contrast_reserve'], $a['name']] <=> [$a['observations'], $a['contrast_reserve'], $b['name']]);

        return [
            'total'    => count($gained),
            'objects'  => array_map(fn (array $object) => array_diff_key($object, ['observations' => true]), $gained),
            'compared' => array_map(fn (array $side) => $side['label'], $old),
            'sqm'      => $sqm,
        ];
    }

    /**
     * The new side and the old sides: an instrument with its magnifications
     * and lens factor. For an instrument, the observer's other active
     * instruments, the largest first; for an eyepiece, the standard
     * instrument without it.
     *
     * @return array{0: array, 1: list<array>}
     */
    private function sides(): array
    {
        $side = fn (Instrument $instrument, ?array $magnifications = null) => [
            'instrument'     => $instrument,
            'lens'           => $lens = ContrastReserveCalculator::lensFactor($instrument->lens_id),
            'magnifications' => $magnifications ?? ContrastReserveCalculator::magnifications($instrument, $this->user, $lens),
            'label'          => html_entity_decode((string) $instrument->fullName(), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ];

        if ($this->kind === self::EYEPIECE) {
            $lens    = ContrastReserveCalculator::lensFactor($this->instrument->lens_id);
            $without = [];
            foreach (ContrastReserveCalculator::magnifications($this->instrument, $this->user, $lens) as $magnification => $eyepieces) {
                $others = array_values(array_filter($eyepieces, fn ($eyepiece) => (int) $eyepiece->id !== (int) $this->equipment->id));
                // the generic magnifications, of an observer without eyepieces, are none
                if ($others !== []) {
                    $without[$magnification] = $others;
                }
            }
            $with                                   = $without;
            $with[$this->eyepieceMagnification()][] = $this->equipment;
            $old                                    = $side($this->instrument, $without);
            $old['label']                           = __(':instrument without the :eyepiece', ['instrument' => $old['label'], 'eyepiece' => $this->equipmentName()]);

            return [$side($this->instrument, $with), [$old]];
        }

        $others = $this->comparable()
            ->when($this->compareIds !== null, fn ($query) => $query->whereKey($this->compareIds))
            ->get();

        return [$side($this->equipment), $others->map(fn (Instrument $instrument) => $side($instrument))->all()];
    }

    /**
     * The instruments a telescope can be compared with: the observer's other
     * active ones, the largest first.
     */
    public function comparable()
    {
        return Instrument::where('user_id', $this->user->id)
            ->where('active', true)
            ->whereKeyNot($this->equipment->id)
            ->where('aperture_mm', '>', 0)
            ->orderByDesc('aperture_mm');
    }

    private function reserve($target, array $side, ?float $altitude): array
    {
        return ContrastReserveCalculator::calculate($target, $side['instrument'], $this->location, $this->user, $side['lens'], $side['magnifications'], null, $altitude);
    }

    /**
     * The magnification the eyepiece gives with the standard instrument.
     */
    private function eyepieceMagnification(): ?int
    {
        $focalLength = (float) $this->equipment->focal_length_mm;
        if ($focalLength <= 0 || ! $this->instrument) {
            return null;
        }
        $magnification = (int) round((float) $this->instrument->focal_length_mm / $focalLength * ContrastReserveCalculator::lensFactor($this->instrument->lens_id));

        return $magnification > 0 ? $magnification : null;
    }

    /**
     * The deep-sky objects with a magnitude that rise at the location.
     */
    private function candidates(): iterable
    {
        $latitude = (float) $this->location->latitude;

        return DB::table('objects as o')
            ->leftJoin('object_observation_stats as s', 's.object_name', '=', 'o.name')
            ->whereNotIn('o.type', self::EXCLUDED_TYPES)
            ->whereNotNull('o.mag')
            ->where('o.mag', '<', 99)
            ->whereBetween('o.decl', [$latitude - 90.0, $latitude + 90.0])
            ->get(['o.name', 'o.mag', 'o.diam1', 'o.diam2', 'o.decl', 's.observations'])
            ->unique('name');
    }

    /**
     * The deep-sky objects the observer has seen (observations that were not
     * "not seen"), by name in lower case.
     *
     * @return array<string, true>
     */
    private function seenNames(): array
    {
        return DB::table('observations')
            ->where('observer_username', $this->user->username)
            ->where('target_table', 'objects')
            ->whereNotExists(function (Builder $details) {
                $details->selectRaw('1')
                    ->from('deepsky_observation_details')
                    ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                    ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN);
            })
            ->distinct()
            ->pluck('target_key')
            ->mapWithKeys(fn ($name) => [mb_strtolower((string) $name) => true])
            ->all();
    }

    /**
     * Changes with everything the result depends on: the equipment and its
     * magnifications, the other instruments and the location.
     */
    private function key(): string
    {
        return $this->key ??= $this->makeKey();
    }

    private function makeKey(): string
    {
        $instruments = Instrument::where('user_id', $this->user->id)
            ->where('active', true)
            ->orderBy('id')
            ->get(['id', 'aperture_mm', 'focal_length_mm', 'fixedMagnification', 'field_of_view_deg', 'lens_id'])
            ->map(fn (Instrument $instrument) => array_values($instrument->getAttributes()))
            ->all();
        $eyepieces = collect(ContrastReserveCalculator::eyepieces($this->user))
            ->map(fn ($eyepiece) => [$eyepiece->id, $eyepiece->focal_length_mm, $eyepiece->field_stop_mm, $eyepiece->apparentFOV])
            ->all();
        $location  = $this->location ? [$this->location->id, $this->location->latitude, $this->location->getSqm()] : null;
        $equipment = $this->equipment instanceof Eyepiece
            ? [$this->equipment->focal_length_mm, $this->equipment->field_stop_mm, $this->equipment->apparentFOV, $this->instrument?->id]
            : [];

        return 'equipment_gain:v'.self::RESULT_VERSION.':'.$this->user->id.':'.$this->kind.':'.$this->equipment->id.':'.md5(json_encode([$instruments, $eyepieces, $location, $equipment, $this->compareIds]));
    }
}
