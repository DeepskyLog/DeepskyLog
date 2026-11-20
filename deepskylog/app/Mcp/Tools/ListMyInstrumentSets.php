<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Lens;
use App\Models\Location;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('The observer\'s instrument sets: named kits of equipment used together ("Travel kit", "Big Dob at home") with their instruments, eyepieces, lenses, filters and locations, and which set is the standard one. With an id, one set with the magnification, exit pupil and true field of every telescope of the set with each of its eyepieces, without and with each of its lenses.')]
#[IsReadOnly]
class ListMyInstrumentSets extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'id'              => ['nullable', 'integer'],
            'include_retired' => ['nullable', 'boolean'],
        ]);
        $user  = $request->user();
        $query = InstrumentSet::query()->where('user_id', $user->id)
            ->with(['instruments.instrument_make', 'instruments.instrument_type', 'instruments.mount_type', 'eyepieces.eyepiece_make', 'lenses', 'filters', 'locations']);

        if (! empty($validated['id'])) {
            $set = $query->find($validated['id']);
            if ($set === null) {
                return Response::error('No instrument set of yours with that id. list-my-instrument-sets without an id gives the ids.');
            }

            return Response::structured($this->row($set, $user, true));
        }

        if (empty($validated['include_retired'])) {
            $query->where('active', true);
        }
        $sets = $query->orderByDesc('active')->orderBy('name')->get();

        return Response::structured([
            'standard_instrument_set_id' => $user->stdinstrumentset ?: null,
            'count'                      => $sets->count(),
            'instrument_sets'            => $sets->map(fn (InstrumentSet $set) => $this->row($set, $user, false))->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(InstrumentSet $set, User $user, bool $withMagnifications): array
    {
        $eyepieces = $set->eyepieces->sortByDesc(fn (Eyepiece $eyepiece) => (float) $eyepiece->focal_length_mm)->values();

        $row = [
            'id'          => $set->id,
            'name'        => McpData::plain($set->name),
            'url'         => $user->slug && $set->slug ? url('/instrumentset/' . $user->slug . '/' . $set->slug) : null,
            'standard'    => (int) $user->stdinstrumentset === (int) $set->id,
            'active'      => (bool) $set->active,
            'description' => McpData::text($set->description, 500) ?: null,
            'instruments' => $set->instruments->sortByDesc(fn (Instrument $instrument) => (float) $instrument->aperture_mm)
                ->map(fn (Instrument $instrument) => McpData::instrumentRow($instrument, $user))->values()->all(),
            'eyepieces' => $eyepieces->map(fn (Eyepiece $eyepiece) => [
                'id'                  => $eyepiece->id,
                'name'                => McpData::plain($eyepiece->fullName()),
                'focal_length_mm'     => $eyepiece->focal_length_mm     !== null ? (float) $eyepiece->focal_length_mm : null,
                'max_focal_length_mm' => $eyepiece->max_focal_length_mm !== null ? (float) $eyepiece->max_focal_length_mm : null,
                'apparent_field_deg'  => $eyepiece->apparentFOV ?: null,
            ])->all(),
            'lenses'    => $set->lenses->map(fn (Lens $lens) => ['id' => $lens->id, 'name' => McpData::plain($lens->fullName()), 'factor' => $lens->factor !== null ? (float) $lens->factor : null])->values()->all(),
            'filters'   => $set->filters->map(fn (Filter $filter) => ['id' => $filter->id, 'name' => McpData::plain($filter->fullName())])->values()->all(),
            'locations' => $set->locations->map(fn (Location $location) => ['id' => $location->id, 'name' => McpData::plain($location->name)])->values()->all(),
        ];

        if ($withMagnifications) {
            $row['magnifications'] = $set->instruments->map(fn (Instrument $instrument) => [
                'instrument_id' => $instrument->id,
                'instrument'    => McpData::plain($instrument->fullName()),
                'combinations'  => $this->combinations($instrument, $eyepieces, $set->lenses->all()),
            ])->values()->all();
        }

        return $row;
    }

    /**
     * Every eyepiece of the set in the instrument, without and with each
     * lens of the set; an instrument with a fixed magnification (binoculars,
     * a finder) has only its own.
     *
     * @return list<array<string, mixed>>
     */
    private function combinations(Instrument $instrument, $eyepieces, array $lenses): array
    {
        if ((float) $instrument->fixedMagnification > 0) {
            return [$this->combination($instrument, null, null)];
        }

        $rows = [];
        foreach ([null, ...$lenses] as $lens) {
            foreach ($eyepieces as $eyepiece) {
                $rows[] = $this->combination($instrument, $eyepiece, $lens);
            }
        }

        return array_values(array_filter($rows, fn (array $row) => $row['magnification'] !== null));
    }

    /**
     * @return array<string, mixed>
     */
    private function combination(Instrument $instrument, ?Eyepiece $eyepiece, ?Lens $lens): array
    {
        $magnification = $instrument->magnificationFor($eyepiece, $lens);
        $field         = $instrument->trueFieldOfViewDeg($eyepiece, $lens);
        $aperture      = (float) $instrument->aperture_mm;

        return array_filter([
            'eyepiece'       => $eyepiece ? McpData::plain($eyepiece->fullName()) : null,
            'eyepiece_id'    => $eyepiece?->id,
            'lens'           => $lens ? McpData::plain($lens->fullName()) : null,
            'lens_id'        => $lens?->id,
            'magnification'  => $magnification,
            'exit_pupil_mm'  => $magnification && $aperture > 0 ? round($aperture / $magnification, 1) : null,
            'true_field_deg' => $field !== null ? round($field, 2) : null,
        ], fn ($value) => $value !== null) + ['magnification' => null];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id'              => $schema->integer()->description('The id of one set: gives it with all magnifications of its equipment.'),
            'include_retired' => $schema->boolean()->description('Also the sets the observer no longer uses (not active).'),
        ];
    }
}
