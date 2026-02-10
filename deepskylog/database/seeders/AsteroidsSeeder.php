<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsteroidsSeeder extends Seeder
{
    public function run()
    {
        // Seed an empty list — real asteroid import comes from MPC or legacy DB.
        $asteroids = [
            // Example placeholder entries can be inserted by upstream import scripts
        ];

        if (!empty($asteroids)) {
            DB::table('asteroids')->insertOrIgnore($asteroids);
        }
    }
}
