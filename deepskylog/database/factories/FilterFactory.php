<?php

namespace Database\Factories;

use App\Models\Filter;
use App\Models\FilterColor;
use App\Models\FilterMake;
use App\Models\FilterType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilterFactory extends Factory
{
    protected $model = Filter::class;

    public function definition(): array
    {
        $make = FilterMake::firstOrCreate(['name' => 'DefaultFilterMake']);
        $type = FilterType::firstOrCreate(['name' => 'DefaultFilterType']);
        $color = FilterColor::firstOrCreate(['name' => 'DefaultColor']);

        return [
            'make_id' => $make->id,
            'name' => $this->faker->word(),
            'type_id' => $type->id,
            'color_id' => $color->id,
            'wratten' => null,
            'schott' => null,
            'active' => true,
            'user_id' => User::factory(),
            'observer' => null,
            'observations' => 0,
            'picture' => null,
            'slug' => $this->faker->word(),
        ];
    }
}
