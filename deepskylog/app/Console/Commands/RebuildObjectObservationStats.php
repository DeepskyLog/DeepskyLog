<?php

namespace App\Console\Commands;

use App\Services\ObjectObservationStatistics;
use Illuminate\Console\Command;

/**
 * Recalculate the observation statistics of all deep-sky objects
 * (object_observation_stats and object_observation_stats_by_observer).
 *
 * Saving or deleting an observation keeps them up to date; run this after
 * creating the tables, and after changing observations outside the models
 * (an import straight into the database, a repair in SQL).
 */
class RebuildObjectObservationStats extends Command
{
    protected $signature = 'observations:rebuild-object-stats';

    protected $description = 'Recalculate the observation statistics of the deep-sky objects shown in the object tables';

    public function handle(): int
    {
        $start = microtime(true);
        $rows = ObjectObservationStatistics::rebuild();

        $this->info(sprintf(
            'Statistics of %d objects and %d observer/object pairs written in %.1f s.',
            $rows['objects'],
            $rows['observers'],
            microtime(true) - $start
        ));

        return self::SUCCESS;
    }
}
