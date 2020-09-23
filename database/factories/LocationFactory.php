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

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3, true),
            'longitude' => $this->faker->longitude(),
            'latitude' => $this->faker->latitude(),
            'elevation' => $this->faker->numberBetween(0, 4000),
            'country' => $this->faker->countryCode(),
            'timezone' => $this->faker->timezone(),
            'limitingMagnitude' => $this->faker->randomFloat(1, 3.5, 7.0),
            'skyBackground' => $this->faker->randomFloat(2, 18.0, 22.0),
            'bortle' => $this->faker->numberBetween(1, 9),
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'active' => $this->faker->numberBetween(0, 1),
        ];
    }
}
