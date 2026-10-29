<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Services\Observations\SuspectObservations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Find the observations that look wrong (App\Services\Observations\SuspectObservations):
 * the object below the horizon, far too faint for the instrument, or a
 * duplicate. Without --all only the observations changed since the last run
 * (and the duplicates of their observers) are checked; the first run, and a
 * run after the cache was cleared, checks all. Scheduled hourly, and weekly
 * with --all, as a changed object, location or instrument changes the
 * findings of observations that did not change.
 */
class CheckSuspectObservations extends Command
{
    /** The cache key of the moment up to which the observations were checked. */
    public const CHECKED_UNTIL_KEY = 'suspect_observations:checked_until';

    protected $signature = 'observations:check-suspect
        {--all : Check all observations, not only those changed since the last run}';

    protected $description = 'Find observations of objects below the horizon, far too faint for the instrument, or duplicated';

    public function handle(SuspectObservations $suspects): int
    {
        $start = now();
        $since = $this->option('all') ? null : Cache::get(self::CHECKED_UNTIL_KEY);

        $query = Observation::query()
            // a change in the second the last run started is checked again
            ->when($since !== null, fn ($q) => $q->where('updated_at', '>=', $since));
        $usernames = $since !== null ? (clone $query)->distinct()->pluck('observer_username')->all() : null;

        $total = (clone $query)->count();
        $this->info($since !== null ? "Checking {$total} observations changed since {$since}." : "Checking all {$total} observations.");
        $bar = $this->output->createProgressBar($total);
        $found = $suspects->check($query, fn (int $checked) => $bar->setProgress($checked));
        $bar->finish();
        $this->newLine();

        $duplicates = $usernames === [] ? 0 : $suspects->checkDuplicates($usernames);

        Cache::forever(self::CHECKED_UNTIL_KEY, $start->toDateTimeString());

        $this->info(sprintf(
            'Checked %d observations in %.1f s: %d below the horizon, %d too faint, %d duplicates%s. Open findings: %d.',
            $found['checked'],
            $start->diffInSeconds(now()),
            $found[SuspectObservation::REASON_BELOW_HORIZON],
            $found[SuspectObservation::REASON_TOO_FAINT],
            $duplicates,
            $usernames !== null ? ' among their observers\' observations' : '',
            SuspectObservation::query()->open()->count()
        ));

        return self::SUCCESS;
    }
}
