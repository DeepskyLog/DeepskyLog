<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\ObservingList;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * A tool that writes: it needs a token with the 'write' permission (only
 * offered to such a token, and checked again when called).
 */
#[Description('Change one of the observer\'s own observing lists: its description (plain text, lines kept; an empty text removes it) and/or whether it is public. Only the owner may change a list. Making a public list private ends the subscriptions of other observers and their "working on" marks. Show the observer what will change and ask before changing it; list-my-observing-lists with own_only, public and has_description finds the lists.')]
class UpdateObservingList extends Tool
{
    /** as the edit form of the list page */
    public const MAX_LENGTH = 5000;

    public function shouldRegister(Request $request): bool
    {
        return McpData::canWrite($request);
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if (! McpData::canWrite($request)) {
            return Response::error('This access token may only read. Make a token with the "write" permission on /user/api-tokens to change lists.');
        }
        $validated = $request->validate([
            'id'           => ['required', 'integer'],
            'description'  => ['nullable', 'string', 'max:' . self::MAX_LENGTH],
            'public'      => ['nullable', 'boolean'],
        ]);
        $user = $request->user();
        $list = ObservingList::query()->find($validated['id']);
        if ($list === null || ! $user->can('update', $list)) {
            return Response::error('No observing list with that id that you own. list-my-observing-lists with own_only gives the ids of yours.');
        }
        $changeDescription = array_key_exists('description', $request->all());
        $changePublic      = isset($validated['public']);
        if (! $changeDescription && ! $changePublic) {
            return Response::error('Give a description, public, or both.');
        }

        $previous = ['description' => McpData::listDescription($list->description), 'public' => (bool) $list->public];
        $changes  = [];
        if ($changeDescription) {
            $description            = trim(strip_tags(str_replace("\r\n", "\n", (string) ($validated['description'] ?? ''))));
            $changes['description'] = $description !== '' ? $description : null;
        }
        $subscribersLost = 0;
        if ($changePublic) {
            $changes['public'] = (bool) $validated['public'];
            if ($previous['public'] && ! $changes['public']) {
                $subscribersLost = $list->subscriptions()->count();
            }
        }
        $list->update($changes);

        return Response::structured(array_filter([
            'id'                  => $list->id,
            'name'                => $list->name_decoded,
            'url'                 => route('observing-list.show', $list),
            'public'              => (bool) $list->public,
            'description'         => McpData::listDescription($list->description),
            'previous'            => $previous,
            'subscriptions_ended' => $subscribersLost ?: null,
        ], fn ($value) => $value !== null));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id'           => $schema->integer()->description('The id of the list (from list-my-observing-lists).')->required(),
            'description'  => $schema->string()->description('The new description (plain text, at most ' . self::MAX_LENGTH . ' characters); empty to remove it. Leave out to keep it.'),
            'public'      => $schema->boolean()->description('true: make the list public; false: make it private. Leave out to keep it.'),
        ];
    }
}
