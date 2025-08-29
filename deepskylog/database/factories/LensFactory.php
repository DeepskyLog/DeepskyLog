<?php

namespace Database\Factories;

use App\Models\Lens;
use App\Models\LensMake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LensFactory extends Factory
{
    protected $model = Lens::class;

    public function definition(): array
    {
        $make = LensMake::firstOrCreate(['name' => 'DefaultLensMake']);

        return [
            'make_id' => $make->id,
            'name' => $this->faker->word(),
            'factor' => $this->faker->randomFloat(2, 0.5, 3),
            'active' => true,
            'user_id' => User::factory(),
            'observer' => null,
            'observations' => 0,
            'picture' => null,
            'slug' => $this->faker->word(),
        ];
    }
}
