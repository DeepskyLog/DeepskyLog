<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LunarFeaturesSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $moonId = DB::table('moons')->where('name', 'Moon')->value('id');

        // Load static features list
        $staticPath = __DIR__ . '/lunar_features_static.php';
        if (file_exists($staticPath)) {
            $this->command->info("Loading static lunar features from: {$staticPath}");
            $raw = include $staticPath;
            $features = array_map(function ($f) use ($moonId, $now) {
                return [
                    'name' => $f['name'],
                    'feature_type' => $f['feature_type'],
                    'moon_id' => $moonId,
                    'lat' => $f['lat'] ?? null,
                    'lon' => $f['lon'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $raw ?: []);
        } else {
            $this->command->warn('Static lunar features file not found; no features will be inserted');
            $features = [];
        }

        // Insert in batches to avoid very large single inserts
        $chunks = array_chunk($features, 200);
        foreach ($chunks as $chunk) {
            DB::table('lunar_features')->insertOrIgnore($chunk);
        }

        $this->command->info('Lunar features seeding complete. Inserted approx: ' . count($features));
    }
}
