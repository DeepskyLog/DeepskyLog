<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\ObservingList;
use App\Services\ObservingListItemNameResolver;
use App\Services\StruveSigmaNames;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * A tool that writes: it needs a token with the 'write' permission
 * (only offered to such a token, and checked again when called).
 */
#[Description('Save a new observing list for the observer with the given objects, in this order (for instance the result of get-tonights-best, or the unseen objects of get-catalog-progress). Names are resolved as search-objects does; names DeepskyLog does not know are reported and left out. The list is private unless public is true. Ask the observer before creating a list.')]
class CreateObservingList extends Tool
{
    public const MAX_OBJECTS = 500;

    public function shouldRegister(Request $request): bool
    {
        return self::allowed($request);
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if (! self::allowed($request)) {
            return Response::error('This access token may only read. Make a token with the "write" permission on /user/api-tokens to create lists.');
        }
        $validated = $request->validate([
            'name'        => ['required', 'string', 'min:1', 'max:100'],
            'objects'     => ['required', 'array', 'min:1', 'max:' . self::MAX_OBJECTS],
            'objects.*'   => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'public'      => ['nullable', 'boolean'],
        ]);
        $user = $request->user();
        if (! $user->can('create', ObservingList::class)) {
            return Response::error('The observer may not create observing lists while working in their current role.');
        }

        $resolver = app(ObservingListItemNameResolver::class);
        $resolved = [];
        $unknown  = [];
        foreach ($validated['objects'] as $name) {
            $name      = trim($name);
            $canonical = $resolver->resolve(StruveSigmaNames::designation($name) ?? $name);
            if ($canonical === null) {
                $unknown[] = $name;
            } else {
                $resolved[] = $canonical;
            }
        }
        $resolved = array_values(array_unique($resolved));
        if ($resolved === []) {
            return Response::error('None of the objects is known to DeepskyLog: ' . implode(', ', $unknown) . '. Use search-objects to find their names.');
        }

        $description = trim(strip_tags((string) ($validated['description'] ?? '')));
        $list        = ObservingList::createWithObjects(
            $user,
            trim(strip_tags($validated['name'])) ?: 'Observing list',
            $resolved,
            $description !== '' ? $description : null,
            (bool) ($validated['public'] ?? false),
        );

        return Response::structured(array_filter([
            'id'                     => $list->id,
            'name'                   => $list->name_decoded,
            'url'                    => route('observing-list.show', $list),
            'public'                 => (bool) $list->public,
            'objects_added'          => count($resolved),
            'unknown_names_left_out' => $unknown ?: null,
        ], fn ($value) => $value !== null));
    }

    private static function allowed(Request $request): bool
    {
        return McpData::canWrite($request);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name'        => $schema->string()->description('The name of the list (made unique among the observer\'s lists).')->required(),
            'objects'     => $schema->array()->items($schema->string())->description('The object names, in observing order.')->required(),
            'description' => $schema->string()->description('What the list is for (plain text).'),
            'public'      => $schema->boolean()->description('Make the list visible to everybody (default false).'),
        ];
    }
}
