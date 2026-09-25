<?php

use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\SearchResultsTable;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function insertStatisticsSearchObject(string $name, string $slug): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0,
        'decl' => 0,
        'mag' => 4.0,
        'subr' => 0,
        'pa' => null,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 10,
        'diam2' => 5,
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
        'slug' => $slug,
        'timestamp' => now(),
    ]);

    DB::table('objectnames')->insert([
        'objectname' => $name,
        'slug' => $slug,
        'catalog' => strtok($name, ' '),
        'catindex' => trim((string) str($name)->after(' ')),
        'altname' => $name,
        'timestamp' => now(),
    ]);

    DB::table('search_index')->insert([
        'name' => $name,
        'name_normalized' => strtolower($name),
        'source_table' => 'objects',
        'source_pk' => $slug,
        'display_name' => $name,
        'source_type' => 'object',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('shows observation statistics of deep-sky objects from the unified observations table', function () {
    $me = User::factory()->create(['name' => 'Me', 'username' => 'me-observer', 'slug' => 'me-observer']);
    $other = User::factory()->create(['name' => 'Other', 'username' => 'other-observer', 'slug' => 'other-observer']);

    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');

    $observation = fn(User $user, string $date, bool $drawing, string $name = 'NGC 7000'): array => [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $name,
        'target_name' => $name,
        'has_drawing' => $drawing,
        'legacy_source' => null,
    ];

    Observation::query()->insert([
        $observation($me, '2024-03-01', false),
        $observation($me, '2025-01-15', true),
        $observation($other, '2026-02-10', false),
    ]);

    $this->actingAs($me);

    $table = new SearchResultsTable();
    $table->boot();
    $table->q = 'NGC 700';

    $rows = $table->datasource()->get()->keyBy('display_name');

    expect($rows['NGC 7000'])
        ->total_observations->toEqual(3)
        ->total_drawings->toEqual(1)
        ->your_observations->toEqual(2)
        ->your_drawings->toEqual(1)
        ->last_seen_date->toEqual('20260210')
        ->your_last_seen_date->toEqual('20250115')
        ->last_drawing_date->toEqual('20250115')
        ->your_last_drawing_date->toEqual('20250115')
        ->seen->toEqual(3)
        ->last_seen->toEqual('20250115');

    expect($rows['NGC 7001'])
        ->total_observations->toEqual(0)
        ->your_last_seen_date->toBeNull();
});

it('filters the advanced object search on the unified observation statistics', function () {
    $me = User::factory()->create(['name' => 'Me', 'username' => 'me-observer', 'slug' => 'me-observer']);
    $other = User::factory()->create(['name' => 'Other', 'username' => 'other-observer', 'slug' => 'other-observer']);

    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');
    insertStatisticsSearchObject('NGC 7002', 'ngc-7002');

    $observation = fn(User $user, string $name, bool $drawing): array => [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-01-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $name,
        'target_name' => $name,
        'has_drawing' => $drawing,
        'legacy_source' => null,
    ];

    Observation::query()->insert([
        $observation($me, 'NGC 7000', false),
        $observation($other, 'NGC 7001', true),
    ]);

    $this->actingAs($me);

    $names = function (string $status): array {
        $table = new AdvancedObjectSearchTable();
        $table->boot();
        $table->mount(['object_types' => ['GX'], 'observing_status' => $status]);

        return $table->datasource()->get()->pluck('display_name')->sort()->values()->all();
    };

    expect($names('all'))->toBe(['NGC 7000', 'NGC 7001', 'NGC 7002'])
        ->and($names('seen_any'))->toBe(['NGC 7000', 'NGC 7001'])
        ->and($names('unseen_any'))->toBe(['NGC 7002'])
        ->and($names('drawn_any'))->toBe(['NGC 7001'])
        ->and($names('seen_by_me'))->toBe(['NGC 7000'])
        ->and($names('unseen_by_me'))->toBe(['NGC 7001', 'NGC 7002'])
        ->and($names('seen_by_others_not_me'))->toBe(['NGC 7001'])
        ->and($names('drawn_by_others_not_me'))->toBe(['NGC 7001']);
});
