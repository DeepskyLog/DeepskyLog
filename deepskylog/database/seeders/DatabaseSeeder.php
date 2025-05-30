<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                //                GroupSeeder::class, UsersTableSeeder::class, addSlug::class,
                //                SketchOfTheWeekSeeder::class,
                //                SketchOfTheMonthSeeder::class,
                //                addAchievementsSeeder::class,
                //                InstrumentSeeder::class,
                //                EyepieceSeeder::class,
                LensSeeder::class,
            ]
        );
    }
}
