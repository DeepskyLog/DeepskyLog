<?php

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('removes the moon from the lunar features and moves its observations to the moon', function () {
    $now = now();
    $moonId = DB::table('moons')->insertGetId(['name' => 'Moon', 'slug' => 'moon', 'body_type' => 'moon', 'created_at' => $now, 'updated_at' => $now]);
    $featureId = DB::table('lunar_features')->insertGetId(['name' => 'Moon', 'slug' => 'moon', 'feature_type' => 'other', 'moon_id' => $moonId, 'created_at' => $now, 'updated_at' => $now]);
    $craterId = DB::table('lunar_features')->insertGetId(['name' => 'Copernicus', 'slug' => 'copernicus', 'feature_type' => 'crater', 'moon_id' => $moonId, 'created_at' => $now, 'updated_at' => $now]);
    foreach ([['moons', $moonId, 'moon'], ['lunar_features', $featureId, 'other'], ['lunar_features', $craterId, 'crater']] as [$table, $id, $type]) {
        DB::table('search_index')->insert([
            'name' => $table === 'lunar_features' && $id === $craterId ? 'Copernicus' : 'Moon',
            'source_table' => $table, 'source_pk' => (string) $id, 'source_type' => $type, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    $user = User::factory()->create();
    $observation = fn (string $key, string $name) => Observation::query()->create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'observed_on' => '2026-09-25',
        'language' => 'en', 'target_type' => 'lunar', 'target_table' => 'lunar_features', 'target_key' => $key,
        'target_name' => $name, 'has_drawing' => false, 'description' => 'Seen.',
    ]);
    $ofMoon = $observation('moon', 'Moon');
    $ofCrater = $observation('copernicus', 'Copernicus');

    $migration = require database_path('migrations/2026_10_19_000000_remove_moon_from_lunar_features.php');
    $migration->up();

    expect(DB::table('lunar_features')->pluck('name')->all())->toBe(['Copernicus'])
        ->and(DB::table('moons')->pluck('name')->all())->toBe(['Moon'])
        ->and($ofMoon->fresh()->only(['target_table', 'target_key', 'target_type', 'target_name']))
        ->toBe(['target_table' => 'moons', 'target_key' => 'moon', 'target_type' => 'lunar', 'target_name' => 'Moon'])
        ->and($ofCrater->fresh()->only(['target_table', 'target_key']))
        ->toBe(['target_table' => 'lunar_features', 'target_key' => 'copernicus'])
        ->and(DB::table('search_index')->where('name', 'Moon')->get(['source_table', 'name_unique'])->map(fn ($row) => (array) $row)->all())
        ->toBe([['source_table' => 'moons', 'name_unique' => 1]]);

    // Running it again changes nothing.
    $migration->up();
    expect(DB::table('lunar_features')->count())->toBe(1);
});
