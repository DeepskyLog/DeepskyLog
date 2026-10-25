<?php

namespace App\Livewire;

use App\Jobs\GenerateObjectSetsPdf;
use App\Models\DeepskyObject;
use App\Services\ObjectSetDataBuilder;
use App\Services\ObjectSetPdfService;
use App\Support\EphemerisDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The object sets page: one row per object with the fields of view, object
 * and star magnitudes and photo sizes of its set, which a queue worker then
 * turns into a PDF.
 */
class ObjectSets extends Component
{
    /** Fields a row carries, each a space separated list of numbers. */
    public const FIELDS = ['fovs', 'dsos', 'stars', 'photos'];

    /** How many of a user's recent sets the page keeps track of. */
    private const RECENT_SETS = 20;

    /** How many values a row's field may hold. */
    public const MAX_VALUES = 6;

    /**
     * How many charts and photos one PDF may hold: the most objects the page
     * is given (ObjectSetSourceService::MAX_OBJECTS) with the default three
     * fields of view and two photos each.
     */
    public const MAX_CHARTS = 1000;

    /**
     * The objects of the rows, by row: the rows themselves can be changed
     * from the browser, so the names are taken from here.
     *
     * @var array<int, string>
     */
    #[Locked]
    public array $names = [];

    #[Locked]
    public string $title = '';

    #[Locked]
    public int $total = 0;

    #[Locked]
    public ?int $listId = null;

    #[Locked]
    public string $filename = 'object-sets';

    /**
     * @var array<int, array{name: string, size: string, diam_arcmin: float, all: bool, checked: array<string, bool>, fovs: string, dsos: string, stars: string, photos: string}>
     */
    public array $rows = [];

    /** The values the "V" buttons copy to the ticked rows. */
    public string $fovs = '';

    public string $dsos = '';

    public string $stars = '';

    public string $photos = '';

    public bool $dataPage = true;

    public bool $ephemerides = true;

    public bool $yearEphemerides = true;

    public bool $indexPage = true;

    /** @var array<int, string> */
    #[Locked]
    public array $tokens = [];

    /**
     * @param  Collection<int, DeepskyObject>|array<int, DeepskyObject>  $objects
     */
    public function mount($objects = [], string $title = '', int $total = 0, ?int $listId = null, string $filename = 'object-sets'): void
    {
        $user = auth()->user();

        $this->title = $title;
        $this->total = $total;
        $this->listId = $listId;
        $this->filename = $filename;

        $this->fovs = self::joinValues([$user->overviewFoV ?? null, $user->lookupFoV ?? null, $user->detailFoV ?? null], '120 60 15');
        $this->dsos = self::joinValues([$user->overviewdsos ?? null, $user->lookupdsos ?? null, $user->detaildsos ?? null], '10 12 15');
        $this->stars = self::joinValues([$user->overviewstars ?? null, $user->lookupstars ?? null, $user->detailstars ?? null], '10 12 15');
        $this->photos = self::joinValues([$user->photosize1 ?? null, $user->photosize2 ?? null], '60 25');

        $this->rows = collect($objects)
            ->map(fn (DeepskyObject $object) => [
                'name' => (string) $object->name,
                'size' => ObjectSetDataBuilder::formatSize($object->diam1, $object->diam2),
                'diam_arcmin' => round(max((float) ($object->diam1 ?? 0), (float) ($object->diam2 ?? 0)) / 60.0, 1),
                'all' => false,
                'checked' => array_fill_keys(self::FIELDS, false),
                'fovs' => $this->fovs,
                'dsos' => $this->dsos,
                'stars' => $this->stars,
                'photos' => $this->photos,
            ])
            ->values()
            ->all();
        $this->names = array_column($this->rows, 'name');

        $this->tokens = collect(Cache::get(GenerateObjectSetsPdf::recentKey((int) $user->id), []))
            ->filter(fn ($token) => is_string($token) && Cache::has(GenerateObjectSetsPdf::statusKey($token)))
            ->values()
            ->all();
    }

    /**
     * Copy the value above a column to every row ticked in that column.
     */
    public function applyColumn(string $field): void
    {
        if (! in_array($field, self::FIELDS, true)) {
            return;
        }

        foreach ($this->rows as $i => $row) {
            if (! empty($row['checked'][$field])) {
                $this->rows[$i][$field] = $this->{$field};
            }
        }
    }

    /**
     * Copy all four values to every row ticked in the first column.
     */
    public function applyAll(): void
    {
        foreach ($this->rows as $i => $row) {
            if (! empty($row['all'])) {
                foreach (self::FIELDS as $field) {
                    $this->rows[$i][$field] = $this->{$field};
                }
            }
        }
    }

