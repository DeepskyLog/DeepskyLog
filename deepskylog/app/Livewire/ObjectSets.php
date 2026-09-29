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
        $selected = $row === null
            ? $this->rows
            : (isset($this->rows[$row]) ? [$this->rows[$row]] : []);

        $objects = collect($selected)
            ->map(fn (array $r) => [
                'name' => $r['name'],
                'fovs' => ObjectSetPdfService::parseNumbers($r['fovs']),
                'dsos' => ObjectSetPdfService::parseNumbers($r['dsos']),
                'stars' => ObjectSetPdfService::parseNumbers($r['stars']),
                'photos' => ObjectSetPdfService::parseNumbers($r['photos']),
            ])
            ->filter(fn (array $o) => $o['fovs'] !== [] || $o['photos'] !== [] || $this->dataPage)
            ->values()
            ->all();

        if ($objects === []) {
            $this->addError('generate', __('There is nothing to generate: give at least one field of view or photo size.'));

            return;
        }

        $user = auth()->user();
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
