<?php

namespace App\Console\Commands;

use App\Models\UserObjectMetric;
use App\Jobs\ComputeContrastReserveForObject;
use Illuminate\Console\Command;

/**
 * Queue jobs for user_object_metrics that have NULL contrast_reserve
 */
class QueueNullContrastReserve extends Command
{
    protected $signature = 'metrics:queue-null-cr {user_id} {--instrument_id=} {--location_id=} {--no-location} {--lens_id=} {--chunk=1000} {--force}';

    protected $description = 'Queue jobs for existing NULL contrast reserve records';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $instrumentId = $this->option('instrument_id') ? (int) $this->option('instrument_id') : null;
        $locationId = $this->option('location_id') ? (int) $this->option('location_id') : null;
        $noLocation = $this->option('no-location');
        $lensId = $this->option('lens_id') ? (int) $this->option('lens_id') : null;
        $chunk = (int) $this->option('chunk');

        $query = UserObjectMetric::where('user_id', $userId)
            ->whereNull('contrast_reserve');

        if ($instrumentId) {
            $query->where('instrument_id', $instrumentId);
        }
        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif ($noLocation) {
            $query->whereNull('location_id');
        }
        if ($lensId) {
            $query->where('lens_id', $lensId);
        } else {
            $query->whereNull('lens_id');
        }

        $total = $query->count();
        $this->info("Found {$total} records with NULL contrast_reserve");

        if ($total === 0) {
            $this->info("Nothing to queue!");
            return 0;
        }

        if (!$this->option('force') && !$this->confirm("Queue {$total} jobs?", true)) {
            $this->info("Cancelled.");
            return 1;
        }

        $queued = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('object_name')->chunk($chunk, function ($metrics) use (&$queued, $bar) {
            foreach ($metrics as $metric) {
                ComputeContrastReserveForObject::dispatch(
                    $metric->user_id,
                    $metric->instrument_id,
                    $metric->location_id,
                    $metric->object_name,
                    $metric->lens_id
                );
                $queued++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Queued {$queued} jobs successfully!");

        return 0;
    }
}
