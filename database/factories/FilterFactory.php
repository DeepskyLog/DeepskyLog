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

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Filter;

class FilterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Filter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = $this->faker->numberBetween(0, 8);
        if ($type == 6) {
            $ran = $this->faker->numberBetween(1, 3);
            if ($ran == 1) {
                $color = $this->faker->numberBetween(1, 15);
                $wratten = null;
                $schott = null;
            } elseif ($ran == 2) {
                $wratten = $this->faker->numberBetween(1, 50);
                $color = null;
                $schott = null;
            } else {
                $schott = $this->faker->numberBetween(1, 90);
                $color = null;
                $wratten = null;
            }
        } else {
            $color = null;
            $wratten = null;
            $schott = null;
        }

        return [
            'name' => $this->faker->sentence(3, true),
            'type' => $type,
            'color' => $color,
            'wratten' => $wratten,
            'schott' => $schott,
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'active' => $this->faker->numberBetween(0, 1),
        ];
    }
}
