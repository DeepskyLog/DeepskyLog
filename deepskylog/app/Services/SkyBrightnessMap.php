<?php

namespace App\Services;

use App\Charts\SkyQualityHistoryChart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The community sky-brightness map: the SQM and NELM the observers recorded
 * with their observations, per cell of CELL_DEG in latitude and longitude.
 *
 * Privacy: a location is never shown, only the cell it lies in, without
 * the observers; hidden locations are left out. A cell of one observer is
 * shown: it says less than the page of their location, which is public.
 *
 * The values are those of LocationSkyHistory: only the owner's observations
 * at a location, within its SQM_RANGE / NELM_RANGE, a night counted once
 * (the median of its observations). Every observer weighs the same: the
 * value of a cell (or a year) is the median of the medians of the nights of
 * each observer, so one observer with a thousand nights in their garden does
 * not outweigh the others.
 *
 * The sky brightness (`sky`) combines both on the scale of the SQM: a night
 * gives its measured SQM, else its NELM converted with the observer's
 * fstOffset (SkyQualityHistoryChart::sqmOfNelm()). A converted NELM is
 * unbiased up to an SQM of about 21 but reads about half a magnitude too
 * dark under a dark sky, so a value mostly from NELMs is marked an estimate
 * (`sky_estimated`, more than half of its nights from a NELM). A NELM of 7 or
 * more converts to the top of the scale (22.5), darker than any natural sky:
 * a converted NELM is at most MAX_CONVERTED_SQM.
 */
class SkyBrightnessMap
{
    /** The size of a cell in degrees of latitude and longitude. */
    public const CELL_DEG = 0.25;

    /** The periods of the map: all years, or the last RECENT_YEARS. */
    public const PERIODS = ['all', 'recent'];

    public const RECENT_YEARS = 10;

    /** Fresh for a day, then shown for a week while recomputed after the response. */
    private const CACHE_SECONDS = [86400, 604800];

    private const CACHE_VERSION = 4;

    /** The darkest sky a converted NELM gives: about that of a natural sky. */
    public const MAX_CONVERTED_SQM = 22.0;

    /** The quantities with the precision of their medians. */
    private const PRECISION = ['sqm' => 2, 'nelm' => 1, 'sky' => 2];

    public static function isPeriod(mixed $period): bool
    {
        return is_string($period) && in_array($period, self::PERIODS, true);
    }

    /** The first year of the period, null for all years. */
    public static function sinceYear(string $period): ?int
    {
        return $period === 'recent' ? (int) now()->year - self::RECENT_YEARS + 1 : null;
    }

    /**
     * The cells of the period, cached.
     *
     * @return list<array{south: float, west: float, north: float, east: float, sqm: ?float, nelm: ?float, sky: float, sky_nelm_nights: int, sky_estimated: bool, observers: int, nights: int, years: list<array{year: int, sqm: ?float, nelm: ?float, sky: float, sky_estimated: bool}>}>
     */
    public static function cells(string $period = 'all'): array
    {
        $period = self::isPeriod($period) ? $period : 'all';

        return Cache::flexible(
            'sky_brightness_map:v'.self::CACHE_VERSION.':'.$period,
            self::CACHE_SECONDS,
            fn () => self::compute(self::sinceYear($period)),
        );
    }

