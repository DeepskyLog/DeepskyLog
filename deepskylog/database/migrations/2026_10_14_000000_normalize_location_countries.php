<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Renames the countries of the locations to the canonical names of the
 * countries table. The reverse geocoder stored current short names
 * ('Czechia', 'United Kingdom', 'Russia') next to the names of the old site
 * ('Czech Republic'), so the statistics listed one country twice.
 */
return new class extends Migration
{
    /**
     * Former countries: a location gets the successor with the nearest
     * reference point (latitude, longitude) of its towns or islands.
     */
    private const FORMER_COUNTRIES = [
        'Netherlands antilles' => [
            'Curaçao' => [[12.17, -68.98]],
            'Bonaire, Sint Eustatius and Saba' => [[12.18, -68.25], [17.49, -62.97], [17.63, -63.24]],
            'Sint Maarten (Dutch part)' => [[18.04, -63.05]],
        ],
        'Serbia and montenegro' => [
            'Serbia' => [[44.80, 20.46], [45.25, 19.85], [43.32, 21.90], [43.86, 19.84], [43.14, 20.51], [42.66, 21.17]],
            'Montenegro' => [[42.44, 19.26], [42.77, 18.94], [43.36, 19.36], [42.29, 18.84], [42.84, 19.87]],
        ],
    ];

    public function up(): void
    {
        // Seru Largu on Bonaire was stored at 66.27° W, at sea 220 km east of
        // the island: it is at 68.27° W.
        DB::table('locations')
            ->where('name', 'Seru Largu')
            ->whereBetween('longitude', [-66.27, -66.26])
            ->update(['longitude' => -68.2667]);

        foreach (self::FORMER_COUNTRIES as $former => $successors) {
            $locations = DB::table('locations')->where('country', $former)->get(['id', 'latitude', 'longitude']);
            foreach ($locations as $location) {
                DB::table('locations')
                    ->where('id', $location->id)
                    ->update(['country' => $this->nearestSuccessor($successors, (float) $location->latitude, (float) $location->longitude)]);
            }
        }

        $renamed = [];
        foreach (DB::table('locations')->whereNotNull('country')->distinct()->pluck('country') as $country) {
            $canonical = Country::canonicalName($country);
            if ($canonical !== $country) {
                DB::table('locations')->where('country', $country)->update(['country' => $canonical]);
                $renamed[] = $country;
            }
        }

        // The country list and counts of /statistics are cached for a day.
        Cache::forget('statistics:countries');
        foreach (['all', ...$renamed] as $country) {
            Cache::forget('statistics:counts:'.$country);
        }
    }

    /**
     * @param  array<string, list<array{float, float}>>  $successors
     */
    private function nearestSuccessor(array $successors, float $latitude, float $longitude): string
    {
        $nearest = null;
        $nearestDistance = INF;
        foreach ($successors as $country => $points) {
            foreach ($points as [$pointLatitude, $pointLongitude]) {
                // Degrees of longitude are shorter away from the equator.
                $distance = hypot(
                    $latitude - $pointLatitude,
                    ($longitude - $pointLongitude) * cos(deg2rad($latitude))
                );
                if ($distance < $nearestDistance) {
                    $nearest = $country;
                    $nearestDistance = $distance;
                }
            }
        }

        return $nearest;
    }

    public function down(): void
    {
        // The old names are not kept.
    }
};
