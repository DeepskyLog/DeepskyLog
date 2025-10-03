<?php

namespace App\Traits;

use App\Models\ObjectNameTranslation;

trait ResolvesLocalizedNames
{
    /**
     * Try to resolve a user-provided term in the given locale to one or more
     * canonical object names used by the application (eg. 'Pluton' -> 'Pluto').
     *
     * Returns an array of canonical names; if nothing found, returns an empty array.
     */
    protected function resolveLocalizedToCanonical(string $term, ?string $locale = null): array
    {
        $query = ObjectNameTranslation::query()
            ->whereRaw('LOWER(`name`) = ?', [mb_strtolower($term)]);

        if ($locale) {
            $query->where('locale', $locale);
        }

        $results = $query->get(['objectname'])->pluck('objectname')->unique()->values()->toArray();

        return $results;
    }
}
