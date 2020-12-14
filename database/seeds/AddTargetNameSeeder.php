<?php

namespace Database\Seeders;

use App\Models\Target;
use Illuminate\Database\Seeder;

class AddTargetNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Loop over the targets
        $targets = Target::all();

        foreach ($targets as $target) {
            $target->name = $target->target_name;
            $target->save();
        }
    }
}
