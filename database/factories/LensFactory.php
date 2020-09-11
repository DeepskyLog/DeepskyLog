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

namespace Database\Factories;

use App\Models\Lens;
use Illuminate\Database\Eloquent\Factories\Factory;

class LensFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lens::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3, true),
            'factor' => $this->faker->randomFloat(2, 0.1, 5.0),
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'active' => $this->faker->numberBetween(0, 1),
        ];
    }
}
