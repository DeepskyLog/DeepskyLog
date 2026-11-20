<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\Filter;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('One of the observer\'s own observations in full: the whole description, equipment, conditions, the details of a deep-sky or comet observation, and the link to its page.')]
#[IsReadOnly]
class GetObservation extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated   = $request->validate(['id' => ['required', 'integer']]);
        $user        = $request->user();
        $observation = Observation::query()
            ->with(['instrument.instrument_make', 'eyepiece.eyepiece_make'])
            ->where('observer_username', $user->username)
            ->find($validated['id']);
        if ($observation === null) {
            return Response::error('No observation of yours with that id. list-my-observations gives the ids.');
        }

        $location = $observation->location_id ? Location::query()->find($observation->location_id) : null;
        $filter   = $observation->filter_id ? Filter::query()->find($observation->filter_id) : null;
        $lens     = $observation->lens_id ? Lens::query()->find($observation->lens_id) : null;

        $details = null;
        if ($observation->target_type === Observation::TYPE_DEEPSKY) {
            $row     = DB::table('deepsky_observation_details')->where('observation_id', $observation->id)->first();
            $details = $row ? array_filter([
                'visibility'            => McpData::visibility($row->visibility),
                'magnification'         => $row->magnification ? (int) $row->magnification : null,
                'estimated_size_arcsec' => $row->large_diameter > 0 ? trim((float) $row->large_diameter . ($row->small_diameter > 0 ? ' x ' . (float) $row->small_diameter : '')) : null,
                'stellar'               => $row->stellar ? true : null,
                'extended'              => $row->extended ? true : null,
                'resolved'              => $row->resolved ? true : null,
                'mottled'               => $row->mottled ? true : null,
                'unusual_shape'         => $row->unusual_shape ? true : null,
                'nice_field'            => $row->nice_field ? true : null,
            ], fn ($value) => $value !== null) : null;
        } elseif ($observation->target_type === Observation::TYPE_COMET) {
            $row     = DB::table('comet_observation_details')->where('observation_id', $observation->id)->first();
            $details = $row ? array_filter(collect((array) $row)->except('observation_id')->all(), fn ($value) => $value !== null && $value !== '' && $value !== -99.9 && $value !== -1) : null;
        }

        $sessions = DB::table('sessionObservations as so')
            ->join('observation_sessions as s', 's.id', '=', 'so.sessionid')
            ->where('so.observationid', $observation->id)
            ->get(['s.id', 's.name'])
            ->map(fn ($session) => ['id' => $session->id, 'name' => McpData::plain($session->name)])->all();

        return Response::structured(array_filter([
            'id'                 => $observation->id,
            'url'                => $observation->detailUrl($user->slug),
            'object'             => $observation->target_name,
            'kind'               => $observation->target_type,
            'date'               => $observation->observed_on?->format('Y-m-d'),
            'time_ut'            => $observation->observed_time ?: null,
            'instrument'         => $observation->instrument ? McpData::plain($observation->instrument->fullName()) : null,
            'location'           => $location ? McpData::plain($location->name) : null,
            'eyepiece'           => $observation->eyepiece ? McpData::plain($observation->eyepiece->fullName()) : null,
            'filter'             => $filter ? McpData::plain($filter->fullName()) : null,
            'lens'               => $lens ? McpData::plain($lens->fullName()) : null,
            'seeing'             => $observation->seeing ?: null,
            'transparency'       => $observation->transparency ?: null,
            'limiting_magnitude' => $observation->limiting_magnitude    > 0 ? (float) $observation->limiting_magnitude : null,
            'sqm'                => $observation->getRawOriginal('sqm') > 0 ? (float) $observation->getRawOriginal('sqm') : null,
            'class'              => $observation->observer_class,
            'language'           => $observation->language,
            'description'        => McpData::text($observation->description) ?: null,
            'drawing'            => (bool) $observation->has_drawing,
            'details'            => $details ?: null,
            'sessions'           => $sessions ?: null,
        ], fn ($value) => $value !== null));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The id of the observation (from list-my-observations or get-object).')->required(),
        ];
    }
}
