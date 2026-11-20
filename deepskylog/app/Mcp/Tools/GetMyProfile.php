<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The observer who owns the access token: name, country, language, standard telescope and location, and all their locations, instruments, eyepieces, filters and lenses with their ids (the ids other tools take). Call this first.')]
#[IsReadOnly]
class GetMyProfile extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        $locations = $user->locations()->orderByDesc('active')->orderBy('name')->get()
            ->map(fn (Location $location) => McpData::locationRow($location, $user))->values();
        $instruments = $user->instruments()->with(['instrument_make', 'instrument_type', 'mount_type'])->orderByDesc('active')->orderBy('name')->get()
            ->map(fn (Instrument $instrument) => McpData::instrumentRow($instrument, $user))->values();
        $eyepieces = $user->eyepieces()->with('eyepiece_make')->where('active', true)->orderByDesc('focal_length_mm')->get()
            ->map(fn (Eyepiece $eyepiece) => [
                'id'                  => $eyepiece->id,
                'name'                => McpData::plain($eyepiece->fullName()),
                'focal_length_mm'     => $eyepiece->focal_length_mm     !== null ? (float) $eyepiece->focal_length_mm : null,
                'max_focal_length_mm' => $eyepiece->max_focal_length_mm !== null ? (float) $eyepiece->max_focal_length_mm : null,
                'apparent_field_deg'  => $eyepiece->apparentFOV ?: null,
            ])->values();
        $filters = $user->filters()->where('active', true)->orderBy('name')->get()
            ->map(fn (Filter $filter) => ['id' => $filter->id, 'name' => McpData::plain($filter->fullName())])->values();
        $lenses = $user->lenses()->where('active', true)->orderBy('name')->get()
            ->map(fn (Lens $lens) => ['id' => $lens->id, 'name' => McpData::plain($lens->fullName()), 'factor' => $lens->factor !== null ? (float) $lens->factor : null])->values();

        return Response::structured([
            'username'               => $user->username,
            'name'                   => McpData::plain($user->name),
            'url'                    => route('observer.show', $user->slug),
            'country'                => $user->country,
            'language'               => $user->language,
            'uses_inches'            => (bool) $user->showInches,
            'standard_location_id'   => $user->stdlocation ?: null,
            'standard_instrument_id' => $user->stdtelescope ?: null,
            'locations'              => $locations,
            'instruments'            => $instruments,
            'active_eyepieces'       => $eyepieces,
            'active_filters'         => $filters,
            'active_lenses'          => $lenses,
        ]);
    }
}
