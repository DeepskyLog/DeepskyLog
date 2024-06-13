<?php

namespace Database\Seeders;

use App\Models\SketchOfTheMonth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SketchOfTheMonthSeeder extends Seeder
{
    // Static list of ids for the DeepskyLog sketch of the month
    protected static array $sketchOfTheMonth = [
        44239 => 20240608, 138098 => 20240508, 175079 => 20240308, 136494 => 20240208, 173323 => 20240108,
        119453 => 20231208, 78913 => 20231107,
    ];

    public function run(): void
    {
        // Loop over all elements in the sketchOfTheMonth array
        foreach (SketchOfTheMonthSeeder::$sketchOfTheMonth as $observationId => $date) {
            // Read year, month and day from $date
            $year = substr($date, 0, 4);
            $month = substr($date, 4, 2);
            $day = substr($date, 6, 2);

            // Put the year, month and day in a date object
            $date = Carbon::create($year, $month, $day, 0, 0, 0, 'UTC');

            SketchOfTheMonth::create([
                'observation_id' => $observationId,
                'date' => $date,
            ]);
        }

    }
}