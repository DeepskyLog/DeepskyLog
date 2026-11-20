<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\ObservingList;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('One observing list (the observer\'s own or a public one) with its objects in order: type, constellation, magnitude, the note and planned time of each item, and whether the observer has seen it.')]
#[IsReadOnly]
class GetObservingList extends Tool
{
    public const MAX_ITEMS = 500;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id'          => ['required', 'integer'],
            'only_unseen' => ['nullable', 'boolean'],
        ]);
        $user = $request->user();
        $list = ObservingList::query()->withCount('items')->with('owner:id,username')->find($validated['id']);
        if ($list === null || ! $user->can('view', $list)) {
            return Response::error('No observing list with that id that you may see. list-my-observing-lists gives the ids of yours.');
        }

        $items   = $list->items()->orderBy('sort_order')->orderBy('id')->limit(self::MAX_ITEMS)->get();
        $names   = $items->pluck('object_name')->filter()->unique()->values();
        $objects = $names->chunk(1000)->flatMap(fn ($chunk) => DB::table('objects as o')
            ->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->whereIn('o.name', $chunk->all())
            ->get(['o.name', 'o.mag', 'o.type', 't.name as type_name', 'c.name as constellation']))
            ->keyBy(fn ($row) => mb_strtolower($row->name));
        $seen = $names->chunk(1000)->flatMap(fn ($chunk) => McpData::seen(McpData::observations($user))
            ->whereIn('observations.target_name', $chunk->all())
            ->distinct()->pluck('observations.target_name'))
            ->map(fn ($name) => mb_strtolower($name))->flip();
        $timezone = $list->planned_timezone ?: 'UTC';

        $rows = $items->map(function ($item) use ($objects, $seen, $timezone) {
            $object = $objects[mb_strtolower((string) $item->object_name)] ?? null;

            return array_filter([
                'name'          => $item->object_name,
                'type'          => $object ? ($object->type_name ?: $object->type) : null,
                'constellation' => $object?->constellation,
                'magnitude'     => $object && is_numeric($object->mag) && $object->mag > 0 && $object->mag < 99 ? round((float) $object->mag, 1) : null,
                'seen_by_me'    => $seen->has(mb_strtolower((string) $item->object_name)),
                'planned_time'  => $item->planned_at?->timezone($timezone)->format('Y-m-d H:i'),
                'note'          => McpData::text($item->item_description, 300) ?: null,
            ], fn ($value) => $value !== null);
        });
        if (! empty($validated['only_unseen'])) {
            $rows = $rows->reject(fn (array $row) => $row['seen_by_me']);
        }

        $description = McpData::listDescription($list->description);

        return Response::structured(array_filter([
            'id'               => $list->id,
            'name'             => $list->name_decoded,
            'url'              => route('observing-list.show', $list),
            'owner'            => $list->owner?->username,
            'public'           => (bool) $list->public,
            'description'      => $description,
            'has_description'  => $description !== '',
            'objects_in_list'  => (int) $list->items_count,
            'seen_by_me'       => $items->filter(fn ($item) => $seen->has(mb_strtolower((string) $item->object_name)))->count(),
            'planned_night'    => $list->planned_night?->toDateString(),
            'planned_timezone' => $list->planned_timezone,
            'truncated'        => $list->items_count > self::MAX_ITEMS ? 'Only the first ' . self::MAX_ITEMS . ' objects are given.' : null,
            'objects'          => $rows->values()->all(),
        ], fn ($value) => $value !== null));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id'          => $schema->integer()->description('The id of the list (from list-my-observing-lists).')->required(),
            'only_unseen' => $schema->boolean()->description('Only the objects the observer has not seen yet.'),
        ];
    }
}
