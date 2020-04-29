<?php

use App\Location;
use App\LocationOld;
use App\User;
use Illuminate\Database\Seeder;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $locationData = LocationOld::all();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('locations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($locationData as $location) {
            $observer = User::where('username', $location->observer)->pluck('id');
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

                // We convert from englisch strings to country codes.
                if ($location->country === 'Belgium') {
                    $country = 'BE';
                } elseif ($location->country === 'Chile') {
                    $country = 'CL';
                } elseif ($location->country === 'Spain') {
                    $country = 'ES';
                } elseif ($location->country === 'Netherlands') {
                    $country = 'NL';
                } elseif ($location->country === 'France') {
                    $country = 'FR';
                } elseif ($location->country === 'United States') {
                    $country = 'US';
                } elseif ($location->country === 'Germany') {
                    $country = 'DE';
                } elseif ($location->country === 'Romania') {
                    $country = 'RO';
                } elseif ($location->country === 'Cape Verde') {
                    $country = 'CV';
                } elseif ($location->country === 'Portugal') {
                    $country = 'PT';
                } elseif ($location->country === 'Argentina') {
                    $country = 'AR';
                } elseif ($location->country === 'Switzerland') {
                    $country = 'CH';
                } elseif ($location->country === 'Australia') {
                    $country = 'AU';
                } elseif ($location->country === 'Sweden') {
                    $country = 'SE';
                } elseif ($location->country === 'Croatia') {
                    $country = 'HR';
                } elseif ($location->country === 'Russia') {
                    $country = 'RU';
                } elseif ($location->country === 'Italy') {
                    $country = 'IT';
                } elseif ($location->country === 'Israel') {
                    $country = 'IL';
                } elseif ($location->country === 'United Kingdom') {
                    $country = 'GB';
                } elseif ($location->country === 'Austria') {
                    $country = 'AT';
                } elseif ($location->country === 'Poland') {
                    $country = 'PL';
                } elseif ($location->country === 'Sri Lanka') {
                    $country = 'LK';
                } elseif ($location->country === 'Czechia' || $location->country === 'Czech Republic') {
                    $country = 'CZ';
                } elseif ($location->country === 'Namibia') {
                    $country = 'NA';
                } elseif ($location->country === 'Greece') {
                    $country = 'GR';
                } elseif ($location->country === 'Norway') {
                    $country = 'NO';
                } elseif ($location->country === 'Morocco') {
                    $country = 'MA';
                } elseif ($location->country === 'New Zealand') {
                    $country = 'NZ';
                } elseif ($location->country === 'Denmark') {
                    $country = 'DK';
                } elseif ($location->country === 'Mexico') {
                    $country = 'MX';
                } elseif ($location->country === 'Costa Rica') {
                    $country = 'CR';
                } elseif ($location->country === 'Madagaskar') {
                    $country = 'MG';
                } elseif ($location->country === 'Hungary') {
                    $country = 'HU';
                } elseif ($location->country === 'South Africa' || $location->country === 'South africa') {
                    $country = 'ZA';
                } elseif ($location->country === 'Bolivia') {
                    $country = 'BO';
                } elseif ($location->country === 'Iceland') {
                    $country = 'IS';
                } elseif ($location->country === 'Slovenia') {
                    $country = 'SI';
                } elseif ($location->country === 'Cyprus') {
                    $country = 'CY';
                } elseif ($location->country === 'Curaçao') {
                    $country = 'CW';
                } elseif ($location->country === 'Brazil') {
                    $country = 'BR';
                } elseif ($location->country === 'Finland') {
                    $country = 'FI';
                } elseif ($location->country === 'Libya') {
                    $country = 'LY';
                } elseif ($location->country === 'Egypt') {
                    $country = 'EG';
                } elseif ($location->country === 'Turkey') {
                    $country = 'TR';
                } elseif ($location->country === 'India') {
                    $country = 'IN';
                } elseif ($location->country === 'Serbia and montenegro') {
                    $country = 'RS';
                } elseif ($location->country === 'Canada') {
                    $country = 'CA';
                } elseif ($location->country === 'China') {
                    $country = 'CN';
                } elseif ($location->country === 'Kenya') {
                    $country = 'KE';
                } elseif ($location->country === 'Ethiopia') {
                    $country = 'ET';
                } elseif ($location->country === 'Netherlands antilles') {
                    $country = 'AN';
                } elseif ($location->country === 'Oman') {
                    $country = 'OM';
                } elseif ($location->country === 'Afghanistan') {
                    $country = 'AF';
                } elseif ($location->country === 'Maldives') {
                    $country = 'MV';
                } elseif ($location->country === 'Zimbabwe') {
                    $country = 'ZW';
                } elseif ($location->country === 'Zambia') {
                    $country = 'ZM';
                } elseif ($location->country === 'Malaysia') {
                    $country = 'MY';
                } else {
                    echo 'MISSING COUNTRY: '.$location->id.' - '.$location->name.' - '.$location->country."\n";
                    continue;
                }

                $sqm = $location->skyBackground;
                if ($sqm < 0) {
                    $sqm = null;
                }
                $lm = $location->limitingMagnitude;

                if ($lm < 0) {
                    $lm = null;
                }

                $bortle = null;

                // Calculate the sqm from the lm
                if ($lm > 0) {
                    $sqm = Location::getSqmFromLimitingMagnitude($lm);
                    $bortle = Location::getBortleFromSqm($sqm);
                } elseif ($sqm > 0) {
                    $lm = Location::getLimitingMagnitudeFromSqm($sqm);
                    $bortle = Location::getBortleFromSqm($sqm);
                }

                $newLocation = Location::create(
                    [
                        'id' => $location->id,
                        'name' => html_entity_decode($location->name),
                        'longitude' => $location->longitude,
                        'latitude' => $location->latitude,
                        'elevation' => $location->elevation,
                        'country' => $country,
                        'timezone' => $location->timezone,
                        'user_id' => $observer[0],
                        'limitingMagnitude' => $lm,
                        'skyBackground' => $sqm,
                        'bortle' => $bortle,
                        'active' => $location->locationactive,
                        'created_at' => $date,
                    ]
                );
            }
        }
    }
}
