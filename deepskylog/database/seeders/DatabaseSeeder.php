<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                // Register legacy migration seeder
                \Database\Seeders\LegacyObjectsSeeder::class,
                \Database\Seeders\AsteroidsSeeder::class,
                \Database\Seeders\PlanetsSeeder::class,
                \Database\Seeders\MoonsSeeder::class,
                \Database\Seeders\LunarFeaturesSeeder::class,
                \Database\Seeders\ObjectNameTranslationsSeeder::class,
                \Database\Seeders\PopulateObjectSlugsSeeder::class,
                \Database\Seeders\BackfillPlanetSlugsSeeder::class,
                \Database\Seeders\BackfillMoonSlugsSeeder::class,
                \Database\Seeders\BackfillLunarFeatureSlugsSeeder::class,
                \Database\Seeders\BackfillAsteroidSlugsSeeder::class,
            ]
        );

        // Ensure malformed HTML entities in seeded sessions are fixed immediately after seeding.
        if ($this->command) {
            $this->command->info('Running sessions:fix-html-entities to correct any malformed entities...');
        }

        // Run the fix command in apply mode so seeded rows are corrected automatically.
        Artisan::call('sessions:fix-html-entities', ['--apply' => true]);

        if ($this->command) {
            $this->command->info('sessions:fix-html-entities completed.');
        }
    }
}
