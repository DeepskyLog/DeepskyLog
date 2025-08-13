<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasObservationsDates
{
    /**
     * Retrieves the date of the first observation made with the model.
     *
     * @param  string  $fieldName  The field name to filter observations (e.g., 'instrumentid', 'locationid')
     * @return array An array containing the formatted date of the first observation and its ID.
     */
    public function first_observation_date_generic(string $fieldName): array
    {
        $language = app()->getLocale();
        $firstDeepskyObservation = \App\Models\ObservationsOld::where($fieldName, $this->id)->min('date');
        $firstCometObservation = \App\Models\CometObservationsOld::where($fieldName, $this->id)->min('date');

        if ($firstDeepskyObservation == null && $firstCometObservation != null) {
            $firstObservation = $firstCometObservation;
        } elseif ($firstDeepskyObservation != null && $firstCometObservation == null) {
            $firstObservation = $firstDeepskyObservation;
        } elseif ($firstDeepskyObservation == null && $firstCometObservation == null) {
            return [null, null];
        } else {
            $firstObservation = min($firstDeepskyObservation, $firstCometObservation);
        }

        return $this->get_date_and_id_generic($firstObservation, $language, $firstDeepskyObservation, $fieldName);
    }

    /**
     * Retrieves the date of the last observation made with the model.
     *
     * @param  string  $fieldName  The field name to filter observations (e.g., 'instrumentid', 'locationid')
     * @return array An array containing the formatted date of the last observation and its ID.
     */
    public function last_observation_date_generic(string $fieldName): array
    {
        $language = app()->getLocale();
        $lastDeepskyObservation = \App\Models\ObservationsOld::where($fieldName, $this->id)->max('date');
        $lastCometObservation = \App\Models\CometObservationsOld::where($fieldName, $this->id)->max('date');

        $lastObservation = max($lastDeepskyObservation, $lastCometObservation);

        if ($lastObservation == null) {
            return [null, null];
        }

        return $this->get_date_and_id_generic($lastObservation, $language, $lastDeepskyObservation, $fieldName);
    }

    /**
     * Helper to format date and get observation ID.
     */
    public function get_date_and_id_generic(mixed $observationDate, string $language, mixed $deepskyDate, string $fieldName): array
    {
        $date = Carbon::createFromFormat('Ymd', $observationDate)->locale($language)->isoFormat('LL');

        if ($observationDate == $deepskyDate) {
            $id = \App\Models\ObservationsOld::where($fieldName, $this->id)->where('date', $observationDate)->first()['id'];
        } else {
            $id = -\App\Models\CometObservationsOld::where($fieldName, $this->id)->where('date', $observationDate)->first()['id'];
        }

        return [$date, $id];
    }
}
