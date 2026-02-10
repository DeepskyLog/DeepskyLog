<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanetsSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $planets = [
            ['name' => 'Sun', 'designation' => 'Sol', 'body_type' => 'star', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Mercury', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Venus', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Earth', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Mars', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Jupiter', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Saturn', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Uranus', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Neptune', 'designation' => null, 'body_type' => 'planet', 'created_at'=>$now,'updated_at'=>$now],
            ['name' => 'Pluto', 'designation' => null, 'body_type' => 'dwarf planet', 'created_at'=>$now,'updated_at'=>$now],
        ];
        DB::table('planets')->insertOrIgnore($planets);
    }
}
