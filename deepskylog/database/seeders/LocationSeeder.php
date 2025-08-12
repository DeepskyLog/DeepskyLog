<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\LocationsOld;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locationData = LocationsOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('locations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $date = date('Y-m-d H:i:s');

        foreach ($locationData as $location) {
            $observer = User::where('username', html_entity_decode($location->observer))->pluck('id');

            if (count($observer) > 0) {
                if ($location->timestamp == '') {
                    $date = date('Y-m-d H:i:s');
                } else {
                    [$year, $month, $day, $hour, $minute, $second]
                        = sscanf($location->timestamp, '%4d%2d%2d%2d%2d%d');
                    $date = date(
                        'Y-m-d H:i:s',
                        mktime($hour, $minute, $second, $month, $day, $year)
                    );
                }

                Location::create(
                    [
                        'id' => $location->id,
                        'name' => html_entity_decode($location->name),
                        'longitude' => $location->longitude,
                        'latitude' => $location->latitude,
                        'timezone' => $location->timezone,
                        'limitingMagnitude' => $location->limitingMagnitude,
                        'skyBackground' => $location->skyBackground,
                        'elevation' => $location->elevation,
                        'country' => $location->country,
                        'active' => $location->locationactive,
                        'user_id' => $observer[0],
                        'observer' => $location->observer,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]
                );
            }
        }

    }
}
