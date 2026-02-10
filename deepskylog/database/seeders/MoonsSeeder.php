<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoonsSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // Find planet ids
        $ids = collect(DB::table('planets')->pluck('id','name'));

        $moons = [
            ['name'=>'Moon','planet_id'=>$ids->get('Earth'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Io','planet_id'=>$ids->get('Jupiter'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Europa','planet_id'=>$ids->get('Jupiter'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Ganymede','planet_id'=>$ids->get('Jupiter'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Callisto','planet_id'=>$ids->get('Jupiter'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Titan','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Rhea','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Dione','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Tethys','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Enceladus','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Mimas','planet_id'=>$ids->get('Saturn'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Phobos','planet_id'=>$ids->get('Mars'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Deimos','planet_id'=>$ids->get('Mars'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Titania','planet_id'=>$ids->get('Uranus'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Oberon','planet_id'=>$ids->get('Uranus'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Umbriel','planet_id'=>$ids->get('Uranus'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Ariel','planet_id'=>$ids->get('Uranus'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Triton','planet_id'=>$ids->get('Neptune'), 'body_type'=>'moon','created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('moons')->insertOrIgnore($moons);
    }
}
