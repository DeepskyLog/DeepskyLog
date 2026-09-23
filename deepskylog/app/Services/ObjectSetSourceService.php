<?php

namespace App\Services;

use App\Http\Controllers\SearchExportController;
use App\Models\DeepskyObject;
use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Works out which objects an object sets page is about.
 *
 * The page is reached from every list of objects on the site -- an observing
 * list, the objects near an object, and (advanced) search results -- and the
 * query string says which one:
 *
 *   ?source=list&list={slug}
 *   ?source=nearby&object={slug}&ra={deg}&dec={deg}&radius={arcmin}
 *   ?source=search&{the search's own query parameters}
 *
 * Only deep-sky objects have the fixed position a chart and a DSS photo need,
 * so comets, planets and the like in a search result are left out.
 *
 * The object reports (App\Livewire\ObjectReport) take their objects from the
 * same places, so they use this service too, with KIND_REPORT: that only
 * changes the page title and the file name.
 */
class ObjectSetSourceService
{
    /**
     * Each object takes a few pages of charts and photos. A few hundred of
     * them is already an atlas of its own, and hours of work for the queue.
     */
    public const MAX_OBJECTS = 200;

    public const KIND_OBJECT_SETS = 'object-sets';

    public const KIND_REPORT = 'report';

    /**
     * @return array{objects: Collection<int, DeepskyObject>, title: string, total: int, list_id: ?int, filename: string}
     */
    public function resolve(Request $request, ?User $user, string $kind = self::KIND_OBJECT_SETS, int $limit = self::MAX_OBJECTS): array
    {
        $source = strtolower(trim((string) $request->query('source', '')));

        [$names, $title, $listId, $filename] = match ($source) {
            'list' => $this->fromList($request, $user, $kind),
            'nearby' => $this->fromNearby($request, $kind),
            'search' => $this->fromSearch($request, $kind),
            default => [collect(), $this->title($kind, 'none'), null, $kind],
        };

        $names = $names
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->values();

        $objects = $this->loadInOrder($names);

        return [
            'objects' => $objects->take($limit)->values(),
            'title' => $title,
            'total' => $objects->count(),
            'list_id' => $listId,
            'filename' => $filename,
        ];
    }

    /**
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromList(Request $request, ?User $user, string $kind): array
    {
        $slug = trim((string) $request->query('list', ''));
        $list = ObservingList::where('slug', $slug)->first();

        if ($list === null) {
            return [collect(), $this->title($kind, 'none'), null, $kind];
        }

        if ($user === null || ! $user->can('view', $list)) {
            throw new AuthorizationException();
        }

        $names = $list->items()->pluck('object_name')
            ->sort(fn ($a, $b) => strnatcasecmp((string) $a, (string) $b))
            ->values();

        return [
            $names,
            $this->title($kind, 'list', ['name' => $list->name]),
            (int) $list->id,
            $kind . '-' . $list->slug,
        ];
    }

    /**
     * The same selection the nearby exports make: every object within the
     * radius of the centre.
     *
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromNearby(Request $request, string $kind): array
    {
        $slug = trim((string) $request->query('object', ''));
        $center = $slug !== '' ? DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first() : null;

        $ra = $request->query('ra');
        $dec = $request->query('dec');
        if ((! is_numeric($ra) || ! is_numeric($dec)) && $center !== null) {
            $ra = $center->ra;
            $dec = $center->decl;
        }

        if (! is_numeric($ra) || ! is_numeric($dec)) {
            return [collect(), $this->title($kind, 'none'), null, $kind];
        }

        $raDeg = (float) $ra;
        if ($raDeg <= 24.0) {
            $raDeg *= 15.0;
        }
        $decDeg = (float) $dec;
        $radiusDeg = max(1, min(600, (int) $request->query('radius', 30))) / 60.0;

        $expr = 'DEGREES(ACOS(LEAST(1, GREATEST(-1, SIN(RADIANS(?))*SIN(RADIANS(`decl`)) + COS(RADIANS(?))*COS(RADIANS(`decl`))*COS(RADIANS((?)-(CASE WHEN `ra` <= 24 THEN `ra`*15 ELSE `ra` END))) ))))';

        $names = DeepskyObject::query()
            ->whereRaw("{$expr} <= ?", [$decDeg, $decDeg, $raDeg, $radiusDeg])
            ->pluck('name')
            ->sort(fn ($a, $b) => strnatcasecmp((string) $a, (string) $b))
            ->values();

        $centerName = $center?->name;

        return [
            $names,
            $centerName !== null
                ? $this->title($kind, 'near', ['object' => $centerName])
                : $this->title($kind, 'nearby'),
            null,
            $kind . '-near-' . ($center?->slug ?? 'position'),
        ];
    }

    /**
     * The rows the search exports produce, in the order the search shows them.
     *
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromSearch(Request $request, string $kind): array
    {
        $query = $request->query();
        unset($query['source']);
        $searchRequest = Request::create('/search', 'GET', $query);

        [, $rows] = app(SearchExportController::class)->resolveRows($searchRequest);

        $names = collect($rows)
            ->filter(fn ($row) => ($row->source_table ?? 'objects') === 'objects')
            ->map(fn ($row) => $row->obj_name ?? $row->name ?? null)
            ->values();

        $q = trim((string) ($query['q'] ?? ''));

        return [
            $names,
            $q !== '' ? $this->title($kind, 'search', ['q' => $q]) : $this->title($kind, 'results'),
            null,
            $kind . '-search',
        ];
    }

    /**
     * The page title for a kind of page and the list of objects it is about.
     *
     * @param  array<string, string>  $replace
     */
    private function title(string $kind, string $which, array $replace = []): string
    {
        if ($kind === self::KIND_REPORT) {
            return match ($which) {
                'list' => __('Report for observing list ":name"', $replace),
                'near' => __('Report for the objects near :object', $replace),
                'nearby' => __('Report for nearby objects'),
                'search' => __('Report for the search ":q"', $replace),
                'results' => __('Report for the search results'),
                default => __('Report'),
            };
        }

        return match ($which) {
            'list' => __('Object sets for observing list ":name"', $replace),
            'near' => __('Object sets for the objects near :object', $replace),
            'nearby' => __('Object sets for nearby objects'),
            'search' => __('Object sets for the search ":q"', $replace),
            'results' => __('Object sets for the search results'),
            default => __('Object sets'),
        };
    }

    /**
     * @param  Collection<int, string>  $names
     * @return Collection<int, DeepskyObject>
     */
    private function loadInOrder(Collection $names): Collection
    {
        if ($names->isEmpty()) {
            return collect();
        }

        $byName = $names->chunk(500)
            ->flatMap(fn (Collection $chunk) => DeepskyObject::whereIn('name', $chunk->all())->get())
            ->keyBy('name');

        return $names
            ->map(fn (string $name) => $byName->get($name))
            ->filter()
            ->values();
    }
}