    /**
     * Tick every row of a column, or untick them all when they already are.
     */
    public function toggleColumn(string $field): void
    {
        if ($field !== 'all' && ! in_array($field, self::FIELDS, true)) {
            return;
        }

        $read = fn (array $row) => $field === 'all' ? ! empty($row['all']) : ! empty($row['checked'][$field]);
        $value = ! collect($this->rows)->every($read);

        foreach ($this->rows as $i => $row) {
            if ($field === 'all') {
                $this->rows[$i]['all'] = $value;
            } else {
                $this->rows[$i]['checked'][$field] = $value;
            }
        }
    }

    /**
     * Queue the set of one object, or of all of them when no row is given.
     */
    public function generate(?int $row = null): void
    {
        $indexes = $row === null ? array_keys($this->names) : (isset($this->names[$row]) ? [$row] : []);
        $values = fn (int $i, string $field) => array_slice(
            ObjectSetPdfService::parseNumbers(is_string($this->rows[$i][$field] ?? null) ? $this->rows[$i][$field] : ''),
            0,
            self::MAX_VALUES
        );

        $objects = collect($indexes)
            ->map(fn (int $i) => [
                'name' => $this->names[$i],
                'fovs' => $values($i, 'fovs'),
                'dsos' => $values($i, 'dsos'),
                'stars' => $values($i, 'stars'),
                'photos' => $values($i, 'photos'),
            ])
            ->filter(fn (array $o) => $o['fovs'] !== [] || $o['photos'] !== [] || $this->dataPage)
            ->values()
            ->all();

        if ($objects === []) {
            $this->addError('generate', __('There is nothing to generate: give at least one field of view or photo size.'));

            return;
        }

        $charts = collect($objects)->sum(fn (array $o) => count($o['fovs']) + count($o['photos']));
        if ($charts > self::MAX_CHARTS) {
            $this->addError('generate', __('This set has :count charts and photos; at most :max fit in one PDF.', ['count' => $charts, 'max' => self::MAX_CHARTS]));

            return;
        }

        $user = auth()->user();
        if (($refusal = GenerateObjectSetsPdf::refusal((int) $user->id)) !== null) {
            $this->addError('generate', $refusal);

            return;
        }

        $token = (string) Str::uuid();
        $filename = ObjectSetPdfService::filenameFor($this->filename, $objects);
        $label = $row === null
            ? trans_choice(':count object|:count objects', count($objects), ['count' => count($objects)])
            : $objects[0]['name'];

        Cache::put(GenerateObjectSetsPdf::statusKey($token), [
            'state' => 'queued',
            'user_id' => $user->id,
            'label' => $label,
            'filename' => $filename,
            'created_at' => now()->toIso8601String(),
            'total' => count($objects),
        ], now()->addHours(GenerateObjectSetsPdf::KEEP_HOURS));

        GenerateObjectSetsPdf::dispatch($token, (int) $user->id, [
            'objects' => $objects,
            'data_page' => $this->dataPage,
            'ephemerides' => $this->dataPage && $this->ephemerides,
            'year_ephemerides' => $this->dataPage && $this->yearEphemerides,
            'index_page' => $this->indexPage,
            'date' => $this->ephemerisDate(),
            'list_id' => $this->listId,
        ]);
        GenerateObjectSetsPdf::recordQueued((int) $user->id);

        $this->tokens = GenerateObjectSetsPdf::forgetUnfinished((int) $user->id, $filename, $this->tokens);
        array_unshift($this->tokens, $token);
        $this->tokens = array_slice(array_values(array_unique($this->tokens)), 0, self::RECENT_SETS);
        Cache::put(GenerateObjectSetsPdf::recentKey((int) $user->id), $this->tokens, now()->addHours(GenerateObjectSetsPdf::KEEP_HOURS));

        $this->resetErrorBag('generate');
    }

    public function render()
    {
        $userId = (int) auth()->id();

        $jobs = collect($this->tokens)
            ->map(function (string $token) use ($userId) {
                $status = Cache::get(GenerateObjectSetsPdf::statusKey($token));

                return is_array($status) && (int) ($status['user_id'] ?? 0) === $userId
                    ? ['token' => $token] + $status
                    : null;
            })
            ->filter()
            ->values();

        // The observer sees them finish here and needs no message.
        GenerateObjectSetsPdf::watch($jobs);

        return view('livewire.object-sets', [
            'jobs' => $jobs,
            'pending' => $jobs->contains(fn (array $job) => in_array($job['state'] ?? '', ['queued', 'processing'], true)),
            'smallestFov' => fn (array $row) => min(ObjectSetPdfService::parseNumbers($row['fovs']) ?: [INF]),
            'smallestPhoto' => fn (array $row) => min(ObjectSetPdfService::parseNumbers($row['photos']) ?: [INF]),
        ]);
    }

    private function ephemerisDate(): string
    {
        return EphemerisDate::current();
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function joinValues(array $values, string $default): string
    {
        $numbers = array_filter($values, fn ($v) => is_numeric($v) && (float) $v > 0);

        return count($numbers) === count($values)
            ? implode(' ', array_map(fn ($v) => (string) (0 + $v), $numbers))
            : $default;
    }
}
