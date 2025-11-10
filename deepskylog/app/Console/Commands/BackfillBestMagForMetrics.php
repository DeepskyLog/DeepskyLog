<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserObjectMetric;
use App\Jobs\ComputeContrastReserveForObject;

class BackfillBestMagForMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:backfill-best-mag {--limit=1000} {--chunk=100} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing optimum_detection_magnification for rows where contrast_reserve is present';

    public function handle(): int
    {
        $limit = intval($this->option('limit') ?? 1000);
        $chunk = intval($this->option('chunk') ?? 100);
        $dry = (bool) $this->option('dry-run');

        $this->info("Backfill: limit={$limit}, chunk={$chunk}, dry_run=" . ($dry ? 'yes' : 'no'));

        $query = UserObjectMetric::query()
            ->whereNotNull('contrast_reserve')
            ->whereNull('optimum_detection_magnification')
            ->orderBy('id', 'asc');

        $total = $query->count();
        $this->info("Found {$total} rows with contrast_reserve but missing optimum_detection_magnification");
        if ($total === 0) {
            return 0;
        }

        $processed = 0;
        $bar = $this->output->createProgressBar(min($total, $limit));
        $bar->start();

        $query->limit($limit)->chunk($chunk, function ($rows) use (&$processed, $dry, $bar) {
            foreach ($rows as $row) {
                $processed++;
                $bar->advance();
                if ($dry) {
                    continue;
                }
                try {
                    // Recompute by invoking the job synchronously so the
                    // same computation codepath (including lens support) runs.
                    $job = new ComputeContrastReserveForObject($row->user_id, $row->instrument_id, $row->location_id, $row->object_name, $row->lens_id ?? null);
                    try {
                        $job->handle();
                    } catch (\Throwable $ex) {
                        // If inline run fails, fall back to queued dispatch
                        try {
                            ComputeContrastReserveForObject::dispatch($row->user_id, $row->instrument_id, $row->location_id, $row->object_name, $row->lens_id ?? null);
                        } catch (\Throwable $_) {
                            // swallow to continue processing other rows
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore per-row errors
                }
            }
        });

        $bar->finish();
        $this->line("");
        $this->info("Processed {$processed} rows (requested limit {$limit})");

        return 0;
    }
}
