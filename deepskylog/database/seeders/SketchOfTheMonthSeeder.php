<?php

namespace Database\Seeders;

use App\Models\Observation;
use App\Models\SketchOfTheMonth;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SketchOfTheMonthSeeder extends Seeder
{
    // Static list of ids for the DeepskyLog sketch of the month
    protected static array $sketchOfTheMonth = [
        123894 => 20250307,
        168877 => 20250208,
        157675 => 20250108,
        169382 => 20241208,
        193025 => 20241108,
        128492 => 20241008,
        168825 => 20240908,
        192264 => 20240808,
        128545 => 20240708,
        44239 => 20240608,
        138098 => 20240508,
        175079 => 20240308,
        136494 => 20240208,
        173323 => 20240108,
        119453 => 20231208,
        78913 => 20231107,
    ];

    public function run(): void
    {
        // Loop over all elements in the sketchOfTheMonth array
        foreach (SketchOfTheMonthSeeder::$sketchOfTheMonth as $legacyObservationId => $date) {
            // Read year, month and day from $date
            $year = substr($date, 0, 4);
            $month = substr($date, 4, 2);
            $day = substr($date, 6, 2);

            // Put the year, month and day in a date object
            $date = Carbon::create($year, $month, $day, 0, 0, 0, 'UTC');

            $legacySource = $legacyObservationId < 0 ? 'comet' : 'deepsky';
            $legacyId = abs((int) $legacyObservationId);

            $observation = Observation::where('legacy_source', $legacySource)
                ->where('legacy_id', $legacyId)
                ->first();
            if (!$observation) {
                continue;
            }

            $user = User::where('username', html_entity_decode((string) $observation->observer_username))->first();
            if (!$user) {
                continue;
            }

            SketchOfTheMonth::create([
                'observation_id' => $observation->id,
                'user_id' => $user->id,
                'date' => $date,
            ]);
        }

    }
}
