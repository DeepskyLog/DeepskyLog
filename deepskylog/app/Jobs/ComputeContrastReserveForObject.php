<?php

namespace App\Jobs;

use App\Models\DeepskyObject;
use App\Models\UserObjectMetric;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to compute and persist the contrast reserve for a single object and user/instrument/location.
 */
class ComputeContrastReserveForObject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public int $userId;
    public ?int $instrumentId;
    public ?int $locationId;
    public string $objectName;
    public ?int $lensId;

    public function __construct(int $userId, ?int $instrumentId, ?int $locationId, string $objectName, ?int $lensId = null)
    {
        $this->userId = $userId;
        $this->instrumentId = $instrumentId;
        $this->locationId = $locationId;
        $this->objectName = $objectName;
        $this->lensId = $lensId;
    }

    public function handle(): void
    {
        try {
            $obj = DeepskyObject::where('name', $this->objectName)->first();
            if (!$obj) {
                return;
            }

            $target = new AstroTarget();
            $d1 = is_numeric($obj->diam1) ? floatval($obj->diam1) : null;
            $d2 = is_numeric($obj->diam2) ? floatval($obj->diam2) : null;

            // Handle cases where only one diameter is provided - treat object as circular
            if (($d1 !== null && $d1 > 0) && (empty($d2) || $d2 <= 0)) {
                $d2 = $d1;
            } elseif (($d2 !== null && $d2 > 0) && (empty($d1) || $d1 <= 0)) {
                $d1 = $d2;
            }

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
            } catch (\Throwable $ex) {
                Log::debug('ComputeContrastReserveForObject: calculateSBObj failed', ['object' => $this->objectName, 'error' => $ex->getMessage()]);
                $sbobj = null;
            }

            $user = \App\Models\User::find($this->userId);
            $instrument = $this->instrumentId ? \App\Models\Instrument::find($this->instrumentId) : null;
            $location = $this->locationId ? \App\Models\Location::find($this->locationId) : null;

            $sqm = $location ? (method_exists($location, 'getSqm') ? $location->getSqm() : ($location->sqm ?? null)) : null;
            $aperture = $instrument->aperture_mm ?? null;

            // Determine candidate magnifications from instrument fixedMagnification or eyepieces
            $possibleMags = [];
            // Determine lens factor (if provided) so candidate mags reflect lens multiplier
            $lensFactor = 1.0;
            if (!empty($this->lensId)) {
                try {
                    $ln = \App\Models\Lens::where('id', $this->lensId)->first();
                    if ($ln && !empty($ln->factor) && is_numeric($ln->factor)) {
                        $lensFactor = floatval($ln->factor);
                    }
                } catch (\Throwable $_) { /* ignore */
                }
            }
            // For instruments with fixed magnification (binoculars, etc.), use only that value
            if ($instrument && !empty($instrument->fixedMagnification)) {
                $possibleMags[] = (int) round($instrument->fixedMagnification * $lensFactor);
            } elseif ($instrument && !empty($instrument->focal_length_mm)) {
                // For telescopes with eyepieces, calculate magnifications
                try {
                    $instSet = $user?->standardInstrumentSet ?? null;
                    if ($instSet && isset($instSet->eyepieces)) {
                        foreach ($instSet->eyepieces as $ep) {
                            if (!empty($ep->focal_length_mm) && $ep->active) {
                                $possibleMags[] = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                            }
                        }
                    }
                } catch (\Throwable $_) { /* ignore */
                }

                // Fall back to all user eyepieces if no instrument set or no eyepieces in set
                if (empty($possibleMags) && $user) {
                    try {
                        $userEps = \App\Models\Eyepiece::where('user_id', $user->id)->where('active', 1)->get();
                        foreach ($userEps as $ep) {
                            if (!empty($ep->focal_length_mm)) {
                                $possibleMags[] = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                            }
                        }
                    } catch (\Throwable $_) { /* ignore */
                    }
                }
            }

            $possibleMags = array_values(array_unique(array_filter($possibleMags)));
            $bestMag = null;
            $optEps = [];
            if (!empty($possibleMags) && $sbobj !== null && $sqm !== null && $aperture) {
                try {
                    $best = $target->calculateBestMagnification($sbobj, $sqm, $aperture, $possibleMags);
                    if ($best) {
                        $bestMag = (int) $best;
                        // Map best mag back to eyepieces used
                        foreach ($possibleMags as $pm) {
                            if ($pm === $bestMag) {
                                // find eyepiece(s) that produced this mag
                                // search instrument set first
                                try {
                                    if (isset($instSet) && $instSet && isset($instSet->eyepieces)) {
                                        foreach ($instSet->eyepieces as $ep) {
                                            if (!empty($ep->focal_length_mm) && $instrument->focal_length_mm) {
                                                $calc = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                                if ($calc === $bestMag) {
                                                    $optEps[] = ['name' => ($ep->name ?? null), 'focal_length_mm' => $ep->focal_length_mm];
                                                }
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) { /* ignore */
                                }
                                // fallback to user's eyepieces
                                try {
                                    $userEps = \App\Models\Eyepiece::where('user_id', $this->userId)->where('active', 1)->get();
                                    foreach ($userEps as $ep) {
                                        if (!empty($ep->focal_length_mm) && $instrument->focal_length_mm) {
                                            $calc = (int) round(($instrument->focal_length_mm / $ep->focal_length_mm) * $lensFactor);
                                            if ($calc === $bestMag) {
                                                $optEps[] = ['name' => ($ep->name ?? null), 'focal_length_mm' => $ep->focal_length_mm];
                                            }
                                        }
                                    }
                                } catch (\Throwable $_) { /* ignore */
                                }
                                break;
                            }
                        }
                    }
                } catch (\Throwable $ex) {
                    Log::debug('ComputeContrastReserveForObject: calculateBestMagnification failed', ['object' => $this->objectName, 'error' => $ex->getMessage()]);
                }
            }

            $contrast = null;
            if ($sbobj !== null && $sqm !== null && $aperture && $bestMag) {
                try {
                    $contrast = $target->calculateContrastReserve($sbobj, $sqm, $aperture, $bestMag);
                } catch (\Throwable $ex) {
                    Log::debug('ComputeContrastReserveForObject: calculateContrastReserve failed', ['object' => $this->objectName, 'error' => $ex->getMessage()]);
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
                ['user_id' => $this->userId, 'instrument_id' => $this->instrumentId, 'location_id' => $this->locationId, 'lens_id' => $this->lensId, 'object_name' => $this->objectName],
                [
                    'contrast_reserve' => $contrast,
                    'contrast_reserve_category' => $category,
                    'optimum_detection_magnification' => $bestMag,
                    'optimum_eyepieces' => !empty($optEps) ? $optEps : null,
                    'lens_id' => $this->lensId,
                ]
            );
        } catch (\Throwable $ex) {
            Log::debug('ComputeContrastReserveForObject: job failed', ['object' => $this->objectName, 'error' => $ex->getMessage()]);
        }
    }
}
