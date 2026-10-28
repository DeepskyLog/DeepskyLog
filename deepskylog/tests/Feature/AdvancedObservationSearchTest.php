<?php

use App\Livewire\AdvancedObservationSearch;
use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function insertLegacyObject(string $name, string $type, string $con = 'ORI', array $overrides = []): void
{
    DB::table('objects')->insert(array_merge([
        'name' => $name,
        'type' => $type,
        'con' => $con,
        'ra' => 5.0,
        'decl' => 20.0,
        'mag' => 8.0,
        'subr' => 12.0,
        'pa' => null,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 10.0,
        'diam2' => 5.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => now(),
    ], $overrides));
}

it('renders the advanced observation search page', function () {
    $response = $this->get(route('search.observations.advanced'));

    $response->assertOk();
    $response->assertViewIs('search.advanced-observations');
    $response->assertSee('Advanced observation search');
});

it('renders observers as a searchable dropdown', function () {
    $response = $this->get(route('search.observations.advanced', [
        'activeFilters' => ['observers'],
    ]));

    $response->assertOk();
    $response->assertSee('Search observers...');
});

it('shows only valid observation languages with full names', function () {
    Observation::query()->insert([
        [
            'observer_username' => 'language-user-1',
            'observer_user_id' => null,
            'observed_on' => '2026-05-03',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 2392',
            'target_name' => 'NGC 2392',
            'language' => 'fr',
            'legacy_source' => null,
        ],
        [
            'observer_username' => 'language-user-2',
            'observer_user_id' => null,
            'observed_on' => '2026-05-04',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 45',
            'target_name' => 'M 45',
            'language' => '20.70',
            'legacy_source' => null,
        ],
        [
            'observer_username' => 'language-user-3',
            'observer_user_id' => null,
            'observed_on' => '2026-05-05',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 31',
            'target_name' => 'M 31',
            'language' => '2l',
            'legacy_source' => null,
        ],
    ]);

    $component = new AdvancedObservationSearch();
    $component->mount(['activeFilters' => ['languages']]);

    expect($component->allLanguages)
        ->toHaveKey('fr', 'French')
        ->not->toHaveKey('20.70')
        ->not->toHaveKey('2l');
});

it('clamps observation latitude filters to the valid range', function () {
    Livewire::test(AdvancedObservationSearch::class)
        ->set('activeFilters', ['latitude'])
        ->set('latitudeMin', '120')
        ->assertSet('latitudeMin', '90')
        ->set('latitudeMax', '-123.4')
        ->assertSet('latitudeMax', '-90');
});

it('renders descriptive deep-sky visibility labels', function () {
    $response = $this->get(route('search.observations.advanced', [
        'activeFilters' => ['visibility'],
    ]));

    $response->assertOk();
    $response->assertSee('Very simple, prominent object');
    $response->assertSee('Object definitely not seen');
});

it('filters observation results with advanced observation criteria', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    insertLegacyObject('NGC 2392', 'GX');
    insertLegacyObject('M 45', 'OC');

    Observation::query()->insert([
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-03',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 2392',
            'target_name' => 'NGC 2392',
            'language' => 'en',
            'has_drawing' => true,
            'legacy_source' => null,
        ],
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-04-25',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 45',
            'target_name' => 'M 45',
            'language' => 'nl',
            'has_drawing' => false,
            'legacy_source' => null,
        ],
    ]);

    $component = new ObservationSearchTable();
    if (method_exists($component, 'boot')) {
        $component->boot();
    }
    $component->mount('', [
        'object_types' => ['GX'],
        'observed_from' => '2026-05-01',
        'languages' => ['en'],
        'drawing_state' => 'with',
    ]);

    $results = $component->datasource()->get();

    expect($results)->toHaveCount(1);
    expect($results->pluck('target_name')->all())->toBe(['NGC 2392']);
});

it('saves and reloads advanced observation searches', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(AdvancedObservationSearch::class)
        ->set('activeFilters', ['observedBetween', 'languages', 'observingStatus', 'contrastReserve'])
        ->set('observedFrom', '2026-05-01')
        ->set('observedTo', '2026-05-31')
        ->set('languages', ['en'])
        ->set('observingStatus', 'drawn_any')
        ->set('contrastReserveMin', '1.5')
        ->set('saveName', 'May English observations')
        ->call('saveSearch');

    $savedId = DB::table('saved_observation_searches')
        ->where('user_id', $user->id)
        ->value('id');

    expect($savedId)->not->toBeNull();

    Livewire::test(AdvancedObservationSearch::class)
        ->call('loadSearch', $savedId)
        ->assertSet('observedFrom', '2026-05-01')
        ->assertSet('observedTo', '2026-05-31')
        ->assertSet('languages', ['en'])
        ->assertSet('observingStatus', 'drawn_any')
        ->assertSet('contrastReserveMin', '1.5');
});

