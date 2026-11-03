<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DetectionThreshold;
use Illuminate\Console\Command;

/**
 * Fit the detection threshold of the observers ("Your own eye",
 * App\Services\DetectionThreshold) whose observations changed since their
 * last fit: those with at least DetectionThreshold::MIN_NOT_SEEN observations
 * not seen, and those who had a fit. The scheduler runs it daily; the
 * profile page also queues a refit when it finds one stale.
 */
class FitDetectionThresholds extends Command
{
    protected $signature = 'observers:fit-detection-thresholds
        {--all : Refit every observer, also when their observations did not change}
        {--user= : Only this observer (username)}';

    protected $description = "Fit the observers' own detection thresholds from their seen and not seen observations";

    public function handle(): int
    {
        $usernames = $this->option('user') !== null
            ? [(string) $this->option('user')]
            : array_values(array_unique([
                ...DetectionThreshold::candidates(),
                ...User::query()->whereNotNull('detection_threshold_fit')->pluck('username')->all(),
            ]));

        $fitted = 0;
        foreach (User::query()->whereIn('username', $usernames)->orderBy('username')->get() as $user) {
            if (! $this->option('all') && ! DetectionThreshold::stale($user)) {
                continue;
            }
            $fit = DetectionThreshold::compute($user);
            $fitted++;
            if ($this->output->isVerbose()) {
                $this->line(sprintf(
                    '%s: %s (%d seen, %d not seen, %d questionable)',
                    $user->username,
                    $fit['threshold'] === null ? 'no threshold' : sprintf('%+.2f ± %.2f%s', $fit['threshold'], $fit['standard_error'] ?? 0, $fit['reliable'] ? '' : ', not reliable'),
                    $fit['seen'],
                    $fit['not_seen'],
                    $fit['questionable']
                ));
            }
        }

        $this->info(sprintf('%d of %d observers fitted.', $fitted, count($usernames)));

        return self::SUCCESS;
    }
}
