<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Services\TonightsBest;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('DeepskyLog\'s "Tonight\'s best": the objects worth observing in a night with the observer\'s standard telescope from one of their locations, well placed in the dark hours and spread over them, mostly objects the observer has not seen yet, with the planets, bright comets and lunar features at the terminator. Each object has its best moment (local time), altitude and contrast reserve. The dark hours, Moon and seeing forecast of the night come with it. create-observing-list saves the result as a list.')]
#[IsReadOnly]
class GetTonightsBest extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'night'        => ['nullable', 'date_format:Y-m-d'],
            'location_id'  => ['nullable', 'integer'],
            'period'       => ['nullable', 'string', 'in:' . implode(',', array_diff(TonightsBest::PERIODS, ['custom']))],
            'count'        => ['nullable', 'integer', 'min:' . TonightsBest::MIN_COUNT, 'max:' . TonightsBest::MAX_COUNT],
            'difficulty'   => ['nullable', 'string', 'in:' . implode(',', TonightsBest::DIFFICULTIES)],
            'seen'         => ['nullable', 'string', 'in:' . implode(',', TonightsBest::SEEN_OPTIONS)],
            'catalog'      => ['nullable', 'string', 'max:40'],
            'only_catalog' => ['nullable', 'boolean'],
        ]);
        $user     = $request->user();
        $location = McpData::location($user, $validated['location_id'] ?? null);
        if ($location === null) {
            return Response::error(($validated['location_id'] ?? null)
                ? 'That location is not one of the observer\'s. get-my-profile lists their locations.'
                : 'The observer has no standard location; pass location_id.');
        }

        $options = array_filter([
            'night'        => $validated['night']      ?? null,
            'period'       => $validated['period']     ?? null,
            'count'        => $validated['count']      ?? null,
            'difficulty'   => $validated['difficulty'] ?? null,
            'seen'         => $validated['seen']       ?? null,
            'sources'      => ! empty($validated['catalog']) ? ['catalog:' . mb_strtolower(trim($validated['catalog']))] : null,
            'sources_only' => ! empty($validated['catalog']) && ! empty($validated['only_catalog']),
        ], fn ($value) => $value !== null);

        $result = TonightsBest::for($user, $options, $location);
        if ($result === null) {
            return Response::error('The location has no valid coordinates.');
        }

        return Response::structured(array_filter([
            'night'             => $result['night'],
            'location'          => McpData::plain($location->name),
            'instrument'        => $result['instrument'] ? McpData::plain($result['instrument']) : null,
            'mode'              => $result['mode'],
            'dark_hours'        => $result['dark'],
            'moon'              => $result['moon'] ? ['illuminated_percent' => (int) round($result['moon']['fraction'] * 100), 'up_from' => $result['moon']['from'], 'up_until' => $result['moon']['until']] : null,
            'seeing'            => $result['seeing'],
            'still_calculating' => $result['pending'] ? 'Some contrast reserves are still being calculated; asking again in a few minutes gives a more complete list.' : null,
            'objects'           => array_map(fn (array $object) => array_filter([
                'name'             => $object['list_name'] ?? $object['name'],
                'kind'             => $object['kind'],
                'type'             => $object['type'],
                'constellation'    => $object['constellation'] ?? null,
                'magnitude'        => $object['magnitude']     ?? null,
                'best_time'        => $object['time']          ?? null,
                'altitude_deg'     => $object['altitude']      ?? null,
                'contrast_reserve' => isset($object['contrast_reserve']) ? round((float) $object['contrast_reserve'], 2) : null,
                'difficulty'       => $object['category'] ?? null,
                'seen_before'      => match ($object['status'] ?? null) {
                    'seen'       => 'seen with this telescope',
                    'seen_other' => 'seen with another instrument',
                    'new'        => 'not seen yet',
                    default      => null,
                },
                'seeing_note' => $object['seeing'] ?? null,
            ], fn ($value) => $value !== null), $result['objects']),
        ], fn ($value) => $value !== null));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'night'        => $schema->string()->format('date')->description('The evening of the night (Y-m-d); default tonight.'),
            'location_id'  => $schema->integer()->description('One of the observer\'s locations (default: the standard location).'),
            'period'       => $schema->string()->enum(array_values(array_diff(TonightsBest::PERIODS, ['custom'])))->description('Part of the night (default: the whole night).'),
            'count'        => $schema->integer()->min(TonightsBest::MIN_COUNT)->max(TonightsBest::MAX_COUNT)->description('Number of deep-sky objects (default ' . TonightsBest::DEEPSKY_COUNT . ').'),
            'difficulty'   => $schema->string()->enum(TonightsBest::DIFFICULTIES)->description('easy, challenging or mixed (default).'),
            'seen'         => $schema->string()->enum(TonightsBest::SEEN_OPTIONS)->description('none: only objects not seen yet; few (default): mostly new ones; any.'),
            'catalog'      => $schema->string()->description('Put the objects of this catalogue first ("M", "NGC", "Caldwell", "H400", "Arp", "Hickson").'),
            'only_catalog' => $schema->boolean()->description('Only objects of that catalogue.'),
        ];
    }
}
