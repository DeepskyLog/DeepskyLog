<?php

use App\Jobs\ComputeContrastReserveForObject;
use App\Jobs\ComputeContrastReserveForObjectsBatch;
use App\Jobs\ComputeObserverStats;
use App\Livewire\NearbyObjectsTable;
use App\Livewire\SearchResultsTable;
use App\Models\DeepskyObject;
use App\Models\User;
use App\Services\ObjectMetricsQueue;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

function insertCachingTestObject(string $name, float $ra, float $decl, float $mag = 8.4): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra' => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 12.0, 'pa' => 35, 'urano' => 0,
        'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 600.0, 'diam2' => 240.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('search_index')->insert([
        'name' => $name, 'display_name' => $name, 'source_table' => 'objects', 'source_pk' => Str::slug($name),
        'source_type' => 'GALXY', 'ra' => $ra, 'decl' => $decl,
    ]);
}

function insertCachingTestObservation(string $username, string $target, array $values = []): int
{
    return (int) DB::table('observations')->insertGetId([
        'observer_username' => $username, 'observed_on' => now()->subMonth()->toDateString(),
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $target, 'target_name' => $target,
        'has_drawing' => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'legacy_id' => null,
        'created_at' => now(), 'updated_at' => now(), ...$values,
    ]);
}

/**
 * A user with a standard instrument and location.
 */
function cachingTestObserver(): User
{
    $user = User::factory()->withPersonalTeam()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 200, 'focal_length_mm' => 1200, 'fixedMagnification' => null]);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'skyBackground' => 21.0]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();

    return $user->fresh();
}

/**
 * The MySQL functions of the queries these pages run (the spherical distance
 * of the nearby table, the natural sort of the search results, the charts of
 * the observer page) that SQLite lacks.
 */
function registerMysqlFunctions(): void
{
    $pdo = DB::connection()->getPdo();
    $pdo->sqliteCreateFunction('LEAST', fn (...$values) => min($values));
    $pdo->sqliteCreateFunction('GREATEST', fn (...$values) => max($values));
    foreach (['SIN' => 'sin', 'COS' => 'cos', 'ACOS' => 'acos', 'RADIANS' => 'deg2rad', 'DEGREES' => 'rad2deg'] as $sql => $php) {
        $pdo->sqliteCreateFunction($sql, $php, 1);
    }
    $pdo->sqliteCreateFunction('REGEXP_REPLACE', fn ($value, $pattern, $replacement) => preg_replace('/'.$pattern.'/u', $replacement, (string) $value), 3);
    $pdo->sqliteCreateFunction('YEAR', fn ($date) => $date === null ? null : (int) substr($date, 0, 4), 1);
    $pdo->sqliteCreateFunction('MONTH', fn ($date) => $date === null ? null : (int) substr($date, 5, 2), 1);
}

it('queues one job for the objects without metrics and follows it in the cache', function () {
    Queue::fake();
    $user = cachingTestObserver();
    DB::table('user_object_metrics')->insert(['user_id' => $user->id, 'instrument_id' => $user->stdtelescope, 'location_id' => $user->stdlocation, 'object_name' => 'M 31', 'created_at' => now(), 'updated_at' => now()]);

    $token = ObjectMetricsQueue::queueMissing($user->id, $user->stdtelescope, $user->stdlocation, null, ['M 32', 'M 31', 'M 110']);

    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, 1);
    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, fn ($job) => $job->objectNames === ['M 110', 'M 32'] && $job->token === $token);
    expect(ObjectMetricsQueue::pending($token))->toBeTrue();

    // The same objects are not queued again while their job runs.
    expect(ObjectMetricsQueue::queueMissing($user->id, $user->stdtelescope, $user->stdlocation, null, ['M 110', 'M 32']))->toBe($token);
    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, 1);

    ObjectMetricsQueue::jobDone($token);
    expect(ObjectMetricsQueue::pending($token))->toBeFalse();
});

