<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectNameTranslationsSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // Example: French translation for Pluto (Pluton -> Pluto)
        DB::table('object_name_translations')->updateOrInsert([
            'objectname' => 'Pluto',
            'locale' => 'fr',
            'name' => 'Pluton',
        ], [
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
