<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Services\CometCoordinates;
use App\Services\Comets\HorizonsElements;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fetch from JPL Horizons the orbital elements of the observed comets at the
 * epochs of their observations that the current elements and the elements
 * fetched before don't cover (App\Services\CometCoordinates::atEpoch()).
 */
class FetchCometEpochElements extends Command
{
    /**
     * An observation is covered by elements whose epoch lies at most this many
     * days from it: the two-body orbit of the library drifts slowly, but a
     * comet close to the Earth needs elements of its own apparition.
     */
    public const COVER_DAYS = 100;

    /** Milliseconds between two requests, to be kind to Horizons. */
    public const DELAY_MS = 500;

    protected $signature = 'comets:fetch-epoch-elements
        {--apply : Fetch and store the elements (without it, only list what would be fetched)}
        {--comet= : Only the comet with this name or designation}';

    protected $description = 'Fetch the orbital elements of comets at the epochs of their observations from JPL Horizons';

    public function handle(HorizonsElements $horizons): int
    {
        $apply = (bool) $this->option('apply');
        $planned = $this->planned();
        if ($planned === []) {
            $this->info('All comet observations are covered by orbital elements near their date.');

            return self::SUCCESS;
        }

        $stored = 0;
        $failed = 0;
        foreach ($planned as $i => [$designation, $epoch, $name, $count]) {
            $label = sprintf('%s at %s (%d observations)', $name, $this->dateOf($epoch), $count);
            if (! $apply) {
                $this->line('Would fetch ' . $label);

                continue;
            }
            if ($i > 0) {
                usleep(self::DELAY_MS * 1000);
            }
            $elements = $horizons->fetch($designation, $epoch);
            if (is_string($elements)) {
                $failed++;
                $this->warn($label . ': ' . $elements);

                continue;
            }
            DB::table(CometCoordinates::EPOCH_TABLE)->updateOrInsert(
                ['designation' => $designation, 'epoch' => $elements['epoch']],
                array_merge($elements, ['created_at' => now(), 'updated_at' => now()]),
            );
            $stored++;
            if ($this->output->isVerbose()) {
                $this->line(sprintf('Stored %s: q %.6f, e %.6f', $label, $elements['q'], $elements['e']));
            }
        }

        if ($apply) {
            $this->info("Stored {$stored} sets of elements" . ($failed ? ", {$failed} failed" : '') . '.');
            if ($stored > 0) {
                $this->line('Run php artisan observations:check-suspect --all to check the comet observations again.');
            }
        } else {
            $this->info(count($planned) . ' sets of elements to fetch. Run with --apply to fetch them.');
        }

        return self::SUCCESS;
    }

    /**
     * Per observed comet the epochs to fetch: each takes the uncovered dates
     * within twice COVER_DAYS of the first one, its epoch in their middle.
     *
     * @return array<int, array{0: string, 1: float, 2: string, 3: int}> designation, epoch, name, observations
     */
    public function planned(): array
    {
        $dates = DB::table('observations')
            ->join('cometobjects', 'cometobjects.id', '=', DB::raw('CAST(observations.target_key AS ' . (DB::getDriverName() === 'sqlite' ? 'INTEGER' : 'UNSIGNED') . ')'))
            ->where('observations.target_type', Observation::TYPE_COMET)
            ->when($this->option('comet'), fn ($q, $comet) => $q->where(fn ($q) => $q
                ->where('cometobjects.name', $comet)
                ->orWhere('cometobjects.name', 'like', $comet . ' %')
                ->orWhere('cometobjects.name', 'like', $comet . '/%')))
            ->select('cometobjects.name', 'observations.observed_on')
            ->get()
            ->groupBy('name');

        $planned = [];
        foreach ($dates as $name => $rows) {
            [$designation] = CometCoordinates::splitName((string) $name);
            if ($designation === null) {
                continue;
            }
            $epochs = DB::table(CometCoordinates::EPOCH_TABLE)->where('designation', $designation)->pluck('epoch')
                ->map(fn ($e) => (float) $e)->all();
            $current = CometCoordinates::orbitalElements((string) $name);
            if ($current !== null && is_numeric($current->epoch ?? null)) {
                $epochs[] = (float) $current->epoch;
            }

            $days = $rows->map(fn ($row) => CometCoordinates::julianDay(Carbon::parse($row->observed_on, 'UTC')))->sort()->values();
            $uncovered = $days->reject(fn (float $jd) => $this->covered($jd, $epochs))->values();
            while ($uncovered->isNotEmpty()) {
                $first = $uncovered->first();
                $group = $uncovered->filter(fn (float $jd) => $jd <= $first + 2 * self::COVER_DAYS);
                $epoch = floor(($group->min() + $group->max()) / 2) + 0.5;
                $planned[] = [$designation, $epoch, (string) $name, $group->count()];
                $epochs[] = $epoch;
                $uncovered = $uncovered->reject(fn (float $jd) => $this->covered($jd, $epochs))->values();
            }
        }

        return $planned;
    }

    /**
     * @param  array<int, float>  $epochs
     */
    private function covered(float $jd, array $epochs): bool
    {
        foreach ($epochs as $epoch) {
            if (abs($jd - $epoch) <= self::COVER_DAYS) {
                return true;
            }
        }

        return false;
    }

    private function dateOf(float $jd): string
    {
        return Carbon::createFromTimestampUTC((int) round(($jd - 2440587.5) * 86400))->format('Y-m-d');
    }
}
