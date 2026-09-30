<?php

use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\NearbyObjectsTable;
use App\Livewire\SearchResultsTable;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObjectObservationStatistics;
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

    // Saved through the model: ObservationObserver keeps the statistics.
    foreach ([
        $observation($me, '2024-03-01', false),
        $observation($me, '2025-01-15', true),
        $observation($other, '2026-02-10', false),
    ] as $fields) {
        Observation::create($fields);
    }

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

    // Inserted without model events: the command fills the statistics.
    Observation::query()->insert([
        $observation($me, 'NGC 7000', false),
        $observation($other, 'NGC 7001', true),
    ]);
    $this->artisan('observations:rebuild-object-stats')->assertSuccessful();

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

/**
 * The statistics of one object as the correlated subqueries on the
 * observations gave them before they were kept in tables.
 */
function statisticsFromObservations(string $name, ?string $username): array
{
    $observations = DB::table('observations')
        ->where('target_type', 'deepsky')
        ->where('target_name', $name)
        ->get();
    $mine = $observations->where('observer_username', $username ?? '');
    $ymd = fn($date) => $date === null ? null : date('Ymd', strtotime((string) $date));

    return [
        'total_observations' => $observations->count(),
        'total_drawings' => $observations->where('has_drawing', 1)->count(),
        'your_observations' => $mine->count(),
        'your_drawings' => $mine->where('has_drawing', 1)->count(),
        'last_seen_date' => $ymd($observations->max('observed_on')),
        'your_last_seen_date' => $ymd($mine->max('observed_on')),
        'last_drawing_date' => $ymd($observations->where('has_drawing', 1)->max('observed_on')),
        'your_last_drawing_date' => $ymd($mine->where('has_drawing', 1)->max('observed_on')),
    ];
}

/**
 * The statistics of one object as the object tables read them.
 */
function statisticsFromTables(string $name, ?string $username): array
{
    $query = DB::table('objects')->where('objects.name', $name);
    ObjectObservationStatistics::join($query, $username);
    $row = (array) $query->selectRaw(ObjectObservationStatistics::selectList())->first();
    unset($row['seen'], $row['last_seen']);

    return $row;
}

function statisticsObservation(User $user, string $name, string $date, bool $drawing = false): array
{
    return [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $name,
        'target_name' => $name,
        'has_drawing' => $drawing,
    ];
}

