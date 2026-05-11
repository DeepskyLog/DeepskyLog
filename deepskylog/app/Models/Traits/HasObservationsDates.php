<?php

namespace App\Models\Traits;

use App\Models\Observation;
use Carbon\Carbon;

trait HasObservationsDates
{
    /**
     * Maps legacy field names (used by callers) to the column names in the
     * unified observations table.
     */
    private static function mapFieldName(string $legacyFieldName): string
    {
        return match ($legacyFieldName) {
            'instrumentid' => 'instrument_id',
            'locationid' => 'location_id',
            'eyepieceid' => 'eyepiece_id',
            'filterid' => 'filter_id',
            'lensid' => 'lens_id',
            default => $legacyFieldName,
        };
    }

    /**
     * Retrieves the date of the first observation made with the model.
     *
     * @param  string  $fieldName  The legacy field name (e.g. 'instrumentid', 'locationid')
     * @return array An array containing the formatted date of the first observation and its legacy ID.
     */
    public function first_observation_date_generic(string $fieldName): array
    {
        $language = app()->getLocale();
        $column = self::mapFieldName($fieldName);
        $first = Observation::where($column, $this->id)->min('observed_on');

        if ($first === null) {
            return [null, null];
        }

        return $this->get_date_and_id_generic($first, $language, $column);
    }

    /**
     * Retrieves the date of the last observation made with the model.
     *
     * @param  string  $fieldName  The legacy field name (e.g. 'instrumentid', 'locationid')
     * @return array An array containing the formatted date of the last observation and its legacy ID.
     */
    public function last_observation_date_generic(string $fieldName): array
    {
        $language = app()->getLocale();
        $column = self::mapFieldName($fieldName);
        $last = Observation::where($column, $this->id)->max('observed_on');

        if ($last === null) {
            return [null, null];
        }

        return $this->get_date_and_id_generic($last, $language, $column);
    }

    /**
     * Helper to format the date and resolve the legacy observation ID for view links.
     *
     * Returns [formattedDate, id] where id is positive for deepsky and negative for
     * comet observations, preserving the convention expected by existing blade views.
     */
    public function get_date_and_id_generic(string $observationDate, string $language, string $column): array
    {
        $formatted = Carbon::parse($observationDate)->locale($language)->isoFormat('LL');

        $row = Observation::where($column, $this->id)
            ->where('observed_on', $observationDate)
            ->orderBy('id')
            ->first(['legacy_id', 'legacy_source']);

        if (!$row) {
            return [$formatted, null];
        }

        $id = ($row->legacy_source === 'comet') ? -(int) $row->legacy_id : (int) $row->legacy_id;

        return [$formatted, $id];
    }
}