it('stores the same metrics in a batch as the single-object job', function () {
    $user = cachingTestObserver();
    insertCachingTestObject('Batch Galaxy', 0.71, 41.27);
    insertCachingTestObject('Other Galaxy', 0.72, 41.0, 10.2);

    $token = ObjectMetricsQueue::queueMissing($user->id, $user->stdtelescope, $user->stdlocation, null, ['Batch Galaxy', 'Other Galaxy']);

    // The sync queue ran the job during the call.
    expect($token)->toBeNull()
        ->and(DB::table('user_object_metrics')->count())->toBe(2);
    foreach (['Batch Galaxy', 'Other Galaxy'] as $name) {
        $expected = ComputeContrastReserveForObject::metricValues(DeepskyObject::where('name', $name)->first(), $user->id, $user->stdtelescope, $user->stdlocation, null);
        $stored = DB::table('user_object_metrics')->where('object_name', $name)->first();
        expect((float) $stored->contrast_reserve)->toEqualWithDelta((float) $expected['contrast_reserve'], 0.0001)
            ->and($stored->contrast_reserve_category)->toBe($expected['contrast_reserve_category'])
            ->and((int) $stored->optimum_detection_magnification)->toBe((int) $expected['optimum_detection_magnification']);
    }

    // Nothing is missing any more.
    Queue::fake();
    expect(ObjectMetricsQueue::queueMissing($user->id, $user->stdtelescope, $user->stdlocation, null, ['Batch Galaxy', 'Other Galaxy']))->toBeNull();
    Queue::assertNothingPushed();
});

it('queues the metrics of the search results instead of computing them in the request', function () {
    Queue::fake();
    registerMysqlFunctions();
    $user = cachingTestObserver();
    insertCachingTestObject('Queued Galaxy 1', 0.71, 41.27);
    insertCachingTestObject('Queued Galaxy 2', 0.72, 41.0);
    insertCachingTestObject('Unrelated Nebula', 5.5, -5.4);
    $this->actingAs($user);
    Livewire::withoutLazyLoading();

    $table = Livewire::test(SearchResultsTable::class, ['q' => 'Queued Galaxy']);

    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, fn ($job) => $job->objectNames === ['Queued Galaxy 1', 'Queued Galaxy 2']);
    expect(DB::table('user_object_metrics')->count())->toBe(0)
        ->and($table->get('hasPendingCalculations'))->toBeTrue();
});

it('queues the metrics of the nearby objects within the declination band', function () {
    Queue::fake();
    registerMysqlFunctions();
    $user = cachingTestObserver();
    insertCachingTestObject('Centre Galaxy', 0.712, 41.27);
    insertCachingTestObject('Near Galaxy', 0.7117, 40.87);
    insertCachingTestObject('Far Galaxy', 0.712, 45.0);
    $this->actingAs($user);
    Livewire::withoutLazyLoading();

    $table = Livewire::test(NearbyObjectsTable::class, ['objectId' => 1, 'objectName' => 'Centre Galaxy', 'ra' => 0.712, 'decl' => 41.27]);

    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, 1);
    Queue::assertPushed(ComputeContrastReserveForObjectsBatch::class, fn ($job) => $job->objectNames === ['Near Galaxy']);

    // The row waits for the job instead of computing and storing the value.
    $contrast = $table->instance()->fields()->fields['computed_contrast_reserve'];
    $row = (object) ['name' => 'Near Galaxy', 'mag' => 8.4, 'subr' => 12.0, 'diam1' => 600.0, 'diam2' => 240.0];
    expect($contrast($row))->toContain('…')
        ->and(DB::table('user_object_metrics')->count())->toBe(0);
});

it('computes the observer statistics on the queue for guests and in the request for users', function () {
    $observer = User::factory()->withPersonalTeam()->create();
    insertCachingTestObservation($observer->username, 'M 31');
    registerMysqlFunctions();
    Bus::fake();

    $this->get(route('observer.show', $observer->slug))->assertOk();
    Bus::assertDispatched(ComputeObserverStats::class);
    Bus::assertNotDispatchedSync(ComputeObserverStats::class);

    cache()->forget('observer_stats_in_progress:'.$observer->id);
    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('observer.show', $observer->slug))->assertOk();
    Bus::assertDispatchedSync(ComputeObserverStats::class);
});

