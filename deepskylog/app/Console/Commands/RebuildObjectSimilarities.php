<?php

namespace App\Console\Commands;

use App\Jobs\ComputeObjectSimilarities;
use App\Services\AlsoSeenObjects;
use Illuminate\Console\Command;

/**
 * Recalculate "Observers who saw this also saw…" (object_similarities) from
 * all observations. The scheduler queues it weekly; run it after creating
 * the table and after an SQL import.
 */
class RebuildObjectSimilarities extends Command
{
    protected $signature = 'objects:rebuild-also-seen {--queue : Queue the calculation instead of running it}';

    protected $description = 'Recalculate which deep-sky objects the same observers saw (Observers who saw this also saw…)';

    public function handle(): int
    {
        if ($this->option('queue')) {
            ComputeObjectSimilarities::dispatch();
            $this->info('Queued.');

            return self::SUCCESS;
        }

        $start = microtime(true);
        $result = AlsoSeenObjects::rebuild();

        $this->info(sprintf(
            '%d observer/object pairs read, %d suggestions of %d objects written in %.1f s.',
            $result['pairs'],
            $result['rows'],
            $result['objects'],
            microtime(true) - $start
        ));

        return self::SUCCESS;
    }
}
