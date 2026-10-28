<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\DB;

/**
 * The sky quality of a location over the years: the SQM and the naked-eye
 * limiting magnitude its owner recorded with their observations at it (a
 * location is one observer's; the few observations of others at it are
 * errors of old data).
 *
 * A night counts once (the median of its observations), so a night with a
 * hundred observations weighs as much as one with a single observation; the
 * median of the nights of each year shows the trend (light pollution).
 *
 * Only the values the owner entered count, not those converted from
 * the other quantity. Values outside SQM_RANGE / NELM_RANGE are typing
 * errors or placeholders of the old site and are left out.
 */
class LocationSkyHistory
{
    /** The plausible SQM values (mag/arcsec²). */
    public const SQM_RANGE = [14.0, 22.5];

    /** The plausible naked-eye limiting magnitudes. */
    public const NELM_RANGE = [2.0, 8.0];

    /**
     * @param  array<int, array{date: string, sqm: ?float, nelm: ?float}>  $nights  oldest first
     * @param  array<int, array{sqm: ?float, nelm: ?float, nights: int}>  $years  keyed by year, oldest first
     */
    public function __construct(
        public readonly array $nights,
        public readonly array $years,
    ) {}

    public static function forLocation(Location $location): self
    {
        $location->loadMissing('user');

        $rows = DB::table('observations')
            ->where('location_id', $location->id)
            ->where('observer_username', $location->user?->username)
            ->whereNotNull('observed_on')
            ->where(fn ($query) => $query->whereBetween('sqm', self::SQM_RANGE)
                ->orWhereBetween('limiting_magnitude', self::NELM_RANGE))
            ->orderBy('observed_on')
            ->get(['observed_on', 'sqm', 'limiting_magnitude']);

        $perNight = [];
        foreach ($rows as $row) {
            $date = substr((string) $row->observed_on, 0, 10);
            $perNight[$date]['sqm'][] = self::within($row->sqm, self::SQM_RANGE);
            $perNight[$date]['nelm'][] = self::within($row->limiting_magnitude, self::NELM_RANGE);
        }

        $nights = [];
        $perYear = [];
        foreach ($perNight as $date => $values) {
            $night = [
                'date' => $date,
                'sqm' => self::median($values['sqm'], 2),
                'nelm' => self::median($values['nelm'], 1),
            ];
            $nights[] = $night;

            $year = (int) substr($date, 0, 4);
            $perYear[$year]['sqm'][] = $night['sqm'];
            $perYear[$year]['nelm'][] = $night['nelm'];
        }

        $years = [];
        foreach ($perYear as $year => $values) {
            $years[$year] = [
                'sqm' => self::median($values['sqm'], 2),
                'nelm' => self::median($values['nelm'], 1),
                'nights' => count($values['sqm']),
            ];
        }

        return new self($nights, $years);
    }

    public function isEmpty(): bool
    {
        return $this->nights === [];
    }

    /** Whether any night has a value of the quantity ('sqm' or 'nelm'). */
    public function has(string $quantity): bool
    {
        foreach ($this->nights as $night) {
            if ($night[$quantity] !== null) {
                return true;
            }
        }

        return false;
    }

    private static function within(mixed $value, array $range): ?float
    {
        if ($value === null || ! is_numeric($value)) {
            return null;
        }
        $value = (float) $value;

        return $value >= $range[0] && $value <= $range[1] ? $value : null;
    }

    /** The median of the values that are not null, or null without any. */
    private static function median(array $values, int $precision): ?float
    {
        $values = array_values(array_filter($values, fn ($value) => $value !== null));
        if ($values === []) {
            return null;
        }
        sort($values);
        $count = count($values);
        $middle = intdiv($count, 2);
        $median = $count % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;

        return round($median, $precision);
    }
}
