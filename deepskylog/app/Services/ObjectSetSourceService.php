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
 */
class ObjectSetSourceService
{
    /**
     * Each object takes a few pages of charts and photos. A few hundred of
     * them is already an atlas of its own, and hours of work for the queue.
     */
    public const MAX_OBJECTS = 200;

    /**
     * @return array{objects: Collection<int, DeepskyObject>, title: string, total: int, list_id: ?int, filename: string}
     */
    public function resolve(Request $request, ?User $user): array
    {
        $source = strtolower(trim((string) $request->query('source', '')));

        [$names, $title, $listId, $filename] = match ($source) {
            'list' => $this->fromList($request, $user),
            'nearby' => $this->fromNearby($request),
            'search' => $this->fromSearch($request),
            default => [collect(), __('Object sets'), null, 'object-sets'],
        };

        $names = $names
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->unique()
            ->values();

        $objects = $this->loadInOrder($names);

        return [
            'objects' => $objects->take(self::MAX_OBJECTS)->values(),
            'title' => $title,
            'total' => $objects->count(),
            'list_id' => $listId,
            'filename' => $filename,
        ];
    }

    /**
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromList(Request $request, ?User $user): array
    {
        $slug = trim((string) $request->query('list', ''));
        $list = ObservingList::where('slug', $slug)->first();

        if ($list === null) {
            return [collect(), __('Object sets'), null, 'object-sets'];
        }

        if ($user === null || ! $user->can('view', $list)) {
            throw new AuthorizationException();
        }

        $names = $list->items()->pluck('object_name')
            ->sort(fn ($a, $b) => strnatcasecmp((string) $a, (string) $b))
            ->values();

        return [
            $names,
            __('Object sets for observing list ":name"', ['name' => $list->name]),
            (int) $list->id,
            'object-sets-' . $list->slug,
        ];
    }

    /**
     * The same selection the nearby exports make: every object within the
     * radius of the centre.
     *
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromNearby(Request $request): array
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
            return [collect(), __('Object sets'), null, 'object-sets'];
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
                ? __('Object sets for the objects near :object', ['object' => $centerName])
                : __('Object sets for nearby objects'),
            null,
            'object-sets-near-' . ($center?->slug ?? 'position'),
        ];
    }

    /**
     * The rows the search exports produce, in the order the search shows them.
     *
     * @return array{0: Collection<int, string>, 1: string, 2: ?int, 3: string}
     */
    private function fromSearch(Request $request): array
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
            $q !== '' ? __('Object sets for the search ":q"', ['q' => $q]) : __('Object sets for the search results'),
            null,
            'object-sets-search',
        ];
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