it('keeps the statistics up to date when observations are created, changed and deleted', function () {
    $me = User::factory()->create(['username' => 'me-observer', 'slug' => 'me-observer']);
    $other = User::factory()->create(['username' => 'other-observer', 'slug' => 'other-observer']);

    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');

    $check = function () use ($me, $other) {
        foreach (['NGC 7000', 'NGC 7001'] as $name) {
            foreach ([$me->username, $other->username, null] as $username) {
                expect(statisticsFromTables($name, $username))->toEqual(statisticsFromObservations($name, $username));
            }
        }
    };

    $first = Observation::create(statisticsObservation($me, 'NGC 7000', '2024-03-01'));
    $second = Observation::create(statisticsObservation($me, 'NGC 7000', '2025-01-15', true));
    Observation::create(statisticsObservation($other, 'NGC 7000', '2026-02-10'));
    $check();
    expect(statisticsFromTables('NGC 7000', $me->username))
        ->total_observations->toEqual(3)
        ->your_last_drawing_date->toBe('20250115');

    // Another date and a drawing
    $first->update(['observed_on' => '2025-06-01', 'has_drawing' => true]);
    $check();
    expect(statisticsFromTables('NGC 7000', $me->username)['your_last_drawing_date'])->toBe('20250601');

    // Another object: both objects change
    $second->update(['target_key' => 'NGC 7001', 'target_name' => 'NGC 7001']);
    $check();
    expect(statisticsFromTables('NGC 7001', $me->username)['your_drawings'])->toEqual(1);

    // Another observer: both observers change
    $second->update(['observer_username' => $other->username, 'observer_user_id' => $other->id]);
    $check();
    expect(statisticsFromTables('NGC 7001', $me->username)['your_observations'])->toEqual(0)
        ->and(statisticsFromTables('NGC 7001', $other->username)['your_observations'])->toEqual(1);

    // No longer a deep-sky observation
    $second->update(['target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => '1', 'target_name' => 'C/2020 F3']);
    $check();
    expect(DB::table(ObjectObservationStatistics::TABLE)->where('object_name', 'NGC 7001')->exists())->toBeFalse();

    // Deleting the last observation of an observer removes their row
    $first->delete();
    $check();
    expect(DB::table(ObjectObservationStatistics::OBSERVER_TABLE)
        ->where('object_name', 'NGC 7000')->where('observer_username', $me->username)->exists())->toBeFalse();
});

it('rebuilds the same statistics the observations give, whatever the case of the name', function () {
    $me = User::factory()->create(['username' => 'me-observer', 'slug' => 'me-observer']);
    $other = User::factory()->create(['username' => 'other-observer', 'slug' => 'other-observer']);

    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');
    insertStatisticsSearchObject('NGC 7002', 'ngc-7002');

    Observation::query()->insert([
        statisticsObservation($me, 'NGC 7000', '2020-01-01'),
        statisticsObservation($me, 'ngc 7000', '2021-05-03', true),
        statisticsObservation($other, 'NGC 7000', '2019-12-31', true),
        statisticsObservation($other, 'NGC 7001', '2022-07-08'),
        ['observed_on' => null] + statisticsObservation($other, 'NGC 7001', '2000-01-01'),
        // Not deep-sky, not counted
        ['target_type' => 'comet'] + statisticsObservation($me, 'NGC 7002', '2023-01-01'),
    ]);

    $this->artisan('observations:rebuild-object-stats')
        ->expectsOutputToContain('Statistics of 2 objects and 3 observer/object pairs')
        ->assertSuccessful();

    foreach (['NGC 7000', 'NGC 7001', 'NGC 7002'] as $name) {
        foreach ([$me->username, $other->username, null] as $username) {
            expect(statisticsFromTables($name, $username))->toEqual(statisticsFromObservations($name, $username));
        }
    }

    expect(statisticsFromTables('NGC 7000', $me->username))
        ->total_observations->toEqual(3)
        ->your_observations->toEqual(2)
        ->last_seen_date->toBe('20210503')
        ->last_drawing_date->toBe('20210503')
        ->and(statisticsFromTables('NGC 7002', $me->username))
        ->total_observations->toEqual(0)
        ->last_seen_date->toBeNull();

    // Running it again gives the same rows
    $this->artisan('observations:rebuild-object-stats')->assertSuccessful();
    expect(DB::table(ObjectObservationStatistics::TABLE)->count())->toBe(2);
});

it('moves the statistics along when an object is renamed without model events', function () {
    $me = User::factory()->create(['username' => 'me-observer', 'slug' => 'me-observer']);
    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');

    Observation::create(statisticsObservation($me, 'NGC 7000', '2024-03-01'));

    Observation::ofDeepskyObject('NGC 7000')->update(['target_key' => 'NGC 7001', 'target_name' => 'NGC 7001']);
    ObjectObservationStatistics::refreshObjects(['NGC 7000', 'NGC 7001']);

    expect(statisticsFromTables('NGC 7000', $me->username)['total_observations'])->toEqual(0)
        ->and(statisticsFromTables('NGC 7001', $me->username))
        ->total_observations->toEqual(1)
        ->your_observations->toEqual(1)
        ->your_last_seen_date->toBe('20240301');
});

it('shows and sorts on the statistics in the nearby objects table', function () {
    $me = User::factory()->create(['username' => 'me-observer', 'slug' => 'me-observer']);
    insertStatisticsSearchObject('NGC 7000', 'ngc-7000');
    insertStatisticsSearchObject('NGC 7001', 'ngc-7001');
    insertStatisticsSearchObject('NGC 7002', 'ngc-7002');

    Observation::create(statisticsObservation($me, 'NGC 7001', '2024-03-01'));
    Observation::create(statisticsObservation($me, 'NGC 7001', '2024-04-01'));
    Observation::create(statisticsObservation($me, 'NGC 7002', '2023-01-01'));

    // The spherical distance of the nearby table uses MySQL functions SQLite lacks.
    $pdo = DB::connection()->getPdo();
    $pdo->sqliteCreateFunction('LEAST', fn(...$values) => min($values));
    $pdo->sqliteCreateFunction('GREATEST', fn(...$values) => max($values));
    foreach (['SIN' => 'sin', 'COS' => 'cos', 'ACOS' => 'acos', 'RADIANS' => 'deg2rad', 'DEGREES' => 'rad2deg'] as $sql => $php) {
        $pdo->sqliteCreateFunction($sql, $php, 1);
    }

    $this->actingAs($me);

    $table = new NearbyObjectsTable();
    $table->objectId = 0;
    $table->objectName = 'NGC 7000';
    $table->ra = 0;
    $table->decl = 0;

    $rows = $table->datasource()->orderBy('seen', 'desc')->get();

    expect($rows->pluck('name')->all())->toBe(['NGC 7001', 'NGC 7002'])
        ->and($rows->pluck('seen')->map(fn($v) => (int) $v)->all())->toBe([2, 1])
        ->and($rows->pluck('last_seen')->all())->toBe(['20240401', '20230101']);
});
