<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanetaryMoonsSeeder extends Seeder
{
    public function run()
    {
        $now = now()->toDateTimeString();

        $moons = [
            // Earth
            ['objectname' => 'Moon', 'partofname' => 'Earth', 'timestamp' => $now],

            // Mars
            ['objectname' => 'Phobos', 'partofname' => 'Mars', 'timestamp' => $now],
            ['objectname' => 'Deimos', 'partofname' => 'Mars', 'timestamp' => $now],

            // Jupiter (major Galilean moons)
            ['objectname' => 'Io', 'partofname' => 'Jupiter', 'timestamp' => $now],
            ['objectname' => 'Europa', 'partofname' => 'Jupiter', 'timestamp' => $now],
            ['objectname' => 'Ganymede', 'partofname' => 'Jupiter', 'timestamp' => $now],
            ['objectname' => 'Callisto', 'partofname' => 'Jupiter', 'timestamp' => $now],

            // Saturn (major moons)
            ['objectname' => 'Mimas', 'partofname' => 'Saturn', 'timestamp' => $now],
            ['objectname' => 'Enceladus', 'partofname' => 'Saturn', 'timestamp' => $now],
            ['objectname' => 'Tethys', 'partofname' => 'Saturn', 'timestamp' => $now],
            ['objectname' => 'Dione', 'partofname' => 'Saturn', 'timestamp' => $now],
            ['objectname' => 'Rhea', 'partofname' => 'Saturn', 'timestamp' => $now],
            ['objectname' => 'Titan', 'partofname' => 'Saturn', 'timestamp' => $now],

            // Uranus
            ['objectname' => 'Ariel', 'partofname' => 'Uranus', 'timestamp' => $now],
            ['objectname' => 'Umbriel', 'partofname' => 'Uranus', 'timestamp' => $now],
            ['objectname' => 'Titania', 'partofname' => 'Uranus', 'timestamp' => $now],
            ['objectname' => 'Oberon', 'partofname' => 'Uranus', 'timestamp' => $now],

            // Neptune
            ['objectname' => 'Triton', 'partofname' => 'Neptune', 'timestamp' => $now],
        ];

        // Insert using insertOrIgnore to avoid duplicates on re-runs
        $batches = array_chunk($moons, 50);
        foreach ($batches as $batch) {
            DB::table('objectpartof')->insertOrIgnore($batch);
        }
    }
}
