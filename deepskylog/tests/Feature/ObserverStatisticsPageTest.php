<?php

use App\Http\Controllers\ObserverController;
use App\Models\ObservingList;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function insertObserverStatsObject(string $name, string $slug): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
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
        'timestamp' => now(),
    ]);
}

function insertObserverStatsObjectName(string $objectName, string $catalog, string $index, string $slug): void
{
    DB::table('objectnames')->insert([
        'objectname' => $objectName,
        'catalog' => $catalog,
        'catindex' => $index,
        'altname' => $objectName,
        'slug' => $slug,
        'timestamp' => now(),
    ]);
}

function insertObserverStatsObservation(string $observerUsername, string $targetName, string $targetKey, bool $drawing = false, ?string $observedOn = null): int
{
    return (int) DB::table('observations')->insertGetId([
        'observer_username' => $observerUsername,
        'observed_on' => $observedOn ?? now()->toDateString(),
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $targetKey,
        'target_name' => $targetName,
        'has_drawing' => $drawing,
        'language' => 'en',
        'legacy_source' => 'deepsky',
        'legacy_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('renders observer ranking rows for the selected catalog', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObject('M 32', 'm-32');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');
    insertObserverStatsObjectName('M 32', 'M', '32', 'm-32');

    $alice = User::factory()->create(['name' => 'Alice Observer', 'username' => 'alice-observer', 'slug' => 'alice-observer']);
    $bob = User::factory()->create(['name' => 'Bob Observer', 'username' => 'bob-observer', 'slug' => 'bob-observer']);

    insertObserverStatsObservation($alice->username, 'M 31', 'm-31', true);
    insertObserverStatsObservation($alice->username, 'M 32', 'm-32');
    insertObserverStatsObservation($bob->username, 'M 31', 'm-31');

    $response = $this->get(route('observers.index', ['source' => 'catalog:m']));
    $response->assertOk();
    $response->assertViewHas('selectedSource', fn(array $source) => $source['key'] === 'catalog:m');
    $response->assertViewHas('observerRows', function ($rows) use ($alice, $bob) {
        $items = collect($rows->items());
        $aliceRow = $items->firstWhere('username', $alice->username);
        $bobRow = $items->firstWhere('username', $bob->username);

        return $rows->perPage() === 25
            && $aliceRow['rank'] === 1
            && $aliceRow['observations_count'] === 2
            && $aliceRow['selected_source_count'] === 2
            && round((float) $aliceRow['selected_source_percentage'], 1) > round((float) $bobRow['selected_source_percentage'], 1)
            && round((float) $bobRow['selected_source_percentage'], 1) > 0.0
            && $bobRow['selected_source_count'] === 1;
    });
});

it('sorts the observer ranking table on requested columns', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObject('M 32', 'm-32');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');
    insertObserverStatsObjectName('M 32', 'M', '32', 'm-32');

    $alice = User::factory()->create(['name' => 'Alice Observer', 'username' => 'alice-sort', 'slug' => 'alice-sort']);
    $bob = User::factory()->create(['name' => 'Bob Observer', 'username' => 'bob-sort', 'slug' => 'bob-sort']);

    insertObserverStatsObservation($alice->username, 'M 31', 'm-31', true);
    insertObserverStatsObservation($bob->username, 'M 32', 'm-32');

    $response = $this->get(route('observers.index', [
        'source' => 'catalog:m',
        'sort' => 'name',
        'direction' => 'desc',
    ]));

    $response->assertOk();
    $response->assertViewHas('currentSort', 'name');
    $response->assertViewHas('currentDirection', 'desc');
    $response->assertViewHas('observerRows', function ($rows) {
        return collect($rows->items())->pluck('username')->values()->all() === ['bob-sort', 'alice-sort'];
    });
});

