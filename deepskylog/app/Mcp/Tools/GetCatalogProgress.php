<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Services\ObjectSources;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('How far the observer got with a catalogue ("M", "NGC", "Caldwell", "H400", "Arp", "Hickson", ...) or one of their observing lists: how many objects they have seen and drawn, and which objects they still have not seen (or not drawn), in catalogue order.')]
#[IsReadOnly]
class GetCatalogProgress extends Tool
{
    public const MAX_LISTED = 300;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'catalog' => ['nullable', 'string', 'max:40', 'required_without:list_id'],
            'list_id' => ['nullable', 'integer', 'required_without:catalog'],
            'missing' => ['nullable', 'string', 'in:unseen,undrawn'],
            'limit'   => ['nullable', 'integer', 'min:0', 'max:' . self::MAX_LISTED],
        ]);
        $user   = $request->user();
        $key    = ! empty($validated['list_id']) ? 'list:' . (int) $validated['list_id'] : 'catalog:' . mb_strtolower(trim($validated['catalog']));
        $source = ObjectSources::find($key, $user);
        if ($source === null) {
            return Response::error(! empty($validated['list_id'])
                ? 'That observing list is not one of the observer\'s or one they subscribed to.'
                : 'DeepskyLog has no catalogue called "' . $validated['catalog'] . '". Catalogue names are the prefixes of object names, such as M, NGC, IC, Caldwell, H400, Arp, Hickson, Sh 2, Abell.');
        }

        $objects = ObjectSources::objects($source);
        $names   = $objects->pluck('object_name')->filter()->unique()->values();
        $base    = fn ($chunk) => McpData::seen(McpData::observations($user))
            ->where('observations.target_type', 'deepsky')
            ->whereIn('observations.target_name', $chunk->all());
        $seen = $names->chunk(1000)->flatMap(fn ($chunk) => $base($chunk)->distinct()->pluck('observations.target_name'))
            ->map(fn ($name) => mb_strtolower($name))->flip();
        $drawn = $names->chunk(1000)->flatMap(fn ($chunk) => $base($chunk)->where('observations.has_drawing', true)->distinct()->pluck('observations.target_name'))
            ->map(fn ($name) => mb_strtolower($name))->flip();

        $missing = $validated['missing'] ?? 'unseen';
        $limit   = (int) ($validated['limit'] ?? 100);
        $done    = $missing === 'undrawn' ? $drawn : $seen;
        $todo    = $objects->reject(fn (array $object) => $done->has(mb_strtolower($object['object_name'])))->unique('object_name')->values();

        $details = $todo->take($limit)->pluck('object_name')->chunk(1000)->flatMap(fn ($chunk) => DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->whereIn('o.name', $chunk->all())
            ->get(['o.name', 'o.mag', 'o.type', 'o.con', 't.name as type_name']))
            ->keyBy(fn ($row) => mb_strtolower($row->name));

        return Response::structured([
            'source'              => $source['label'],
            'objects'             => $names->count(),
            'seen'                => $names->filter(fn ($name) => $seen->has(mb_strtolower($name)))->count(),
            'drawn'               => $names->filter(fn ($name) => $drawn->has(mb_strtolower($name)))->count(),
            'still_' . $missing   => $todo->count(),
            'listed'              => min($limit, $todo->count()),
            $missing . '_objects' => $todo->take($limit)->map(function (array $object) use ($details) {
                $row = $details[mb_strtolower($object['object_name'])] ?? null;

                return array_filter([
                    'name'          => $object['object_name'],
                    'designation'   => $object['display_name'] !== $object['object_name'] ? $object['display_name'] : null,
                    'type'          => $row ? ($row->type_name ?: $row->type) : null,
                    'constellation' => $row?->con,
                    'magnitude'     => $row && is_numeric($row->mag) && $row->mag > 0 && $row->mag < 99 ? round((float) $row->mag, 1) : null,
                ], fn ($value) => $value !== null);
            })->values()->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'catalog' => $schema->string()->description('The catalogue ("M", "NGC", "Caldwell", "H400", "Arp", "Hickson").'),
            'list_id' => $schema->integer()->description('Or one of the observer\'s observing lists (from list-my-observing-lists).'),
            'missing' => $schema->string()->enum(['unseen', 'undrawn'])->description('List the objects not seen yet (default) or not drawn yet.'),
            'limit'   => $schema->integer()->min(0)->max(self::MAX_LISTED)->description('List at most this many of them (default 100).'),
        ];
    }
}
