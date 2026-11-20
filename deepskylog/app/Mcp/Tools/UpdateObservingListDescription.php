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
#[Description('Replace the description of one of the observer\'s own observing lists (plain text, lines kept; an empty text removes it). Only the owner may change a list. Show the observer the new text and ask before changing it; list-my-observing-lists with has_description false finds the lists without one.')]
class UpdateObservingListDescription extends Tool
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
            'id'          => ['required', 'integer'],
            'description' => ['present', 'nullable', 'string', 'max:' . self::MAX_LENGTH],
        ]);
        $user = $request->user();
        $list = ObservingList::query()->find($validated['id']);
        if ($list === null || ! $user->can('update', $list)) {
            return Response::error('No observing list with that id that you own. list-my-observing-lists with own_only gives the ids of yours.');
        }

        $previous    = McpData::listDescription($list->description);
        $description = trim(strip_tags(str_replace("\r\n", "\n", (string) ($validated['description'] ?? ''))));
        $list->update(['description' => $description !== '' ? $description : null]);

        return Response::structured([
            'id'                   => $list->id,
            'name'                 => $list->name_decoded,
            'url'                  => route('observing-list.show', $list),
            'public'               => (bool) $list->public,
            'description'          => $description,
            'previous_description' => $previous,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id'          => $schema->integer()->description('The id of the list (from list-my-observing-lists).')->required(),
            'description' => $schema->string()->description('The new description (plain text, at most ' . self::MAX_LENGTH . ' characters); empty to remove it.')->required(),
        ];
    }
}
