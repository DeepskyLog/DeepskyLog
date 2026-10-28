<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair the sky values that the conversions of the astronomy library reject.
 *
 * Magnitude accepts a naked-eye limiting magnitude (NELM) from 0 to 8 and an
 * SQM from 10. Some old observations and locations hold other values:
 *
 * - an SQM between 0 and 10 that is a plausible NELM (2.5 to 8, the range
 *   Magnitude::sqmToNelm() returns) was typed in the wrong field: it is moved
 *   to the limiting magnitude when that is empty, and dropped otherwise;
 * - a lower SQM (mostly 1.00, from forms of the old site) is dropped;
 * - a NELM above 8 is not a naked-eye value (probably a telescopic limiting
 *   magnitude) and can't be converted to an SQM either, so it is dropped.
 *
 * Observations store a missing value as null, locations as -999. The import
 * of the old site's data copied its placeholders for a missing value (-1 and 0)
 * into observations; these become null as well, so the API doesn't return them
 * as values. That bulk update leaves updated_at alone: the value doesn't change,
 * only how "not set" is stored.
 *
 * Saving a location deletes its cached object metrics (see Location::booted()).
 * The object observation statistics don't use these columns.
 *
 * Safe to run more than once: only rows with a rejected value are touched.
 */
class RepairSkyValues extends Command
{
    /** The lowest SQM of a valid sky; lower values are not an SQM. */
    private const SQM_MIN = 10.0;

    /** The highest naked-eye limiting magnitude. */
    private const NELM_MAX = 8.0;

    /** The lowest NELM Magnitude::sqmToNelm() gives; a lower "SQM" is no NELM either. */
    private const NELM_MIN_PLAUSIBLE = 2.5;

    /** How locations store a missing value. */
    private const LOCATION_UNSET = -999;

    protected $signature = 'observations:repair-sky-values
        {--apply : Apply fixes to the database}';

    protected $description = 'Move NELM values stored as SQM to the limiting magnitude, and drop SQM and NELM values the astronomy library rejects';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $observations = $this->observationFixes();
        $locations = $this->locationFixes();
        $placeholderSqm = DB::table('observations')->where('sqm', '<=', 0);
        $placeholderNelm = DB::table('observations')->where('limiting_magnitude', '<=', 0);
        $placeholderCounts = [$placeholderSqm->count(), $placeholderNelm->count()];

        if ($observations !== []) {
            $this->table(
                ['Observation', 'Observer', 'Date', 'SQM', 'NELM', 'New SQM', 'New NELM'],
                array_map(fn ($f) => [
                    $f['row']->id, $f['row']->observer_username, $f['row']->observed_on,
                    $this->show($f['row']->sqm), $this->show($f['row']->limiting_magnitude),
                    $this->show($f['sqm']), $this->show($f['limiting_magnitude']),
                ], $observations)
            );
        }

        if ($locations !== []) {
            $this->table(
                ['Location', 'Name', 'Observer', 'SQM', 'NELM', 'New SQM', 'New NELM'],
                array_map(fn ($f) => [
                    $f['location']->id, $f['location']->name, $f['location']->observer,
                    $this->show($f['location']->skyBackground), $this->show($f['location']->limitingMagnitude),
                    $this->show($f['skyBackground']), $this->show($f['limitingMagnitude']),
                ], $locations)
            );
        }

        if ($apply) {
            DB::transaction(function () use ($observations, $locations, $placeholderSqm, $placeholderNelm) {
                foreach ($observations as $fix) {
                    DB::table('observations')->where('id', $fix['row']->id)->update([
                        'sqm' => $fix['sqm'],
                        'limiting_magnitude' => $fix['limiting_magnitude'],
                        'updated_at' => now(),
                    ]);
                }

                // After the repairs above, which keep a placeholder they don't replace.
                $placeholderSqm->update(['sqm' => null]);
                $placeholderNelm->update(['limiting_magnitude' => null]);

                foreach ($locations as $fix) {
                    // Through the model, so the cached metrics of the location are deleted.
                    $fix['location']->update([
                        'skyBackground' => $fix['skyBackground'],
                        'limitingMagnitude' => $fix['limitingMagnitude'],
                    ]);
                }
            });

            if ($observations !== [] || array_sum($placeholderCounts) > 0) {
                // The observation updates bypass the model events.
                Location::clearResponseCache();
            }
        }

