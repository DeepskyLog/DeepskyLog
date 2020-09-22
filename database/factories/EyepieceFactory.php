<?php
/**
 * Eyepiece Factory. Creates an eyepiece.
 *
 * PHP Version 7
 *
 * @category Eyepieces
 * @author   Wim De Meester <deepskywim@gmail.com>
 * @license  GPL3 <https://opensource.org/licenses/GPL-3.0>
 * @link     http://www.deepskylog.org
 */

namespace Database\Factories;

use App\Models\Eyepiece;
use Illuminate\Database\Eloquent\Factories\Factory;

class EyepieceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Eyepiece::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $brandNumber = $this->faker->numberBetween(1, 7);

        if ($brandNumber == 1) {
            $brand = 'Televue';
            $typeNumber = $this->faker->numberBetween(1, 8);
            switch ($typeNumber) {
            case 1:
                $type = 'Plössl';
                break;
            case 2:
                $type = 'Panoptic';
                break;
            case 3:
                $type = 'Nagler';
                break;
            case 4:
                $type = 'Radian';
                break;
            case 5:
                $type = 'Apollo';
                break;
            case 6:
                $type = 'Ethos';
                break;
            case 7:
                $type = 'DeLite';
                break;
            case 8:
                $type = 'Delos';
                break;
            }
        } elseif ($brandNumber == 2) {
            $brand = 'Baader';
            $typeNumber = $this->faker->numberBetween(1, 2);
            switch ($typeNumber) {
            case 1:
                $type = 'Hyperion';
                break;
            case 2:
                $type = 'Morpheus';
                break;
            }
        } elseif ($brandNumber == 3) {
            $brand = 'Meade';
            $typeNumber = $this->faker->numberBetween(1, 2);
            switch ($typeNumber) {
            case 1:
                $type = 'Super Plössl';
                break;
            case 2:
                $type = 'MWA';
                break;
            }
        } elseif ($brandNumber == 4) {
            $brand = 'University Optics';
            $typeNumber = $this->faker->numberBetween(1, 6);
            switch ($typeNumber) {
            case 1:
                $type = 'Ortho';
                break;
            case 2:
                $type = 'König';
                break;
            case 3:
                $type = 'Wide Scan';
                break;
            case 4:
                $type = 'K';
                break;
            case 5:
                $type = 'ER';
                break;
            case 6:
                $type = 'UW';
                break;
            }
        } elseif ($brandNumber == 5) {
            $brand = 'Pentax';
            $typeNumber = $this->faker->numberBetween(1, 3);
            switch ($typeNumber) {
            case 1:
                $type = 'SMC XW';
                break;
            case 2:
                $type = 'SMC XL';
                break;
            case 3:
                $type = 'SMC XF';
                break;
            }
        } elseif ($brandNumber == 6) {
            $brand = 'Celestron';
            $typeNumber = $this->faker->numberBetween(1, 7);
            switch ($typeNumber) {
            case 1:
                $type = 'X-Cel LX';
                break;
            case 2:
                $type = 'Omni';
                break;
            case 3:
                $type = 'Luminos';
                break;
            case 4:
                $type = 'Kellner';
                break;
            case 5:
                $type = 'Plössl';
                break;
            case 6:
                $type = 'Ortho';
                break;
            case 7:
                $type = 'Erfle';
                break;
            }
        } elseif ($brandNumber == 7) {
            $brand = 'Vixen';
            $typeNumber = $this->faker->numberBetween(1, 7);
            switch ($typeNumber) {
            case 1:
                $type = 'LV';
                break;
            case 2:
                $type = 'NPL';
                break;
            case 3:
                $type = 'SLV';
                break;
            case 4:
                $type = 'NLVW';
                break;
            case 5:
                $type = 'NLV';
                break;
            case 6:
                $type = 'HR';
                break;
            case 7:
                $type = 'SSW';
                break;
            }
        }

        $focalLength = $this->faker->randomFloat(1, 1.0, 80.0);

        $rnd = $this->faker->numberBetween(1, 10);
        if ($rnd == 10) {
            $maxFocalLength = $this->faker->randomFloat(1, $focalLength + 0.1, 85.0);
        } else {
            $maxFocalLength = null;
        }

        return [
            'name' => $this->faker->sentence(3, true),
            'brand' => $brand,
            'focalLength' => $focalLength,
            'type' => $type,
            'apparentFOV' => $this->faker->numberBetween(15, 130),
            'maxFocalLength' => $maxFocalLength,
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'active' => $this->faker->numberBetween(0, 1),
        ];
    }
}
