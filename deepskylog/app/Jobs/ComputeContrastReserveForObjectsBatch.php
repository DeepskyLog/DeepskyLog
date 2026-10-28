<?php

namespace App\Jobs;

use App\Models\DeepskyObject;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use App\Services\ObjectMetricsQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Computes and stores the metrics (user_object_metrics) of several objects
 * with the same equipment, for the objects that have none yet, or for all of
 * them with $recompute. The equipment and the magnifications are loaded once,
 * the rows written with one insert per chunk. Queued by ObjectMetricsQueue,
 * for the object tables and after a change of the standard equipment.
 */
class ComputeContrastReserveForObjectsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    public int $userId;
    public ?int $instrumentId;
    public ?int $locationId;
    public array $objectNames;
    public ?int $lensId;
    public ?string $token;
    public bool $recompute;

    public function __construct(int $userId, ?int $instrumentId, ?int $locationId, array $objectNames, ?int $lensId = null, ?string $token = null, bool $recompute = false)
    {
        $this->userId = $userId;
        $this->instrumentId = $instrumentId;
        $this->locationId = $locationId;
        $this->objectNames = $objectNames;
        $this->lensId = $lensId;
        $this->token = $token;
        $this->recompute = $recompute;
    }

    public function handle(): void
    {
        try {
            $this->compute();
        } finally {
            if ($this->token !== null) {
                ObjectMetricsQueue::jobDone($this->token);
            }
        }
    }

    private function compute(): void
    {
        $user = User::find($this->userId);
        $instrument = $this->instrumentId ? Instrument::find($this->instrumentId) : null;
        $location = $this->locationId ? Location::find($this->locationId) : null;
        if (!$user || !$instrument || !$location) {
            return;
        }

        // Computed meanwhile (by another table or job): not again, unless the
        // stored values are to be replaced.
        $missing = $this->recompute
            ? $this->objectNames
            : ObjectMetricsQueue::missing($this->userId, $this->instrumentId, $this->locationId, $this->lensId, $this->objectNames);
        if ($missing === []) {
            return;
        }

        $magnifications = ContrastReserveCalculator::magnifications($instrument, $user, ContrastReserveCalculator::lensFactor($this->lensId));
        $now = now();

        foreach (array_chunk($missing, 500) as $chunk) {
            $rows = [];
            foreach (DeepskyObject::whereIn('name', $chunk)->get()->unique('name') as $object) {
                $values = ComputeContrastReserveForObject::valuesFor($object, $user, $instrument, $location, $this->lensId, $magnifications);
                $rows[] = [
                    'user_id' => $this->userId,
                    'instrument_id' => $this->instrumentId,
                    'location_id' => $this->locationId,
                    'lens_id' => $this->lensId,
                    'object_name' => $object->name,
                    'contrast_reserve' => $values['contrast_reserve'],
                    'contrast_reserve_category' => $values['contrast_reserve_category'],
                    'optimum_detection_magnification' => $values['optimum_detection_magnification'],
                    // insert() does not apply the model's array cast
                    'optimum_eyepieces' => $values['optimum_eyepieces'] === null ? null : json_encode($values['optimum_eyepieces']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($this->recompute) {
                // Replaces the stored rows. Not upsert(): the table has no unique
                // key to match on, and a NULL lens_id would never match anyway.
                DB::transaction(function () use ($chunk, $rows) {
                    DB::table('user_object_metrics')
                        ->where('user_id', $this->userId)
                        ->where('instrument_id', $this->instrumentId)
                        ->where('location_id', $this->locationId)
                        ->when($this->lensId === null, fn ($query) => $query->whereNull('lens_id'), fn ($query) => $query->where('lens_id', $this->lensId))
                        ->whereIn('object_name', $chunk)
                        ->delete();
                    if ($rows !== []) {
                        DB::table('user_object_metrics')->insert($rows);
                    }
                });
            } elseif ($rows !== []) {
                DB::table('user_object_metrics')->insert($rows);
            }
        }
    }
}
