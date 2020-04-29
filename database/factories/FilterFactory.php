<?php
/**
 * Filter Factory. Creates a filter.
 *
 * PHP Version 7
 *
 * @category Filter
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Faker\Generator as Faker;

$factory->define(
    App\Filter::class, function (Faker $faker) {
        $type = $faker->numberBetween(0, 8);

        if ($type == 6) {
            $ran = $faker->numberBetween(1, 3);
            if ($ran == 1) {
                $color = $faker->numberBetween(1, 15);
                $wratten = null;
                $schott = null;
            } elseif ($ran == 2) {
                $wratten = $faker->numberBetween(1, 50);
                $color = null;
                $schott = null;
            } else {
                $schott = $faker->numberBetween(1, 90);
                $color = null;
                $wratten = null;
            }
        } else {
            $color = null;
            $wratten = null;
            $schott = null;
        }

        return [
            'name' => $faker->sentence(3, true),
            'type' => $type,
            'color' => $color,
            'wratten' => $wratten,
            'schott' => $schott,
            'user_id' => \App\User::inRandomOrder()->first()->id,
            'active' => $faker->numberBetween(0, 1),
        ];
    }
);
