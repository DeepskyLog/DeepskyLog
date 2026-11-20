<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Services\ObservingListItemNameResolver;
use App\Services\StruveSigmaNames;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Find objects in DeepskyLog by name: catalogue designations in any common spelling ("M31", "NGC891", "Hickson 92", "Sigma 2816"), common names ("Andromeda Galaxy", "Veil"), comets, planets, asteroids and lunar features. Returns the canonical names the other tools take.')]
#[IsReadOnly]
class SearchObjects extends Tool
{
    public const MAX_RESULTS = 50;

    /** The search_index.source_table of each kind. */
    private const KINDS = [
        'deepsky'       => 'objects',
        'comet'         => 'cometobjects',
        'planet'        => 'planets',
        'moon'          => 'moons',
        'lunar_feature' => 'lunar_features',
        'asteroid'      => 'asteroids',
    ];

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'kind'  => ['nullable', 'string', 'in:' . implode(',', array_keys(self::KINDS))],
            'limit' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_RESULTS],
        ]);
        $query = trim($validated['query']);
        $query = StruveSigmaNames::designation($query) ?? $query;
        $limit = (int) ($validated['limit'] ?? 10);
        $table = isset($validated['kind']) ? self::KINDS[$validated['kind']] : null;

        $base = fn () => DB::table('search_index')
            ->when($table !== null, fn ($q) => $q->where('source_table', $table))
            ->select(['name', 'source_table', 'source_pk', 'source_type']);

        // the exact object first ("m31" is "M 31"), then names starting with the query, then names holding it
        $rows  = collect();
        $exact = app(ObservingListItemNameResolver::class)->resolve($query);
        if ($exact !== null) {
            $rows = $rows->concat($base()->where('name', $exact)->limit(5)->get());
        }
        $escaped = addcslashes($query, '%_\\');
        $rows    = $rows->concat($base()->where('name', 'like', $escaped . '%')->orderByRaw('LENGTH(name)')->orderBy('name')->limit($limit * 3)->get());
        if ($rows->unique(fn ($row) => $row->source_table . '|' . $row->source_pk)->count() < $limit && mb_strlen($query) >= 3) {
            $rows = $rows->concat($base()->where('name', 'like', '%' . $escaped . '%')->orderByRaw('LENGTH(name)')->limit($limit * 3)->get());
        }
        $rows = $rows->unique(fn ($row) => $row->source_table . '|' . $row->source_pk)->take($limit)->values();

        $objects = DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->whereIn('o.slug', $rows->where('source_table', 'objects')->pluck('source_pk')->all())
            ->get(['o.name', 'o.slug', 'o.type', 'o.mag', 'o.diam1', 'o.diam2', 't.name as type_name', 'c.name as constellation'])
            ->keyBy('slug');

        $results = $rows->map(function ($row) use ($objects) {
            $kind = array_search($row->source_table, self::KINDS, true) ?: $row->source_table;
            if ($row->source_table !== 'objects') {
                return ['name' => $row->name, 'kind' => $kind, 'type' => $row->source_type ?: null];
            }
            $object = $objects[$row->source_pk] ?? null;
            if ($object === null) {
                return null;
            }

            return array_filter([
                'name'          => $object->name,
                'matched_name'  => Str::lower($row->name) !== Str::lower($object->name) ? $row->name : null,
                'kind'          => 'deepsky',
                'type'          => $object->type_name ?: $object->type,
                'constellation' => $object->constellation,
                'magnitude'     => is_numeric($object->mag) && $object->mag > 0 && $object->mag < 99 ? round((float) $object->mag, 1) : null,
                // a double star keeps its separation (arcseconds) in diam1
                'separation_arcsec' => $object->type === 'DS' && is_numeric($object->diam1) && $object->diam1 > 0 ? round((float) $object->diam1, 1) : null,
                'size_arcmin'       => $object->type !== 'DS' ? McpData::size($object->diam1, $object->diam2) : null,
                'url'               => McpData::objectUrl($object->slug),
            ], fn ($value) => $value !== null);
        })->filter()->values();

        return Response::structured([
            'query'   => $query,
            'count'   => $results->count(),
            'results' => $results->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The name or designation to look for.')->required(),
            'kind'  => $schema->string()->enum(array_keys(self::KINDS))->description('Only objects of this kind.'),
            'limit' => $schema->integer()->min(1)->max(self::MAX_RESULTS)->description('At most this many results (default 10).'),
        ];
    }
}
