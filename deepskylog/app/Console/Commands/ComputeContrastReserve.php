<?php

namespace App\Console\Commands;

use App\Models\DeepskyObject;
use App\Models\UserObjectMetric;
use App\Jobs\ComputeContrastReserveForObject;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Compute contrast reserve for a user's instrument/location and store it in the
 * user_object_metrics table.
 */
class ComputeContrastReserve extends Command
{
    protected $signature = 'metrics:compute-cr {user_id} {--instrument_id=} {--location_id=} {--chunk=500} {--queued} {--lens_id=}';

    protected $description = 'Compute Contrast Reserve for a user/instrument/location and cache results in user_object_metrics';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $instrumentId = $this->option('instrument_id') ? (int) $this->option('instrument_id') : null;
        $locationId = $this->option('location_id') ? (int) $this->option('location_id') : null;
        $chunk = (int) $this->option('chunk');
        $lensId = $this->option('lens_id') ? (int) $this->option('lens_id') : null;

        $this->info("Computing CR for user {$userId} instrument={$instrumentId} location={$locationId}");

        // We'll iterate objects in chunks to avoid memory pressure
        $queued = (bool) $this->option('queued');

        // Ensure we order by a real column present on the legacy `objects` table
        // (this avoids Laravel trying to ORDER BY `objects`.`id` when no id column exists)
        DeepskyObject::query()->orderBy('name')->chunk($chunk, function ($objects) use ($userId, $instrumentId, $locationId, $queued, $lensId) {
            foreach ($objects as $obj) {
                try {
                    if ($queued) {
                        // Dispatch a job per object to compute CR asynchronously
                        ComputeContrastReserveForObject::dispatch($userId, $instrumentId, $locationId, $obj->name, $lensId);
                        continue;
                    }

                    // Synchronous path: compute inline and populate extra metadata
                    $target = new AstroTarget();
                    $d1 = is_numeric($obj->diam1) ? floatval($obj->diam1) : null;
                    $d2 = is_numeric($obj->diam2) ? floatval($obj->diam2) : null;
                    if ($d1 && $d2) {
                        $target->setDiameter($d1, $d2);
                    }

                    $m = (is_numeric($obj->mag) && floatval($obj->mag) != 99.9) ? floatval($obj->mag) : null;
                    if ($m !== null) {
                        $target->setMagnitude($m);
                    }

                    $sbobj = null;
                    try {
                        $sbobj = $target->calculateSBObj();
                    } catch (\Throwable $_) {
                        $sbobj = null;
                    }

                    if (! $instrumentId || ! $locationId) {
                        UserObjectMetric::updateOrCreate(
                            ['user_id' => $userId, 'instrument_id' => $instrumentId, 'location_id' => $locationId, 'lens_id' => $lensId, 'object_name' => $obj->name],
                            ['contrast_reserve' => null, 'contrast_reserve_category' => null, 'lens_id' => $lensId]
                        );
                        continue;
                    }

                    $user = \App\Models\User::find($userId);
                    if (! $user) {
                        continue;
                    }
                    $instrument = \App\Models\Instrument::find($instrumentId);
                    $location = \App\Models\Location::find($locationId);
                    if (! $instrument || ! $location) {
                        continue;
                    }

                    $sqm = method_exists($location, 'getSqm') ? $location->getSqm() : ($location->sqm ?? null);
                    $aperture = $instrument->aperture_mm ?? null;

                    // Determine lens factor (if lens specified) so candidate mags
                    // reflect the lens multiplier consistently.
                    $lensFactor = 1.0;
                    if (! empty($lensId)) {
                        try {
                            $ln = \App\Models\Lens::where('id', $lensId)->first();
                            if ($ln && ! empty($ln->factor) && is_numeric($ln->factor)) {
                                $lensFactor = floatval($ln->factor);
                            }
                        } catch (\Throwable $_) { /* ignore */
                        }
                    }

                    // Determine candidate magnifications
                    $possibleMags = [];
                    if (! empty($instrument->fixedMagnification)) {
                        $possibleMags[] = (int) round($instrument->fixedMagnification * $lensFactor);
                    }
                    if (! empty($instrument->focal_length_mm)) {
                        try {
                            $instSet = $user?->standardInstrumentSet ?? null;
                            if ($instSet && isset($instSet->eyepieces)) {
                                foreach ($instSet->eyepieces as $ep) {
                                    if (! empty($ep->focal_length_mm) && $ep->active) {
                                        $possibleMags[] = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                    }
                                }
                            }
                        } catch (\Throwable $_) { /* ignore */
                        }
                    }
                    if (empty($possibleMags)) {
                        try {
                            $userEps = \App\Models\Eyepiece::where('user_id', $user->id)->where('active', 1)->get();
                            foreach ($userEps as $ep) {
                                if (! empty($ep->focal_length_mm) && ! empty($instrument->focal_length_mm)) {
                                    $possibleMags[] = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                }
                            }
                        } catch (\Throwable $_) { /* ignore */
                        }
                    }

                    $possibleMags = array_values(array_unique(array_filter($possibleMags)));
                    $bestMag = null;
                    $optEps = [];
                    if (! empty($possibleMags) && $sbobj !== null && $sqm !== null && $aperture) {
                        try {
                            $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possibleMags);
                            if ($best) {
                                $bestMag = (int) $best;
                                // find eyepieces used
                                try {
                                    if (isset($instSet) && $instSet && isset($instSet->eyepieces)) {
                                        foreach ($instSet->eyepieces as $ep) {
                                            if (! empty($ep->focal_length_mm) && $instrument->focal_length_mm) {
                                                $calc = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                                if ($calc === $bestMag) {
                                                    $optEps[] = ['name' => ($ep->name ?? null), 'focal_length_mm' => $ep->focal_length_mm];
                                                }
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) { /* ignore */
                                }
                                try {
                                    $userEps = \App\Models\Eyepiece::where('user_id', $user->id)->where('active', 1)->get();
                                    foreach ($userEps as $ep) {
                                        if (! empty($ep->focal_length_mm) && $instrument->focal_length_mm) {
                                            $calc = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                            if ($calc === $bestMag) {
                                                $optEps[] = ['name' => ($ep->name ?? null), 'focal_length_mm' => $ep->focal_length_mm];
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) { /* ignore */
                                }
                            }
                        } catch (\Throwable $ex) {
                            Log::debug('ComputeContrastReserve: calculateBestMagnification failed', ['object' => $obj->name ?? null, 'error' => $ex->getMessage()]);
                        }
                    }

                    $contrast = null;
                    if ($sbobj !== null && $sqm !== null && $aperture && $bestMag) {
                        try {
                            $contrast = $target->calculateContrastReserve($sbobj, $sqm, $aperture, $bestMag);
                        } catch (\Throwable $ex) {
                            Log::debug('ComputeContrastReserve: calculateContrastReserve failed', ['object' => $obj->name ?? null, 'error' => $ex->getMessage()]);
                            $contrast = null;
                        }
                    }

                    $category = null;
                    if (is_numeric($contrast)) {
                        if ($contrast >= 3.0) {
                            $category = 'excellent';
                        } elseif ($contrast >= 1.0) {
                            $category = 'good';
                        } elseif ($contrast >= 0.5) {
                            $category = 'marginal';
                        } else {
                            $category = 'poor';
                        }
                    }

                    UserObjectMetric::updateOrCreate(
                        ['user_id' => $userId, 'instrument_id' => $instrumentId, 'location_id' => $locationId, 'lens_id' => null, 'object_name' => $obj->name],
                        [
                            'contrast_reserve' => $contrast,
                            'contrast_reserve_category' => $category,
                            'optimum_detection_magnification' => $bestMag,
                            'optimum_eyepieces' => ! empty($optEps) ? $optEps : null,
                            'lens_id' => null,
                        ]
                    );
                } catch (\Throwable $ex) {
                    // swallow to continue chunk processing, but log
                    Log::debug('ComputeContrastReserve: object compute failed', ['object' => $obj->name ?? null, 'error' => $ex->getMessage()]);
                    continue;
                }
            }
        });

        $this->info('Done computing metrics');
        return Command::SUCCESS;
    }
}