    /**
     * The cells with values since the
     * year (all years for null), south to north and west to east.
     */
    public static function compute(?int $sinceYear = null): array
    {
        $query = DB::table('observations as o')
            ->join('locations as l', 'l.id', '=', 'o.location_id')
            ->join('users as u', 'u.username', '=', 'o.observer_username')
            ->whereColumn('l.user_id', 'u.id')
            ->where(fn ($query) => $query->where('l.hidden', false)->orWhereNull('l.hidden'))
            ->whereNotNull('l.latitude')
            ->whereNotNull('l.longitude')
            ->whereNotNull('o.observed_on')
            ->where(fn ($query) => $query->whereBetween('o.sqm', LocationSkyHistory::SQM_RANGE)
                ->orWhereBetween('o.limiting_magnitude', LocationSkyHistory::NELM_RANGE));
        if ($sinceYear !== null) {
            $query->where('o.observed_on', '>=', sprintf('%04d-01-01', $sinceYear));
        }

        // cell => observer => night => values
        $perNight = [];
        $fstOffsets = [];
        foreach ($query->select(['u.id as observer', 'u.fstOffset as fst_offset', 'l.latitude', 'l.longitude', 'o.observed_on', 'o.sqm', 'o.limiting_magnitude'])->cursor() as $row) {
            $fstOffsets[$row->observer] = (float) ($row->fst_offset ?? 0);
            $cell = (int) floor((float) $row->latitude / self::CELL_DEG).':'.(int) floor((float) $row->longitude / self::CELL_DEG);
            $date = substr((string) $row->observed_on, 0, 10);
            $perNight[$cell][$row->observer][$date]['sqm'][] = LocationSkyHistory::within($row->sqm, LocationSkyHistory::SQM_RANGE);
            $perNight[$cell][$row->observer][$date]['nelm'][] = LocationSkyHistory::within($row->limiting_magnitude, LocationSkyHistory::NELM_RANGE);
        }

        $cells = [];
        foreach ($perNight as $cell => $observers) {
            // The medians of each observer, over all their nights and per year
            $medians = ['sqm' => [], 'nelm' => [], 'sky' => []];
            $yearMedians = [];
            // The nights, and those whose sky brightness is a converted NELM, in all and per year
            $nights = $nelmNights = 0;
            $yearNights = [];
            foreach ($observers as $observer => $dates) {
                $observerNights = ['sqm' => [], 'nelm' => [], 'sky' => []];
                $observerYears = [];
                foreach ($dates as $date => $values) {
                    $year = (int) substr($date, 0, 4);
                    $night = [
                        'sqm' => LocationSkyHistory::median($values['sqm'], self::PRECISION['sqm']),
                        'nelm' => LocationSkyHistory::median($values['nelm'], self::PRECISION['nelm']),
                    ];
                    $fromNelm = $night['sqm'] === null;
                    $night['sky'] = $fromNelm
                        ? round(min(self::MAX_CONVERTED_SQM, SkyQualityHistoryChart::sqmOfNelm($night['nelm'], $fstOffsets[$observer])), self::PRECISION['sky'])
                        : $night['sqm'];
                    foreach ($night as $quantity => $value) {
                        $observerNights[$quantity][] = $value;
                        $observerYears[$year][$quantity][] = $value;
                    }
                    $nelmNights += (int) $fromNelm;
                    $yearNights[$year]['all'] = ($yearNights[$year]['all'] ?? 0) + 1;
                    $yearNights[$year]['nelm'] = ($yearNights[$year]['nelm'] ?? 0) + (int) $fromNelm;
                }
                $nights += count($dates);
                foreach (self::PRECISION as $quantity => $precision) {
                    $medians[$quantity][] = LocationSkyHistory::median($observerNights[$quantity], $precision);
                    foreach ($observerYears as $year => $values) {
                        $yearMedians[$year][$quantity][] = LocationSkyHistory::median($values[$quantity], $precision);
                    }
                }
            }

            ksort($yearMedians);
            $years = [];
            foreach ($yearMedians as $year => $values) {
                $years[] = [
                    'year' => $year,
                    'sqm' => LocationSkyHistory::median($values['sqm'], self::PRECISION['sqm']),
                    'nelm' => LocationSkyHistory::median($values['nelm'], self::PRECISION['nelm']),
                    'sky' => LocationSkyHistory::median($values['sky'], self::PRECISION['sky']),
                    'sky_estimated' => $yearNights[$year]['nelm'] * 2 > $yearNights[$year]['all'],
                ];
            }

            [$row, $column] = array_map('intval', explode(':', $cell));
            $cells[] = [
                'south' => $row * self::CELL_DEG,
                'west' => $column * self::CELL_DEG,
                'north' => ($row + 1) * self::CELL_DEG,
                'east' => ($column + 1) * self::CELL_DEG,
                'sqm' => LocationSkyHistory::median($medians['sqm'], self::PRECISION['sqm']),
                'nelm' => LocationSkyHistory::median($medians['nelm'], self::PRECISION['nelm']),
                'sky' => LocationSkyHistory::median($medians['sky'], self::PRECISION['sky']),
                'sky_nelm_nights' => $nelmNights,
                'sky_estimated' => $nelmNights * 2 > $nights,
                'observers' => count($observers),
                'nights' => $nights,
                'years' => $years,
            ];
        }

        usort($cells, fn ($a, $b) => [$a['south'], $a['west']] <=> [$b['south'], $b['west']]);

        return $cells;
    }
}