it('shares the totals of all observers between the observer statistics', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    insertCachingTestObservation($alice->username, 'M 31');
    insertCachingTestObservation($bob->username, 'M 32', ['has_drawing' => true]);

    (new ComputeObserverStats($alice->id))->handle();
    // Not recounted for the second observer.
    insertCachingTestObservation($bob->username, 'M 33', ['has_drawing' => true]);
    (new ComputeObserverStats($bob->id))->handle();

    $alices = cache('observer_stats:'.$alice->id);
    $bobs = cache('observer_stats:'.$bob->id);
    expect($alices['allTotalObservations'])->toBe(2)
        ->and($bobs['allTotalObservations'])->toBe(2)
        ->and($bobs['allYourObservations'])->toBe(2)
        ->and($bobs['deepskyYourDrawings'])->toBe(2)
        ->and($alices['allYourObservations'])->toBe(1);

    // A saved observation clears the shared totals.
    $observation = \App\Models\Observation::query()->first();
    $observation->touch();
    expect(cache()->has(ComputeObserverStats::SITE_TOTALS_KEY))->toBeFalse();
});

it('caches the counts of the statistics page', function () {
    $observer = User::factory()->create();
    insertCachingTestObservation($observer->username, 'M 31');
    $total = fn () => collect($this->get(route('statistics.index'))->assertOk()->viewData('summaryRows'))
        ->firstWhere('key', 'observations')['values']['total'];

    expect($total())->toBe(1);
    insertCachingTestObservation($observer->username, 'M 32');
    expect($total())->toBe(1);

    cache()->forget('statistics:counts:v2:all');
    expect($total())->toBe(2);
});

it('shows the old counts of the statistics page while they are counted again after the response', function () {
    $observer = User::factory()->create();
    insertCachingTestObservation($observer->username, 'M 31');
    $total = fn () => collect($this->get(route('statistics.index'))->assertOk()->viewData('summaryRows'))
        ->firstWhere('key', 'observations')['values']['total'];

    expect($total())->toBe(1);
    insertCachingTestObservation($observer->username, 'M 32');

    $this->travel(2)->hours();
    expect($total())->toBe(1)
        ->and($total())->toBe(2);

    $this->travel(2)->days();
    insertCachingTestObservation($observer->username, 'M 33');
    expect($total())->toBe(3);
});

it('caches the ranking of the popular objects', function () {
    $observer = User::factory()->create();
    insertCachingTestObject('Popular Galaxy', 0.71, 41.27);
    insertCachingTestObservation($observer->username, 'Popular Galaxy');
    insertCachingTestObservation($observer->username, 'Popular Galaxy');
    insertCachingTestObservation($observer->username, 'Other Galaxy');
    $rows = fn () => app(\App\Services\PopularObjectsRanking::class)->rows('observations');

    $first = $rows();
    expect($first)->toHaveCount(2)
        ->and($first[0]['name'])->toBe('Popular Galaxy')
        ->and($first[0]['count'])->toBe(2)
        ->and($first[0]['slug'])->toBe('popular-galaxy');

    insertCachingTestObservation($observer->username, 'Other Galaxy');
    insertCachingTestObservation($observer->username, 'Other Galaxy');
    expect($rows()[0]['name'])->toBe('Popular Galaxy');

    $this->travel(2)->days();
    expect($rows()[0]['name'])->toBe('Other Galaxy');
});

it('caches the observer ranking', function () {
    $observer = User::factory()->create();
    insertCachingTestObservation($observer->username, 'M 31');
    $count = fn () => collect($this->get(route('observers.index'))->assertOk()->viewData('observerRows')->items())
        ->firstWhere('username', $observer->username)['observations_count'];

    expect($count())->toBe(1);
    insertCachingTestObservation($observer->username, 'M 32');
    expect($count())->toBe(1);
});

it('caches the rss feeds and shows the local time of the location', function () {
    $observer = User::factory()->create();
    $location = LocationFactory::new()->create(['user_id' => $observer->id, 'observer' => $observer->username, 'timezone' => 'Europe/Brussels']);
    insertCachingTestObservation($observer->username, 'M 31', ['observed_on' => '2026-07-01', 'observed_time' => '21:30', 'location_id' => $location->id]);

    $feed = $this->get(route('feed.observations'))->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')->getContent();
    // 21:30 UT is 23:30 summer time in Brussels.
    expect($feed)->toContain('2026-07-01 23:30 CEST');

    insertCachingTestObservation($observer->username, 'M 32');
    expect($this->get(route('feed.observations'))->getContent())->toBe($feed);
});