        $moved = count(array_filter($observations, fn ($f) => $f['moved']));

        $this->newLine();
        $this->info('Observations with an SQM moved to the limiting magnitude: ' . $moved);
        $this->info('Observations with a value dropped: ' . (count($observations) - $moved));
        $this->info('Locations repaired: ' . count($locations));
        $this->info('Observations with a placeholder SQM (-1 or 0) set to empty: ' . $placeholderCounts[0]);
        $this->info('Observations with a placeholder limiting magnitude (0) set to empty: ' . $placeholderCounts[1]);

        if (! $apply && ($observations !== [] || $locations !== [] || array_sum($placeholderCounts) > 0)) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{row: object, sqm: ?float, limiting_magnitude: ?float, moved: bool}>
     */
    private function observationFixes(): array
    {
        $rows = DB::table('observations')
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('sqm', '>', 0)->where('sqm', '<', self::SQM_MIN))
                ->orWhere('limiting_magnitude', '>', self::NELM_MAX))
            ->orderBy('id')
            ->get(['id', 'observer_username', 'observed_on', 'sqm', 'limiting_magnitude']);

        $fixes = [];
        foreach ($rows as $row) {
            [$sqm, $nelm, $moved] = $this->repaired(
                $row->sqm === null ? null : (float) $row->sqm,
                $row->limiting_magnitude === null ? null : (float) $row->limiting_magnitude,
                null
            );
            $fixes[] = ['row' => $row, 'sqm' => $sqm, 'limiting_magnitude' => $nelm, 'moved' => $moved];
        }

        return $fixes;
    }

    /**
     * @return array<int, array{location: Location, skyBackground: float, limitingMagnitude: float}>
     */
    private function locationFixes(): array
    {
        $locations = Location::query()
            ->where(fn ($q) => $q
                ->where(fn ($q) => $q->where('skyBackground', '>', 0)->where('skyBackground', '<', self::SQM_MIN))
                ->orWhere('limitingMagnitude', '>', self::NELM_MAX))
            ->orderBy('id')
            ->get();

        $fixes = [];
        foreach ($locations as $location) {
            [$sqm, $nelm] = $this->repaired($location->skyBackground, $location->limitingMagnitude, self::LOCATION_UNSET);
            $fixes[] = ['location' => $location, 'skyBackground' => $sqm, 'limitingMagnitude' => $nelm];
        }

        return $fixes;
    }

    /**
     * The repaired SQM and NELM, and whether the SQM was moved to the NELM.
     * Values of 0 or less mean "not set" in the old data.
     *
     * @return array{0: float|int|null, 1: float|int|null, 2: bool}
     */
    private function repaired(?float $sqm, ?float $nelm, ?int $unset): array
    {
        $moved = false;

        if ($nelm !== null && $nelm > self::NELM_MAX) {
            $nelm = $unset;
        }

        if ($sqm !== null && $sqm > 0 && $sqm < self::SQM_MIN) {
            $nelmIsSet = $nelm !== null && $nelm > 0;
            if (! $nelmIsSet && $sqm >= self::NELM_MIN_PLAUSIBLE && $sqm <= self::NELM_MAX) {
                $nelm = $sqm;
                $moved = true;
            }
            $sqm = $unset;
        }

        return [$sqm, $nelm, $moved];
    }

    private function show(float|int|string|null $value): string
    {
        if ($value === null || (float) $value == self::LOCATION_UNSET) {
            return '-';
        }

        return (string) $value;
    }
}
