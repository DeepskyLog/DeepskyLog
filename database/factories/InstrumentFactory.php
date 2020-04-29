<?php
/**
 * Instrument Factory. Creates an instrument.
 *
 * PHP Version 7
 *
 * @category Instruments
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Faker\Generator as Faker;

$factory->define(
    App\Instrument::class, function (Faker $faker) {
        $typeNumber = $faker->numberBetween(0, 9);

        if ($typeNumber == 0) {
            $fd = null;
            $diameter = $faker->numberBetween(2, 8);
            $fixedMagnification = 1;
        } elseif ($typeNumber == 1 || $typeNumber == 4) {
            $fixed = $faker->numberBetween(0, 1);
            if ($fixed) {
                $fixedMagnification = $faker->numberBetween(5, 50);
                $fd = null;
                $diameter = $faker->numberBetween(25, 200);
            } else {
                $fixedMagnification = null;
                $fd = $faker->numberBetween(2, 15);
                $diameter = $faker->numberBetween(25, 200);
            }
        } else {
            $fixedMagnification = null;
            $fd = $faker->numberBetween(2, 30);
            $diameter = $faker->numberBetween(25, 2000);
        }

        return [
            'name' => $faker->sentence(3, true),
            'diameter' => $diameter,
            'fd' => $fd,
            'type' => $typeNumber,
            'fixedMagnification' => $fixedMagnification,
            'user_id' => \App\User::inRandomOrder()->first()->id,
            'active' => $faker->numberBetween(0, 1),
        ];
    }
);
