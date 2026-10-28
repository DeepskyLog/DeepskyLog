<?php

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function insertAdvancedSearchIndexRow(string $name, string $sourceTable = 'objects', string $sourcePk = ''): void
{
    DB::table('search_index')->insert([
        'name' => $name,
        'name_normalized' => strtolower($name),
        'source_table' => $sourceTable,
        'source_pk' => $sourcePk !== '' ? $sourcePk : str($name)->slug()->toString(),
        'display_name' => $name,
        'source_type' => $sourceTable === 'objects' ? 'object' : $sourceTable,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('renders advanced object results with quick-search export actions', function () {
    $response = $this->get(route('search.advanced.results', ['name_text' => '2392']));

    $response->assertOk();
    $response->assertSee('Export names (PDF)');
    $response->assertSee('Export AstroPlanner (.apd)');
});

it('exports advanced object results using the current advanced filters', function () {
    insertAdvancedSearchIndexRow('NGC 2392', 'objects', 'ngc-2392');
    insertAdvancedSearchIndexRow('Mars', 'planets', 'mars');

    $response = $this->get(route('search.stxt', ['name_text' => '2392']));

    $response->assertOk();

    expect($response->streamedContent())
        ->toContain('NGC 2392')
        ->not->toContain('Mars');
});

it('renders advanced observation results with extraction and export actions', function () {
    $response = $this->get(route('search.observations.advanced.results', ['languages' => ['en']]));

    $response->assertOk();
    $response->assertSee('Extract objects');
    $response->assertSee('Export OAL');
});

it('extracts objects from advanced observation results using the current filters', function () {
    $user = User::factory()->create([
        'name' => 'Advanced Observer',
        'username' => 'advanced-observer',
        'slug' => 'advanced-observer',
    ]);

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
            'legacy_source' => null,
        ],
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-04',
            'target_type' => 'planet',
            'target_table' => 'planets',
            'target_key' => 'Mars',
            'target_name' => 'Mars',
            'language' => 'en',
            'legacy_source' => null,
        ],
        [
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-05-05',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => 'M 45',
            'target_name' => 'M 45',
            'language' => 'nl',
            'legacy_source' => null,
        ],
    ]);

    insertAdvancedSearchIndexRow('NGC 2392', 'objects', 'ngc-2392');
    insertAdvancedSearchIndexRow('Mars', 'planets', 'mars');
    insertAdvancedSearchIndexRow('M 45', 'objects', 'm-45');

    $response = $this->get(route('observations.search.objects', ['languages' => ['en']]));

    $response->assertOk();
    $response->assertViewIs('search.results');
    $response->assertViewHas('observationSearch', ['q' => '', 'filters' => ['languages' => ['en']]]);

    // The results table lists only the objects of the filtered observations.
    $table = new \App\Livewire\SearchResultsTable();
    $table->boot();
    $table->observationSearch = ['q' => '', 'filters' => ['languages' => ['en']]];

    expect($table->datasource()->get()->pluck('display_name')->sort()->values()->all())
        ->toBe(['Mars', 'NGC 2392']);
});