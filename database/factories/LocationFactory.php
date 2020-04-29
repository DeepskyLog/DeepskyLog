<?php
/**
 * Location Factory. Creates a location.
 *
 * PHP Version 7
 *
 * @category Locations
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Faker\Generator as Faker;

$factory->define(
    App\Location::class, function (Faker $faker) {
        return [
            'name' => $faker->sentence(3, true),
            'longitude' => $faker->longitude(),
            'latitude' => $faker->latitude(),
            'elevation' => $faker->numberBetween(0, 4000),
            'country' => $faker->countryCode(),
            'timezone' => $faker->timezone(),
            'limitingMagnitude' => $faker->randomFloat(1, 3.5, 7.0),
            'skyBackground' => $faker->randomFloat(2, 18.0, 22.0),
            'bortle' => $faker->numberBetween(1, 9),
            'user_id' => \App\User::inRandomOrder()->first()->id,
            'active' => $faker->numberBetween(0, 1),
        ];
    }
);
