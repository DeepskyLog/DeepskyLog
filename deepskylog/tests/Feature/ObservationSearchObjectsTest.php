<?php

use App\Livewire\SearchResultsTable;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('renders the legacy observation search page with results', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search', ['q' => 'NGC 2392']));

    $response->assertOk();
    $response->assertViewIs('observations.search');
    $response->assertSee('Search observations');
    $response->assertSee('Export OAL');
});

it('redirects directly to the object detail page when observation search contains one unique object', function () {
    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $response = $this->get(route('observations.search.objects', ['q' => 'NGC 2392']));

    $response->assertRedirect(route('object.show', ['slug' => 'ngc-2392']));
});

it('shows extracted observation objects in the same search results table for multiple objects', function () {
    config(['database.connections.mysqlOld.database' => '']);

    $user = User::factory()->create([
        'name' => 'Alice Observer',
        'username' => 'alice',
        'slug' => 'alice',
    ]);

    Observation::query()->insert([
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-01',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'NGC 2392',
            'target_name' => 'NGC 2392',
            'has_drawing' => false,
            'legacy_source' => null,
        ],
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-02',
            'target_type' => 'planet',
            'target_table' => 'planets',
            'target_key' => 'mars',
            'target_name' => 'Mars',
            'has_drawing' => false,
            'legacy_source' => null,
        ],
    ]);

    DB::table('search_index')->insert([
        [
            'name' => 'NGC 2392',
            'name_normalized' => 'ngc 2392',
            'source_table' => 'objects',
            'source_pk' => 'ngc-2392',
            'display_name' => 'NGC 2392',
            'source_type' => 'object',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Mars',
            'name_normalized' => 'mars',
            'source_table' => 'planets',
            'source_pk' => 'mars',
            'display_name' => 'Mars',
            'source_type' => 'planet',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Unrelated Object',
            'name_normalized' => 'unrelated object',
            'source_table' => 'objects',
            'source_pk' => 'unrelated-object',
            'display_name' => 'Unrelated Object',
            'source_type' => 'object',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get(route('observations.search.objects', ['q' => '%']));

    $response->assertOk();
    $response->assertViewIs('search.results');
    $response->assertViewHas('observationSearch', ['q' => '%', 'filters' => []]);

    // The results table matches the observed objects in the database.
    $table = new SearchResultsTable();
    $table->boot();
    $table->q = '%';
    $table->observationSearch = ['q' => '%', 'filters' => []];

    expect($table->datasource()->get()->pluck('display_name')->sort()->values()->all())
        ->toBe(['Mars', 'NGC 2392']);

    // An explicit list of object references is still supported.
    $component = new SearchResultsTable();
    $component->objectRefs = [
        ['source_table' => 'objects', 'name' => 'NGC 2392'],
        ['source_table' => 'planets', 'name' => 'Mars'],
    ];

    if (method_exists($component, 'boot')) {
        $component->boot();
    }

    $results = $component->datasource()->get();

    expect($results->pluck('display_name')->all())->toBe(['Mars', 'NGC 2392']);
    expect($results->pluck('display_name')->all())->not->toContain('Unrelated Object');
});
it('lists the distinct objects of all observations when no search term or filter is given', function () {
    $user = User::factory()->create([
        'name' => 'Bob Observer',
        'username' => 'bob',
        'slug' => 'bob',
    ]);

    $observation = fn(string $date, string $type, string $table, string $key, string $name): array => [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'target_type' => $type,
        'target_table' => $table,
        'target_key' => $key,
        'target_name' => $name,
        'has_drawing' => false,
        'legacy_source' => null,
    ];

    Observation::query()->insert([
        $observation('2026-05-01', 'deepsky', 'objects', 'M 31', 'M 31'),
        $observation('2026-05-02', 'deepsky', 'objects', 'M 31', 'M 31'),
        $observation('2026-05-03', 'planet', 'planets', 'jupiter', 'Jupiter'),
        $observation('2026-05-04', 'lunar', 'lunar_features', 'copernicus', 'copernicus'),
    ]);

    $indexRow = fn(string $name, string $table, string $pk, string $type): array => [
        'name' => $name,
        'name_normalized' => strtolower($name),
        'source_table' => $table,
        'source_pk' => $pk,
        'display_name' => $name,
        'source_type' => $type,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('search_index')->insert([
        $indexRow('M 31', 'objects', 'm-31', 'object'),
        $indexRow('Jupiter', 'planets', 'jupiter', 'planet'),
        $indexRow('Copernicus', 'lunar_features', 'copernicus', 'lunar_feature'),
        $indexRow('M 32', 'objects', 'm-32', 'object'),
    ]);

    $response = $this->get(route('observations.search.objects'));

    $response->assertOk();
    $response->assertViewIs('search.results');
    $response->assertViewHas('observationSearch', ['q' => '', 'filters' => []]);

    $table = new SearchResultsTable();
    $table->boot();
    $table->observationSearch = ['q' => '', 'filters' => []];

    expect($table->datasource()->get()->pluck('display_name')->sort()->values()->all())
        ->toBe(['Copernicus', 'Jupiter', 'M 31']);
});