it('filters observation results by observing list and atlas page', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice-list',
        'slug' => 'alice-list',
    ]);

    insertLegacyObject('NGC 2392', 'GX', 'ORI', ['urano' => 42]);
    insertLegacyObject('M 45', 'OC', 'TAU', ['urano' => 17]);

    Observation::query()->insert([
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-03',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 2392',
            'target_name' => 'NGC 2392',
            'legacy_source' => null,
        ],
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-04',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 45',
            'target_name' => 'M 45',
            'legacy_source' => null,
        ],
    ]);

    $listId = DB::table('observing_lists')->insertGetId([
        'owner_user_id' => $user->id,
        'name' => 'Planetary Nebulae',
        'slug' => 'planetary-nebulae',
        'description' => null,
        'public' => false,
        'comments_count' => 0,
        'likes_count' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('observing_list_items')->insert([
        'observing_list_id' => $listId,
        'object_name' => 'NGC 2392',
        'item_description' => null,
        'source_mode' => 'manual',
        'source_observation_id' => null,
        'added_by_user_id' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component = new ObservationSearchTable();
    if (method_exists($component, 'boot')) {
        $component->boot();
    }
    $component->mount('', [
        'observing_lists' => [$listId],
        'atlas_code' => 'urano',
        'atlas_page_spec' => '42',
    ]);

    $results = $component->datasource()->get();

    expect($results)->toHaveCount(1);
    expect($results->pluck('target_name')->all())->toBe(['NGC 2392']);
});

it('filters observation results by observing status and contrast reserve', function () {
    $currentUser = User::factory()->create([
        'name' => 'Current User',
        'username' => 'current-user',
        'slug' => 'current-user',
    ]);
    $otherUser = User::factory()->create([
        'name' => 'Other User',
        'username' => 'other-user',
        'slug' => 'other-user',
    ]);

    $locationId = DB::table('locations')->insertGetId([
        'name' => 'Backyard',
        'longitude' => 4.0,
        'latitude' => 51.0,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 10,
        'country' => 'BE',
        'active' => true,
        'user_id' => $currentUser->id,
        'observer' => $currentUser->username,
        'observations' => 0,
        'picture' => null,
        'slug' => 'backyard',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $instrumentId = DB::table('instruments')->insertGetId([
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Test Scope',
        'slug' => 'test-scope',
        'aperture_mm' => 200,
        'instrument_type_id' => DB::table('instrument_types')->value('id'),
        'focal_length_mm' => 1000,
        'fixedMagnification' => null,
        'active' => true,
        'flip_image' => true,
        'flop_image' => true,
        'obstruction_perc' => null,
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'user_id' => $currentUser->id,
        'observer' => $currentUser->username,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')
        ->where('id', $currentUser->id)
        ->update([
            'stdlocation' => $locationId,
            'stdtelescope' => $instrumentId,
        ]);

    $currentUser = $currentUser->fresh();
    $this->actingAs($currentUser);

    insertLegacyObject('NGC 2392', 'GX');
    insertLegacyObject('M 45', 'OC');

    Observation::query()->insert([
        [
            'observer_username' => $otherUser->username,
            'observer_user_id' => $otherUser->id,
            'observed_on' => '2026-05-03',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 2392',
            'target_name' => 'NGC 2392',
            'legacy_source' => null,
        ],
        [
            'observer_username' => $currentUser->username,
            'observer_user_id' => $currentUser->id,
            'observed_on' => '2026-05-04',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 45',
            'target_name' => 'M 45',
            'legacy_source' => null,
        ],
    ]);

    DB::table('user_object_metrics')->insert([
        [
            'user_id' => $currentUser->id,
            'instrument_id' => $instrumentId,
            'location_id' => $locationId,
            'lens_id' => null,
            'object_name' => 'NGC 2392',
            'contrast_reserve' => 3.2,
            'contrast_reserve_category' => 'easy',
            'optimum_detection_magnification' => 120,
            'optimum_eyepieces' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'user_id' => $currentUser->id,
            'instrument_id' => $instrumentId,
            'location_id' => $locationId,
            'lens_id' => null,
            'object_name' => 'M 45',
            'contrast_reserve' => 1.1,
            'contrast_reserve_category' => 'hard',
            'optimum_detection_magnification' => 90,
            'optimum_eyepieces' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $component = new ObservationSearchTable();
    if (method_exists($component, 'boot')) {
        $component->boot();
    }
    $component->mount('', [
        'observing_status' => 'unseen_by_me',
        'cr_min' => '2.0',
    ]);

    $results = $component->datasource()->get();

    expect($results)->toHaveCount(1);
    expect($results->pluck('target_name')->all())->toBe(['NGC 2392']);
});
// Issue #698: the chosen sort was lost when the observer opened a result and
// came back to the search.
it('keeps the sort of the observation search when the table is opened again', function () {
    Livewire::test(ObservationSearchTable::class)
        ->call('sortBy', 'instruments.aperture_mm', 'desc');

    Livewire::test(ObservationSearchTable::class)
        ->assertSet('sortField', 'instruments.aperture_mm')
        ->assertSet('sortDirection', 'desc');
});
