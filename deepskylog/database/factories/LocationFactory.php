<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'longitude' => $this->faker->randomFloat(6, -180, 180),
            'latitude' => $this->faker->randomFloat(6, -90, 90),
            'timezone' => 'UTC',
            'limitingMagnitude' => -999,
            'skyBackground' => -999,
            'elevation' => 0,
            'country' => $this->faker->country(),
            'active' => true,
            'user_id' => User::factory(),
            'observer' => null,
            'observations' => 0,
            'picture' => null,
            'slug' => $this->faker->word(),
        ];
    }
}
