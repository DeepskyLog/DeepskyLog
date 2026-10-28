<?php

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function insertLunarListingFixtures(User $observer): void
{
    DB::table('planets')->insert([
        'name' => 'Listing Planet Test',
        'slug' => 'listing-planet-test',
        'body_type' => 'planet',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('moons')->insert([
        'name' => 'Listing Moon Test',
        'slug' => 'listing-moon-test',
        'planet_id' => DB::table('planets')->where('slug', 'listing-planet-test')->value('id'),
        'body_type' => 'moon',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('lunar_features')->insert([
        'name' => 'Listing Crater Test',
        'slug' => 'listing-crater-test',
        'feature_type' => 'crater',
        'moon_id' => DB::table('moons')->where('slug', 'listing-moon-test')->value('id'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $base = [
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'legacy_source' => null,
    ];

    Observation::query()->insert([
        $base + [
            'observed_on' => '2026-05-10',
            'target_type' => 'planet',
            'target_table' => 'planets',
            'target_key' => 'listing-planet-test',
            'target_name' => 'Listing Planet Test',
            'has_drawing' => true,
        ],
        $base + [
            'observed_on' => '2026-05-11',
            'target_type' => 'lunar',
            'target_table' => 'moons',
            'target_key' => 'listing-moon-test',
            'target_name' => 'Listing Moon Test',
            'has_drawing' => true,
        ],
        $base + [
            'observed_on' => '2026-05-12',
            'target_type' => 'lunar',
            'target_table' => 'lunar_features',
            'target_key' => 'listing-crater-test',
            'target_name' => 'listing-crater-test',
            'has_drawing' => false,
        ],
    ]);
}

function listedTargetKeys($response): array
{
    return $response->viewData('deepsky')->pluck('target_key')->all();
}

beforeEach(function () {
    $this->observer = User::factory()->create([
        'name' => 'Lunar Observer',
        'username' => 'lunar-observer',
        'slug' => 'lunar-observer',
    ]);

    insertLunarListingFixtures($this->observer);
});

it('lists all observations of a moon and of a lunar feature', function () {
    $moon = $this->get('/observations/object/listing-moon-test')->assertOk();
    expect(listedTargetKeys($moon))->toBe(['listing-moon-test']);

    $crater = $this->get('/observations/object/listing-crater-test')->assertOk();
    expect(listedTargetKeys($crater))->toBe(['listing-crater-test']);
});

it('lists only drawings of a moon and of a lunar feature', function () {
    $moon = $this->get('/observations/drawings/listing-moon-test')->assertOk();
    expect(listedTargetKeys($moon))->toBe(['listing-moon-test']);

    $crater = $this->get('/observations/drawings/listing-crater-test')->assertOk();
    expect(listedTargetKeys($crater))->toBe([]);
});

it('lists an observer\'s observations and drawings of a lunar feature', function () {
    $observations = $this->get('/observations/lunar-observer/listing-crater-test')->assertOk();
    expect(listedTargetKeys($observations))->toBe(['listing-crater-test']);

    $drawings = $this->get('/observations/drawings/lunar-observer/listing-moon-test')->assertOk();
    expect(listedTargetKeys($drawings))->toBe(['listing-moon-test']);
});

it('still lists planet observations and rejects unknown targets', function () {
    $planet = $this->get('/observations/object/listing-planet-test')->assertOk();
    expect(listedTargetKeys($planet))->toBe(['listing-planet-test']);

    $this->get('/observations/object/listing-unknown-test')->assertNotFound();
});
