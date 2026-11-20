<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\Observation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The observer\'s own observations, newest first, with a short part of each description. Filter by date range, object, kind, constellation, instrument, location, seen or not seen, and drawings. Use get-observation for the full description of one.')]
#[IsReadOnly]
class ListMyObservations extends Tool
{
    public const MAX_LIMIT = 100;

    private const KINDS = ['deepsky', 'comet', 'planet', 'lunar', 'solarsystem'];

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'from'          => ['nullable', 'date_format:Y-m-d'],
            'to'            => ['nullable', 'date_format:Y-m-d'],
            'object'        => ['nullable', 'string', 'max:100'],
            'kind'          => ['nullable', 'string', 'in:' . implode(',', self::KINDS)],
            'constellation' => ['nullable', 'string', 'max:40'],
            'instrument_id' => ['nullable', 'integer'],
            'location_id'   => ['nullable', 'integer'],
            'seen'          => ['nullable', 'boolean'],
            'with_drawing'  => ['nullable', 'boolean'],
            'limit'         => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
            'offset'        => ['nullable', 'integer', 'min:0'],
        ]);
        $user   = $request->user();
        $limit  = (int) ($validated['limit'] ?? 25);
        $offset = (int) ($validated['offset'] ?? 0);

        $query = McpData::observations($user)
            ->leftJoin('instruments as i', 'i.id', '=', 'observations.instrument_id')
            ->leftJoin('locations as l', 'l.id', '=', 'observations.location_id');
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            if (! empty($validated[$key])) {
                $query->where('observations.observed_on', $operator, $validated[$key]);
            }
        }
        if (! empty($validated['object'])) {
            $name      = trim($validated['object']);
            $canonical = app(\App\Services\ObservingListItemNameResolver::class)->resolve($name);
            $query->whereIn('observations.target_name', array_unique(array_filter([$name, $canonical])));
        }
        if (! empty($validated['kind'])) {
            $query->where('observations.target_type', $validated['kind']);
        }
        if (! empty($validated['constellation'])) {
            $constellation = trim($validated['constellation']);
            $query->where('observations.target_type', 'deepsky')
                ->whereIn('observations.target_name', \Illuminate\Support\Facades\DB::table('objects as o')
                    ->join('constellations as c', 'c.id', '=', 'o.con')
                    ->where(fn ($q) => $q->where('c.id', $constellation)->orWhere('c.name', $constellation))
                    ->select('o.name'));
        }
        foreach (['instrument_id', 'location_id'] as $key) {
            if (! empty($validated[$key])) {
                $query->where('observations.' . $key, $validated[$key]);
            }
        }
        if (array_key_exists('seen', $validated) && $validated['seen'] !== null) {
            $validated['seen']
                ? McpData::seen($query)
                : $query->where('details.visibility', Observation::VISIBILITY_NOT_SEEN);
        }
        if (! empty($validated['with_drawing'])) {
            $query->where('observations.has_drawing', true);
        }

        $total = (clone $query)->count();
        $rows  = $query->orderByDesc('observations.observed_on')->orderByDesc('observations.id')
            ->offset($offset)->limit($limit)
            ->get([
                'observations.id', 'observations.target_name', 'observations.target_type', 'observations.observed_on',
                'observations.observed_time', 'observations.description', 'observations.has_drawing', 'observations.observer_class',
                'details.visibility', 'details.magnification', 'i.name as instrument', 'l.name as location',
            ]);

        return Response::structured([
            'total'        => $total,
            'offset'       => $offset,
            'returned'     => $rows->count(),
            'observations' => $rows->map(fn ($row) => array_filter([
                'id'            => $row->id,
                'object'        => $row->target_name,
                'kind'          => $row->target_type,
                'date'          => $row->observed_on,
                'time_ut'       => $row->observed_time ?: null,
                'instrument'    => McpData::plain($row->instrument) ?: null,
                'location'      => McpData::plain($row->location) ?: null,
                'magnification' => $row->magnification ? (int) $row->magnification : null,
                'visibility'    => McpData::visibility($row->visibility),
                'class'         => $row->observer_class,
                'drawing'       => (bool) $row->has_drawing ?: null,
                'description'   => McpData::text($row->description, 200) ?: null,
            ], fn ($value) => $value !== null))->values()->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from'          => $schema->string()->format('date')->description('First observation date (Y-m-d).'),
            'to'            => $schema->string()->format('date')->description('Last observation date (Y-m-d).'),
            'object'        => $schema->string()->description('Only observations of this object ("M 51").'),
            'kind'          => $schema->string()->enum(self::KINDS)->description('Only observations of this kind of target; solarsystem is asteroids.'),
            'constellation' => $schema->string()->description('Only deep-sky objects in this constellation: its three-letter code ("CYG") or English name.'),
            'instrument_id' => $schema->integer()->description('Only observations with this instrument (ids from get-my-profile).'),
            'location_id'   => $schema->integer()->description('Only observations from this location.'),
            'seen'          => $schema->boolean()->description('true: only objects that were seen; false: only those marked not seen.'),
            'with_drawing'  => $schema->boolean()->description('Only observations with a drawing.'),
            'limit'         => $schema->integer()->min(1)->max(self::MAX_LIMIT)->description('At most this many (default 25).'),
            'offset'        => $schema->integer()->min(0)->description('Skip this many, to page through the results.'),
        ];
    }
}
