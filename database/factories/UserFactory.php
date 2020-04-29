<?php

/**
 * User Factory. Creates a verified user.
 *
 * PHP Version 7
 *
 * @category Database
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(
    App\User::class,
    function (Faker $faker) {
        $copyright = ['Attribution CC BY', 'Attribution-ShareAlike CC BY-SA',
            'Attribution-NoDerivs CC BY-ND', 'Attribution-NonCommercial CC BY-NC',
            'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
            'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
            'No license (Not recommended!)', 'Enter your own copyright text',
        ];

        return [
            'username' => $faker->firstName,
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'country' => $faker->countryCode,
            'copyright' => $faker->randomElement($copyright),
            'password' => 'secret',
            'remember_token' => Str::random(10),
            'language' => 'en_US',
            'type' => 'default',
        ];
    }
);
