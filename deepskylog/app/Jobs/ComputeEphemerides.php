<?php

namespace App\Jobs;

use App\Services\EphemeridesBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ComputeEphemerides implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $objectIdentifier;
    public string $dateIso;
    public ?int $locationId;

    public function __construct(string $objectIdentifier, string $dateIso, ?int $locationId = null)
    {
        $this->objectIdentifier = $objectIdentifier;
        $this->dateIso = $dateIso;
        $this->locationId = $locationId;
    }

    /**
     * The cache key the ephemerides are stored under. ObjectEphemerides looks
     * them up (and polls for them) with the same key.
     */
    public static function cacheKey(string $objectIdentifier, string $dateIso, ?int $locationId): string
    {
        return 'ephemerides:' . md5($objectIdentifier . '|' . $dateIso . '|' . ($locationId ?? '0'));
    }

    public function handle(): void
    {
        // Resolve object record: try Target, then CometObject, then fallback to name
        $obj = null;
        try {
            if (is_numeric((string) $this->objectIdentifier)) {
                $obj = \App\Models\Target::find((int) $this->objectIdentifier) ?? \App\Models\CometObject::find((int) $this->objectIdentifier);
            }
            if (!$obj) {
                $searchVal = trim((string) $this->objectIdentifier);
                $obj = \App\Models\Target::where('slug', $searchVal)->orWhere('name', $searchVal)->first() ?? \App\Models\CometObject::where('slug', $searchVal)->orWhere('name', $searchVal)->first();
            }
        } catch (\Throwable $_) {
            $obj = null;
        }

        // Determine location
        $userLocation = null;
        if ($this->locationId) {
            try {
                $userLocation = \App\Models\Location::find($this->locationId);
            } catch (\Throwable $_) {
                $userLocation = null;
            }
        }
        if (!$userLocation) {
            try {
                $userLocation = \App\Models\Location::forEphemerides();
            } catch (\Throwable $_) {
                $userLocation = null;
            }
        }

        $date = \App\Support\EphemerisDate::parse($this->dateIso, \App\Support\EphemerisDate::timezone($userLocation));
        $geo_coords = new \deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates($userLocation->longitude ?? 0.0, $userLocation->latitude ?? 0.0);

        $ephem = EphemeridesBuilder::compute($obj ?? (object) ['name' => $this->objectIdentifier, 'designation' => $this->objectIdentifier], $date, $userLocation, $geo_coords);

        Cache::put(self::cacheKey($this->objectIdentifier, $this->dateIso, $this->locationId), $ephem, now()->addHours(6));
    }
}
