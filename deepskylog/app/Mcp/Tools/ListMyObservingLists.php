<?php

namespace App\Mcp\Tools;

use App\Models\ObservingList;
use App\Models\ObservingListPin;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The observer\'s own observing lists and the public lists they subscribed to, the most recently changed first (the lists they are working on and the active list always): ids, number of objects, the night a list is planned for. Search by name. get-observing-list gives the objects of one.')]
#[IsReadOnly]
class ListMyObservingLists extends Tool
{
    public const MAX_LIMIT = 200;

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'limit'  => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_LIMIT],
        ]);
        $user   = $request->user();
        $pinned = array_flip(ObservingListPin::idsFor($user->id));
        $search = trim((string) ($validated['search'] ?? ''));

        $lists = ObservingList::query()
            ->withCount('items')
            ->with('owner:id,username')
            ->where(fn ($q) => $q->where('owner_user_id', $user->id)
                ->orWhereIn('id', $user->subscribedObservingLists()->select('observing_lists.id')))
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->orderByRaw('CASE WHEN id IN (' . implode(',', array_map('intval', [0, (int) $user->active_observing_list_id, ...array_keys($pinned)])) . ') THEN 0 ELSE 1 END')
            ->orderByDesc('updated_at')
            ->limit((int) ($validated['limit'] ?? 50))
            ->get()
            ->map(fn (ObservingList $list) => array_filter([
                'id'            => $list->id,
                'name'          => $list->name_decoded,
                'url'           => route('observing-list.show', $list),
                'own'           => (int) $list->owner_user_id === (int) $user->id,
                'owner'         => (int) $list->owner_user_id === (int) $user->id ? null : $list->owner?->username,
                'public'        => (bool) $list->public,
                'objects'       => (int) $list->items_count,
                'active'        => (int) $user->active_observing_list_id === (int) $list->id ?: null,
                'working_on'    => isset($pinned[$list->id]) ?: null,
                'planned_night' => $list->planned_night?->toDateString(),
                'updated'       => $list->updated_at?->toDateString(),
            ], fn ($value) => $value !== null))
            ->values();

        return Response::structured(['returned' => $lists->count(), 'lists' => $lists->all()]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Only lists whose name holds this text.'),
            'limit'  => $schema->integer()->min(1)->max(self::MAX_LIMIT)->description('At most this many (default 50).'),
        ];
    }
}
