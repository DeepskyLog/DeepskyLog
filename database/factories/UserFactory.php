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

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $copyright = ['Attribution CC BY', 'Attribution-ShareAlike CC BY-SA',
            'Attribution-NoDerivs CC BY-ND', 'Attribution-NonCommercial CC BY-NC',
            'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
            'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
            'No license (Not recommended!)', 'Enter your own copyright text',
        ];

        return [
            'username' => $this->faker->firstName,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'country' => $this->faker->countryCode,
            'copyright' => $this->faker->randomElement($copyright),
            'password' => 'secret',
            'remember_token' => Str::random(10),
            'language' => 'en_US',
            'type' => 'default',
        ];
    }
}
