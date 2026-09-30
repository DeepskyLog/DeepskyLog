<?php

namespace App\Jobs;

use App\Models\DeepskyObject;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use App\Models\UserObjectMetric;
use App\Services\ContrastReserveCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        // Exceptions are not caught: the queue then tries the job again ($tries)
        // and marks it as failed, and the batch counts it as a failure.
        $obj = DeepskyObject::where('name', $this->objectName)->first();
        if (!$obj) {
            return;
        }

        UserObjectMetric::updateOrCreate(
            ['user_id' => $this->userId, 'instrument_id' => $this->instrumentId, 'location_id' => $this->locationId, 'lens_id' => $this->lensId, 'object_name' => $this->objectName],
            self::metricValues($obj, $this->userId, $this->instrumentId, $this->locationId, $this->lensId)
        );
    }

    /**
     * The values stored in user_object_metrics for the object with this
     * equipment, as calculated for the preview by ContrastReserveCalculator.
     *
     * @return array<string, mixed>
     */
    public static function metricValues(DeepskyObject $obj, int $userId, ?int $instrumentId, ?int $locationId, ?int $lensId): array
    {
        return self::valuesFor(
            $obj,
            User::find($userId),
            $instrumentId ? Instrument::find($instrumentId) : null,
            $locationId ? Location::find($locationId) : null,
            $lensId
        );
    }

    /**
     * metricValues() with the equipment already loaded, for jobs that compute
     * many objects with the same equipment.
     *
     * @param  array<int, list<\App\Models\Eyepiece>>|null  $magnifications  from ContrastReserveCalculator::magnifications()
     * @return array<string, mixed>
     */
    public static function valuesFor(DeepskyObject $obj, ?User $user, ?Instrument $instrument, ?Location $location, ?int $lensId, ?array $magnifications = null): array
    {
        $result = ['contrast_reserve' => null, 'category' => null, 'magnification' => null, 'eyepieces' => []];
        if ($instrument && $location) {
            $result = ContrastReserveCalculator::calculate(
                ContrastReserveCalculator::target($obj->diam1, $obj->diam2, $obj->mag),
                $instrument,
                $location,
                $user,
                // the lens only changes the magnifications
                $magnifications === null ? ContrastReserveCalculator::lensFactor($lensId) : 1.0,
                $magnifications
            );
        }

        $eyepieces = array_map(fn ($ep) => ['name' => $ep->name ?? null, 'focal_length_mm' => $ep->focal_length_mm], $result['eyepieces']);

        return [
            'contrast_reserve' => $result['contrast_reserve'],
            'contrast_reserve_category' => $result['category'],
            'optimum_detection_magnification' => $result['magnification'],
            'optimum_eyepieces' => $eyepieces ?: null,
            'lens_id' => $lensId,
        ];
    }
}
