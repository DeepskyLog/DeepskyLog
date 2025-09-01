<?php

namespace Database\Factories;

use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EyepieceFactory extends Factory
{
    protected $model = Eyepiece::class;

    public function definition(): array
    {
        $make = EyepieceMake::firstOrCreate(['name' => 'DefaultEyepieceMake']);
        $type = EyepieceType::firstOrCreate(['name' => 'DefaultEyepieceType']);

        return [
            'make_id' => $make->id,
            'type_id' => $type->id,
            'name' => $this->faker->word(),
            'focal_length_mm' => $this->faker->randomFloat(1, 2, 40),
            'apparentFOV' => $this->faker->numberBetween(40, 100),
            'max_focal_length_mm' => null,
            'field_stop_mm' => null,
            'active' => true,
            'user_id' => User::factory(),
            'observer' => null,
            'observations' => 0,
            'picture' => null,
            'slug' => $this->faker->word(),
        ];
    }
}