it('paginates the observer ranking table with 25 observers per page', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');

    for ($index = 1; $index <= 30; $index++) {
        $username = 'pager-observer-' . $index;
        User::factory()->create([
            'name' => 'Pager Observer ' . $index,
            'username' => $username,
            'slug' => $username,
        ]);
        insertObserverStatsObservation($username, 'M 31', 'm-31');
    }

    $defaultResponse = $this->get(route('observers.index', ['source' => 'catalog:m']));
    $defaultResponse->assertOk();
    $defaultResponse->assertViewHas('observerRows', function ($rows) {
        return $rows->perPage() === 25
            && $rows->total() === 30
            && count($rows->items()) === 25;
    });
    $secondPageResponse = $this->get(route('observers.index', ['source' => 'catalog:m', 'page' => 2]));
    $secondPageResponse->assertOk();
    $secondPageResponse->assertViewHas('observerRows', function ($rows) {
        return $rows->currentPage() === 2
            && $rows->perPage() === 25
            && count($rows->items()) === 5;
    });
});

it('filters the observer ranking table by observer name', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');

    $alice = User::factory()->create(['name' => 'Alice Filter', 'username' => 'alice-filter', 'slug' => 'alice-filter']);
    $bob = User::factory()->create(['name' => 'Bob Filter', 'username' => 'bob-filter', 'slug' => 'bob-filter']);

    insertObserverStatsObservation($alice->username, 'M 31', 'm-31');
    insertObserverStatsObservation($bob->username, 'M 31', 'm-31');

    $response = $this->get(route('observers.index', ['source' => 'catalog:m', 'observer' => 'Alice']));

    $response->assertOk();
    $response->assertViewHas('observerSearch', 'Alice');
    $response->assertViewHas('observerRows', function ($rows) use ($alice) {
        $items = collect($rows->items());

        return $rows->total() === 1
            && $items->pluck('username')->all() === [$alice->username];
    });
});

it('renders the observer catalog grid with observed and unobserved object links', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObject('M 32', 'm-32');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');
    insertObserverStatsObjectName('M 32', 'M', '32', 'm-32');

    $observer = User::factory()->create(['name' => 'Catalog Observer', 'username' => 'catalog-observer', 'slug' => 'catalog-observer']);
    insertObserverStatsObservation($observer->username, 'M 31', 'm-31');

    $response = $this->get(route('observer.catalog', ['observer' => $observer->slug, 'source' => 'catalog:m']));

    $response->assertOk();
    $response->assertViewHas('observedCount', 1);
    $response->assertSee(route('observations.user.object', ['observer' => $observer->slug, 'object' => 'm-31']), false);
    $response->assertSee(route('object.show', ['slug' => 'm-32']), false);
});

