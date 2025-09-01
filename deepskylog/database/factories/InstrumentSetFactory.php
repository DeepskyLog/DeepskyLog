<?php

namespace Database\Factories;

use App\Models\InstrumentSet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InstrumentSetFactory extends Factory
{
    protected $model = InstrumentSet::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(asText: true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'active' => true,
            'observer' => null,
            'picture' => null,
        ];
    }
}
