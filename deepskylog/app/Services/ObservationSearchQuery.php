<?php

namespace App\Services;

use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Queries for an observation search (a search term plus the advanced search
 * filters), matching the same observations as the observation search table.
 *
 * Without a search term or filters there are 188k+ observations, so callers
 * must let the database do the work: never load them all into PHP, and never
 * bind their ids as placeholders one by one (MySQL allows at most 65,535 per
 * statement).
 */
class ObservationSearchQuery
{
    /**
     * The advanced search filters of a request: its query parameters other
     * than the search term, without the empty ones.
     *
     * @param  array<int, string>  $except  further parameters that are no filter
     * @return array<string, mixed>
     */
    public static function filtersFrom(Request $request, array $except = []): array
    {
        return array_filter($request->except(array_merge(['_token', 'page', 'q'], $except)), function ($value): bool {
            if (is_array($value)) {
                return collect($value)
                    ->flatten()
                    ->contains(fn($item): bool => trim((string) $item) !== '');
            }

            return trim((string) $value) !== '';
        });
    }

    /**
     * The matching observations, one row per observation.
     */
    public function observations(string $search, array $filters = []): EloquentBuilder
    {
        // The search table only needs its (left) joins for the advanced
        // filters; they never remove observations, so without filters skip
        // them and apply the table's search term condition directly. The
        // joins make the query roughly ten times slower.
        if ($filters === []) {
            $observations = Observation::query();
            if ($search !== '') {
                ObservationSearchTable::whereTargetNameMatches($observations, $search);
            }

            return $observations;
        }

        // The joins can repeat an observation, so select it by id.
        return Observation::query()->whereIn(
            'observations.id',
            $this->searchTable($search, $filters)->datasource()->select('observations.id')->toBase()
        );
    }

    /**
     * The distinct targets of the matching observations.
     *
     * Each row holds `source_table` (the observation's target table, lower
     * case) and `name` (its lower-case target name), which is how the targets
     * are matched against `search_index`.
     */
    public function targets(string $search, array $filters = []): Builder
    {
        return $this->observations($search, $filters)
            ->select([
                DB::raw("LOWER(COALESCE(observations.target_table, 'objects')) as source_table"),
                DB::raw('LOWER(TRIM(COALESCE(observations.target_name, observations.target_key))) as name'),
            ])
            ->whereRaw("TRIM(COALESCE(observations.target_name, observations.target_key, '')) <> ''")
            ->distinct()
            ->toBase();
    }

    private function searchTable(string $search, array $filters): ObservationSearchTable
    {
        $component = new ObservationSearchTable();

        if (method_exists($component, 'boot')) {
            try {
                $component->boot();
            } catch (\Throwable $_) {
            }
        }

        try {
            $component->mount($search, $filters);
        } catch (\Throwable $_) {
            $component->objectName = $search;
        }

        return $component;
    }
}
