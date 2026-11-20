<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\DeepskyObject;
use App\Services\AlsoSeenObjects;
use App\Services\ContrastReserveCalculator;
use App\Services\DoubleStarResolvability;
use App\Services\DreyerDescription;
use App\Services\ObservingListItemNameResolver;
use App\Services\StruveSigmaNames;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Everything about one deep-sky object: type, position, magnitude, size, alternative names, its description in plain words, how often it was observed in DeepskyLog, the observer\'s own observations of it, how well the observer\'s telescope shows it from their location (contrast reserve and best magnification, or for a double star whether it is split), and the objects observers who saw it also saw. Uses the standard telescope and location unless other ids are given.')]
#[IsReadOnly]
class GetObject extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'instrument_id' => ['nullable', 'integer'],
            'location_id'   => ['nullable', 'integer'],
        ]);
        $user      = $request->user();
        $name      = trim($validated['name']);
        $canonical = app(ObservingListItemNameResolver::class)->resolve(StruveSigmaNames::designation($name) ?? $name);
        $object    = $canonical !== null ? DeepskyObject::query()->find($canonical) : null;
        if ($object === null) {
            return Response::error("No deep-sky object called \"{$name}\" in DeepskyLog. Try search-objects; comets, planets and lunar features are not covered by this tool.");
        }

        $type          = DB::table('deepskytypes')->where('code', $object->type)->value('name');
        $constellation = DB::table('constellations')->where('id', $object->con)->value('name');
        $isDouble      = $object->type === 'DS';
        $magnitude     = is_numeric($object->mag) && $object->mag > 0 && $object->mag < 99 ? round((float) $object->mag, 2) : null;

        $result = array_filter([
            'name'                  => $object->name,
            'url'                   => McpData::objectUrl($object->slug),
            'type'                  => $type ?: $object->type,
            'type_code'             => $object->type,
            'constellation'         => $constellation,
            'ra'                    => $object->formattedRa(),
            'dec'                   => $object->formattedDec(),
            'magnitude'             => $magnitude,
            'magnitude_is_estimate' => $object->hasEstimatedMagnitude() ? true : null,
            'companion_magnitude'   => $isDouble   && is_numeric($object->subr) && $object->subr                      < 99 ? round((float) $object->subr, 2) : null,
            'surface_brightness'    => ! $isDouble && is_numeric($object->subr) && $object->subr > 0 && $object->subr < 99 ? round((float) $object->subr, 2) : null,
            'separation_arcsec'     => $isDouble   && is_numeric($object->diam1) && $object->diam1 > 0 ? round((float) $object->diam1, 1) : null,
            'size_arcmin'           => $isDouble ? null : McpData::size($object->diam1, $object->diam2),
            'position_angle'        => is_numeric($object->pa) && $object->pa >= 0 && $object->pa < 360 ? (int) $object->pa : null,
            'alternative_names'     => DB::table('objectnames')->where('objectname', $object->name)
                ->where('altname', '!=', $object->name)->orderBy('altname')->limit(30)->pluck('altname')->unique()->values()->all() ?: null,
            'description'                => McpData::plain($object->description) ?: null,
            'description_in_plain_words' => DreyerDescription::plain($object->description),
            'observations_in_deepskylog' => (int) (DB::table('object_observation_stats')->where('object_name', $object->name)->value('observations') ?? 0),
        ], fn ($value) => $value !== null);

        $result['my_observations'] = $this->myObservations($user, $object->name);

        $instrument = McpData::instrument($user, $validated['instrument_id'] ?? null);
        $location   = McpData::location($user, $validated['location_id'] ?? null);
        if (($validated['instrument_id'] ?? null) && ! $instrument) {
            return Response::error('That instrument is not one of the observer\'s. get-my-profile lists their instruments.');
        }
        if (($validated['location_id'] ?? null) && ! $location) {
            return Response::error('That location is not one of the observer\'s. get-my-profile lists their locations.');
        }
        $result['visibility'] = $instrument && $location
            ? $this->visibility($object, $instrument, $location, $user)
            : 'The observer has no standard telescope or location; pass instrument_id and location_id.';

        $alsoSeen = AlsoSeenObjects::forObject($object->name, 8);
        if ($alsoSeen !== []) {
            $result['observers_who_saw_this_also_saw'] = array_map(fn (array $row) => [
                'name' => $row['name'], 'type' => $row['type'], 'constellation' => $row['constellation'], 'magnitude' => $row['magnitude'],
            ], $alsoSeen);
        }

        return Response::structured($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function myObservations($user, string $objectName): array
    {
        $rows = McpData::observations($user)
            ->leftJoin('instruments as i', 'i.id', '=', 'observations.instrument_id')
            ->where('observations.target_type', 'deepsky')
            ->where('observations.target_name', $objectName)
            ->orderByDesc('observations.observed_on')
            ->get(['observations.id', 'observations.observed_on', 'observations.has_drawing', 'details.visibility', 'i.name as instrument']);

        return [
            'count'      => $rows->count(),
            'seen'       => $rows->filter(fn ($row) => (int) $row->visibility !== 7)->count(),
            'drawings'   => $rows->where('has_drawing', true)->count(),
            'first_date' => $rows->last()?->observed_on,
            'last_date'  => $rows->first()?->observed_on,
            'latest'     => $rows->take(5)->map(fn ($row) => [
                'observation_id' => $row->id,
                'date'           => $row->observed_on,
                'instrument'     => McpData::plain($row->instrument) ?: null,
                'visibility'     => McpData::visibility($row->visibility),
                'drawing'        => (bool) $row->has_drawing,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function visibility(DeepskyObject $object, $instrument, $location, $user): array
    {
        $lensFactor = ContrastReserveCalculator::lensFactor($instrument->lens_id);
        $altitude   = ContrastReserveCalculator::highestAltitude($object->declDegrees(), $location);
        $base       = [
            'instrument'           => McpData::plain($instrument->fullName()),
            'location'             => McpData::plain($location->name),
            'highest_altitude_deg' => $altitude !== null ? (int) round($altitude) : null,
        ];

        if (! ContrastReserveCalculator::appliesTo($object->type)) {
            $split = DoubleStarResolvability::forObject($object, $instrument, $location, $user, $lensFactor);

            return $split === null ? $base + ['note' => 'No separation known for this double star.'] : $base + [
                'verdict'               => $split['verdict'] ?? null,
                'meaning'               => isset($split['verdict']) ? DoubleStarResolvability::label($split['verdict']) : null,
                'magnification'         => $split['magnification']         ?? null,
                'needs_steady_seeing'   => $split['steady_seeing']         ?? null,
                'minimum_magnification' => $split['minimum_magnification'] ?? null,
                'magnification_too_low' => $split['magnification_too_low'] ?? false,
                'never_rises'           => $split['not_visible']           ?? false,
            ];
        }

        $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);
        $calc   = ContrastReserveCalculator::calculate($target, $instrument, $location, $user, $lensFactor, altitude: $altitude);

        return $base + [
            'sky_sqm'            => $calc['sqm'],
            'contrast_reserve'   => $calc['contrast_reserve'] !== null ? round($calc['contrast_reserve'], 2) : null,
            'category'           => $calc['category'],
            'best_magnification' => $calc['magnification'],
            'best_eyepieces'     => array_map(fn ($eyepiece) => McpData::plain($eyepiece->fullName()), $calc['eyepieces']) ?: null,
            'note'               => $calc['category'] === 'not_visible' && $altitude !== null && $altitude <= 0 ? 'The object never rises at this location.' : null,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name'          => $schema->string()->description('The object, in any common spelling ("M 31", "m31", "Andromeda Galaxy").')->required(),
            'instrument_id' => $schema->integer()->description('One of the observer\'s instruments (default: the standard telescope).'),
            'location_id'   => $schema->integer()->description('One of the observer\'s locations (default: the standard location).'),
        ];
    }
}
