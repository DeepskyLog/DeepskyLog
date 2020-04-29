<?php
/**
 * Lens Factory. Creates a lens.
 *
 * PHP Version 7
 *
 * @category Lenses
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Faker\Generator as Faker;

$factory->define(
    App\Lens::class,
    function (Faker $faker) {
        return [
            'name' => $faker->sentence(3, true),
            'factor' => $faker->randomFloat(2, 0.1, 5.0),
            'user_id' => \App\User::inRandomOrder()->first()->id,
            'active' => $faker->numberBetween(0, 1),
        ];
    }
);
