<?php

namespace App\Mcp\Tools;

use App\Mcp\Support\McpData;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Totals of the observer\'s logbook, for all years or one year: observations per kind, different objects seen, drawings, nights, first and last date, observations and different objects per year, the object types and constellations seen most, and the instruments and locations used most.')]
#[IsReadOnly]
class GetMyStatistics extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate(['year' => ['nullable', 'integer', 'min:1900', 'max:2100']]);
        $user      = $request->user();
        $year      = $validated['year'] ?? null;

        $query = fn () => McpData::observations($user)
            ->when($year !== null, fn ($q) => $q->whereBetween('observations.observed_on', ["{$year}-01-01", "{$year}-12-31"]));
        $seen = fn () => McpData::seen($query());

        $totals = $query()->selectRaw('COUNT(*) AS observations, SUM(CASE WHEN observations.has_drawing THEN 1 ELSE 0 END) AS drawings,
            COUNT(DISTINCT observations.observed_on) AS dates, MIN(observations.observed_on) AS first_date, MAX(observations.observed_on) AS last_date')->first();
        if ((int) $totals->observations === 0) {
            return Response::structured(['year' => $year, 'observations' => 0, 'note' => $year ? "No observations in {$year}." : 'No observations yet.']);
        }

        $perKind = $query()->groupBy('observations.target_type')->selectRaw('observations.target_type AS kind, COUNT(*) AS n')
            ->orderByDesc('n')->pluck('n', 'kind')->map(fn ($n) => (int) $n);
        $differentSeen    = (int) $seen()->selectRaw('COUNT(DISTINCT observations.different_object_key) AS n')->value('n');
        $differentDeepsky = (int) $seen()->where('observations.target_type', 'deepsky')->selectRaw('COUNT(DISTINCT observations.different_object_key) AS n')->value('n');

        $perYear = $year !== null ? null : $query()
            ->groupByRaw('SUBSTR(observations.observed_on, 1, 4)')
            ->selectRaw('SUBSTR(observations.observed_on, 1, 4) AS year, COUNT(*) AS observations, COUNT(DISTINCT observations.observed_on) AS dates')
            ->orderBy('year')->get()
            ->map(fn ($row) => ['year' => (int) $row->year, 'observations' => (int) $row->observations, 'dates' => (int) $row->dates])->all();

        $deepsky = fn () => $seen()->where('observations.target_type', 'deepsky')->join('objects as o', 'o.name', '=', 'observations.target_name');
        $types   = $deepsky()->leftJoin('deepskytypes as t', 't.code', '=', 'o.type')
            ->groupBy('o.type', 't.name')->selectRaw('COALESCE(t.name, o.type) AS type, COUNT(DISTINCT o.name) AS objects')
            ->orderByDesc('objects')->limit(10)->get()->map(fn ($row) => ['type' => $row->type, 'objects' => (int) $row->objects])->all();
        $constellations = $deepsky()->leftJoin('constellations as c', 'c.id', '=', 'o.con')
            ->groupBy('o.con', 'c.name')->selectRaw('COALESCE(c.name, o.con) AS constellation, COUNT(DISTINCT o.name) AS objects')
            ->orderByDesc('objects')->limit(10)->get()->map(fn ($row) => ['constellation' => $row->constellation, 'objects' => (int) $row->objects])->all();

        $instruments = $query()->join('instruments as i', 'i.id', '=', 'observations.instrument_id')
            ->groupBy('i.id', 'i.name')->selectRaw('i.id, i.name, COUNT(*) AS n')->orderByDesc('n')->limit(5)->get()
            ->map(fn ($row) => ['id' => $row->id, 'name' => McpData::plain($row->name), 'observations' => (int) $row->n])->all();
        $locations = $query()->join('locations as l', 'l.id', '=', 'observations.location_id')
            ->groupBy('l.id', 'l.name')->selectRaw('l.id, l.name, COUNT(*) AS n')->orderByDesc('n')->limit(5)->get()
            ->map(fn ($row) => ['id' => $row->id, 'name' => McpData::plain($row->name), 'observations' => (int) $row->n])->all();

        return Response::structured(array_filter([
            'year'                           => $year,
            'url'                            => $year !== null ? url("/observers/{$user->slug}/year/{$year}") : route('observer.show', $user->slug),
            'observations'                   => (int) $totals->observations,
            'observations_per_kind'          => $perKind->all(),
            'different_objects_seen'         => $differentSeen,
            'different_deepsky_objects_seen' => $differentDeepsky,
            'drawings'                       => (int) $totals->drawings,
            'observing_dates'                => (int) $totals->dates,
            'first_date'                     => $totals->first_date,
            'last_date'                      => $totals->last_date,
            'per_year'                       => $perYear,
            'deepsky_types_seen_most'        => $types,
            'constellations_seen_most'       => $constellations,
            'instruments_used_most'          => $instruments,
            'locations_used_most'            => $locations,
        ], fn ($value) => $value !== null));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'year' => $schema->integer()->description('Only this year (default: all years).'),
        ];
    }
}