it('includes owned and subscribed observing lists in the source selector for authenticated users', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');

    Team::forceCreate([
        'user_id' => 1,
        'name' => 'Observers',
        'personal_team' => false,
    ]);

    $owner = User::factory()->create(['name' => 'List Owner', 'username' => 'list-owner', 'slug' => 'list-owner']);
    $viewer = User::factory()->withPersonalTeam()->create(['name' => 'List Viewer', 'username' => 'list-viewer', 'slug' => 'list-viewer']);

    $list = ObservingList::create([
        'owner_user_id' => $owner->id,
        'name' => 'Messier shortlist',
        'slug' => 'messier-shortlist',
        'public' => true,
    ]);

    DB::table('observing_list_subscriptions')->insert([
        'observing_list_id' => $list->id,
        'user_id' => $viewer->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('observing_list_items')->insert([
        'observing_list_id' => $list->id,
        'object_name' => 'M 31',
        'item_description' => null,
        'sort_order' => 1,
        'source_mode' => 'manual',
        'source_observation_id' => null,
        'added_by_user_id' => $owner->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $request = Request::create(route('observers.index'), 'GET', ['source' => 'list:' . $list->id]);
    $request->setUserResolver(fn() => $viewer);

    $response = app(ObserverController::class)->index($request);
    $data = $response->getData();

    expect(collect($data['sourceOptions'])->pluck('key')->all())->toContain('list:' . $list->id);
    expect($data['selectedSource']['key'])->toBe('list:' . $list->id);
    expect(collect($data['sourceOptions'])->pluck('label')->implode(' '))->toContain('Messier shortlist');
});
it('shows the drawing status on the observer catalog grid', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObject('M 32', 'm-32');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');
    insertObserverStatsObjectName('M 32', 'M', '32', 'm-32');

    $observer = User::factory()->create(['name' => 'Drawing Observer', 'username' => 'drawing-observer', 'slug' => 'drawing-observer']);
    insertObserverStatsObservation($observer->username, 'M 31', 'm-31');
    insertObserverStatsObservation($observer->username, 'M 32', 'm-32', true);

    $this->get(route('observer.catalog', ['observer' => $observer->slug, 'source' => 'catalog:m']))
        ->assertOk()
        ->assertViewHas('observedCount', 2);

    $response = $this->get(route('observer.catalog', ['observer' => $observer->slug, 'source' => 'catalog:m', 'mode' => 'drawn']));

    $response->assertOk();
    $response->assertViewHas('mode', 'drawn');
    $response->assertViewHas('observedCount', 1);
    $response->assertSee(route('object.show', ['slug' => 'm-31']), false);
    $response->assertSee(route('observations.user.object', ['observer' => $observer->slug, 'object' => 'm-32']), false);
});

it('only offers guests no observing list from the catalog grid', function () {
    insertObserverStatsObject('M 31', 'm-31');
    insertObserverStatsObjectName('M 31', 'M', '31', 'm-31');

    $observer = User::factory()->create(['username' => 'guest-view', 'slug' => 'guest-view']);

    $this->get(route('observer.catalog', ['observer' => $observer->slug, 'source' => 'catalog:m']))
        ->assertOk()
        ->assertDontSee(route('observer.catalog.list', ['observer' => $observer->slug]), false);

    $this->post(route('observer.catalog.list', ['observer' => $observer->slug]), ['source' => 'catalog:m'])
        ->assertRedirect(route('login'));

    expect(ObservingList::count())->toBe(0);
});

it('creates an observing list with the catalog objects not seen or not drawn yet', function () {
    foreach (['31', '32', '33'] as $index) {
        insertObserverStatsObject('Tst ' . $index, 'tst-' . $index);
        insertObserverStatsObjectName('Tst ' . $index, 'Tst', $index, 'tst-' . $index);
    }

    Team::forceCreate(['user_id' => 1, 'name' => 'Observers', 'personal_team' => false]);
    $viewer = User::factory()->withPersonalTeam()->create(['name' => 'List Maker', 'username' => 'list-maker', 'slug' => 'list-maker']);
    $viewer->forceFill(['current_team_id' => Team::where('name', 'Observers')->value('id')])->save();
    insertObserverStatsObservation($viewer->username, 'Tst 31', 'tst-31', true);
    insertObserverStatsObservation($viewer->username, 'Tst 32', 'tst-32');

    $this->actingAs($viewer)
        ->get(route('observer.catalog', ['observer' => $viewer->slug, 'source' => 'catalog:tst']))
        ->assertSee(route('observer.catalog.list', ['observer' => $viewer->slug]), false);

    $this->actingAs($viewer)
        ->post(route('observer.catalog.list', ['observer' => $viewer->slug]), ['source' => 'catalog:tst'])
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $viewer->id)->sole();
    expect($list->name)->toBe('Tst - not seen yet')
        ->and($list->public)->toBeFalse()
        ->and($list->items()->orderBy('sort_order')->pluck('object_name')->all())->toBe(['Tst 33']);

    $this->actingAs($viewer)
        ->post(route('observer.catalog.list', ['observer' => $viewer->slug]), ['source' => 'catalog:tst', 'mode' => 'drawn'])
        ->assertRedirect();
    $this->actingAs($viewer)
        ->post(route('observer.catalog.list', ['observer' => $viewer->slug]), ['source' => 'catalog:tst', 'mode' => 'drawn'])
        ->assertRedirect();

    $drawn = ObservingList::where('owner_user_id', $viewer->id)->where('name', 'Tst - not drawn yet (2)')->sole();
    expect($drawn->items()->orderBy('sort_order')->pluck('object_name')->all())->toBe(['Tst 32', 'Tst 33']);
});
