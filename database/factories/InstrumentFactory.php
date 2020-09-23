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

namespace Database\Factories;

use App\Models\Instrument;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstrumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Instrument::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $typeNumber = $this->faker->numberBetween(0, 9);

        if ($typeNumber == 0) {
            $fd = null;
            $diameter = $this->faker->numberBetween(2, 8);
            $fixedMagnification = 1;
        } elseif ($typeNumber == 1 || $typeNumber == 4) {
            $fixed = $this->faker->numberBetween(0, 1);
            if ($fixed) {
                $fixedMagnification = $this->faker->numberBetween(5, 50);
                $fd = null;
                $diameter = $this->faker->numberBetween(25, 200);
            } else {
                $fixedMagnification = null;
                $fd = $this->faker->numberBetween(2, 15);
                $diameter = $this->faker->numberBetween(25, 200);
            }
        } else {
            $fixedMagnification = null;
            $fd = $this->faker->numberBetween(2, 30);
            $diameter = $this->faker->numberBetween(25, 2000);
        }

        return [
            'name' => $this->faker->sentence(3, true),
            'diameter' => $diameter,
            'fd' => $fd,
            'type' => $typeNumber,
            'fixedMagnification' => $fixedMagnification,
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'active' => $this->faker->numberBetween(0, 1),
        ];
    }
}
