<?php

namespace Database\Factories;

use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstrumentFactory extends Factory
{
    protected $model = Instrument::class;

    public function definition(): array
    {
        $make = InstrumentMake::firstOrCreate(['name' => 'DefaultMake']);
        $type = InstrumentType::firstOrCreate(['name' => 'DefaultType']);
        $mount = MountType::firstOrCreate(['name' => 'DefaultMount']);

        return [
            'make_id' => $make->id,
            'name' => $this->faker->word(),
            'aperture_mm' => $this->faker->numberBetween(50, 400),
            'instrument_type_id' => $type->id,
            'focal_length_mm' => $this->faker->numberBetween(200, 2000),
            'fixedMagnification' => null,
            'active' => true,
            'observer' => null,
            'flip_image' => true,
            'flop_image' => true,
            'obstruction_perc' => null,
            'mount_type_id' => $mount->id,
            'user_id' => User::factory(),
        ];
    }
}
