<?php

use App\Http\Livewire\QuickSearch;
use App\Livewire\AdvancedObjectSearch;
use App\Livewire\CreateLocation;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObjectSearchIndex;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\CreateLocationWithoutLookups;

// Search index, quick search and the object/location forms (functionality review 2026-09-28).

function searchIdxObject(string $name, string $slug): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'LEO',
        'ra' => 11.3155,
        'decl' => 13.0922,
        'mag' => 9.3,
        'subr' => 12.4,
        'pa' => 174,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 600.0,
        'diam2' => 180.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0.0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '0',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => '2026-01-01 00:00:00',
    ]);
}

function searchIdxAlias(string $objectName, string $alias, string $slug): void
{
    DB::table('objectnames')->insert([
        'objectname' => $objectName,
        'catalog' => strtok($alias, ' '),
        'catindex' => trim((string) strstr($alias, ' ')),
        'altname' => $alias,
        'slug' => $slug,
        'timestamp' => '2026-01-01 00:00:00',
    ]);
}

function searchIdxRows(): array
{
    return DB::table('search_index')
        ->orderBy('source_table')->orderBy('name')
        ->get()
        ->map(fn ($r) => $r->source_table . '|' . $r->source_type . '|' . $r->name . '|' . $r->source_pk)
        ->all();
}

beforeEach(function () {
    // search:reindex keeps its last-run timestamp in storage/app
    $this->storage = sys_get_temp_dir() . '/dsl-search-index-' . uniqid();
    mkdir($this->storage . '/app', 0777, true);
    $this->app->useStoragePath($this->storage);

    searchIdxObject('M 65', 'm-65');
    searchIdxAlias('M 65', 'NGC 3623', 'ngc-3623');
    searchIdxAlias('M 65', 'UGC 6328', 'ugc-6328');
    DB::table('cometobjects')->insert(['name' => '1P/Halley', 'slug' => '1p-halley', 'icqname' => '', 'timestamp' => now()]);
});

afterEach(function () {
    @unlink($this->storage . '/app/search_index_last_reindex.txt');
    @rmdir($this->storage . '/app');
    @rmdir($this->storage);
});

it('answers /api/search without substring=1, with and without filters', function () {
    Artisan::call('search:reindex');

    $this->getJson('/api/search?q=NGC')->assertOk()->assertJsonFragment(['name' => 'NGC 3623']);
    $this->getJson('/api/search?q=M&object_type=GALXY')->assertOk()->assertJsonFragment(['name' => 'M 65']);
    $this->getJson('/api/search?q=NGC&object_type=alias&source=objects')->assertOk()->assertJsonFragment(['source_pk' => 'm-65']);
    $this->getJson('/api/search?q=1P&object_type=comet')->assertOk()->assertJsonFragment(['name' => '1P/Halley']);
    $this->getJson('/api/search?q=M&object_type=galaxy')->assertStatus(400);
    $this->getJson('/api/search?q=M&source=users')->assertStatus(400);
});

it('opens the object of an alias from the quick search', function () {
    DB::table('search_index')->truncate();

    Livewire::test(QuickSearch::class)
        ->set('query', 'NGC 3623')
        ->call('submit')
        ->assertRedirect(route('object.show', ['slug' => 'm-65']));
});

it('suggests the object of an alias when the search index is empty', function () {
    DB::table('search_index')->truncate();

    $suggestions = Livewire::test(QuickSearch::class)->set('query', 'UGC 63')->get('suggestions');

    expect(collect($suggestions)->pluck('slug')->all())->toContain('m-65');
});

it('keeps the other aliases of an object in an incremental reindex', function () {
    Artisan::call('search:reindex');
    $afterFull = searchIdxRows();
    expect($afterFull)->toContain('objects|alias|NGC 3623|m-65', 'objects|alias|UGC 6328|m-65', 'objects|GALXY|M 65|m-65', 'cometobjects|comet|1P/Halley|1');

    DB::table('objectnames')->where('altname', 'UGC 6328')->update(['timestamp' => now()->addMinute()]);
    Artisan::call('search:reindex', ['--incremental' => true]);
    expect(searchIdxRows())->toBe($afterFull);

    DB::table('objects')->where('name', 'M 65')->update(['timestamp' => now()->addMinute()]);
    Artisan::call('search:reindex', ['--incremental' => true]);
    expect(searchIdxRows())->toBe($afterFull);
});

it('stores the start of every reindex as the last run', function () {
    $this->travelTo('2026-09-28 03:00:00');
    Artisan::call('search:reindex');

    expect(file_get_contents(storage_path('app/search_index_last_reindex.txt')))->toBe('2026-09-28 03:00:00');
});

it('updates the search index, the slug and the observations when an object is renamed', function () {
    ObjectSearchIndex::syncObject('M 65');
    $user = User::factory()->create();
    $observation = Observation::create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-03-01',
        'description' => 'Seen',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
    ]);

    $admin = $this->createUserAndAssignToTeam('Administrators');
    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'Messier 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'alternative_names' => 'NGC 3623, UGC 6328',
        // The test database has no names in these catalogs yet
        'name_catalogs' => [
            ['name' => 'Messier 65', 'choice' => 'new'],
            ['name' => 'NGC 3623', 'choice' => 'new'],
            ['name' => 'UGC 6328', 'choice' => 'new'],
        ],
    ])->assertSessionHasNoErrors();

    $object = DB::table('objects')->where('name', 'Messier 65')->first();
    expect($object->slug)->toBe('messier-65')
        ->and($object->timestamp)->toStartWith(now()->toDateString())
        ->and(searchIdxRows())->toBe([
            'objects|GALXY|Messier 65|messier-65',
            'objects|alias|NGC 3623|messier-65',
            'objects|alias|UGC 6328|messier-65',
        ])
        ->and($observation->fresh()->target_key)->toBe('Messier 65')
        ->and($observation->fresh()->target_name)->toBe('Messier 65');
});

it('removes the object and its aliases from the search index when it is deleted', function () {
    Artisan::call('search:reindex');

    $admin = $this->createUserAndAssignToTeam('Administrators');
    $this->actingAs($admin)->delete(route('object.destroy', ['slug' => 'm-65']))->assertRedirect();

    expect(DB::table('search_index')->whereIn('source_pk', ['m-65', 'M 65'])->count())->toBe(0)
        ->and(searchIdxRows())->toContain('cometobjects|comet|1P/Halley|1');
});

it('keeps the ids of the observing lists in the advanced object search', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    // Ids that are not 0, 1, ... so a renumbering shows
    $first = ObservingList::create(['id' => 17, 'owner_user_id' => $owner->id, 'name' => 'Own list', 'public' => false]);
    $second = ObservingList::create(['id' => 42, 'owner_user_id' => $other->id, 'name' => 'Public list', 'public' => true]);

    $lists = Livewire::actingAs($owner)->test(AdvancedObjectSearch::class)->get('allObservingLists');

    expect(array_keys($lists))->toBe([$first->id, $second->id]);
});

it('saves a location without SQM or NELM', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(CreateLocationWithoutLookups::class)
        ->set('name', 'Backyard')
        ->set('description', 'Behind the house')
        ->set('latitude', 50.8)
        ->set('longitude', 4.7)
        ->call('save')
        ->assertHasNoErrors();

    $location = Location::where('name', 'Backyard')->first();
    expect($location)->not->toBeNull()
        ->and((float) $location->skyBackground)->toBe(-999.0)
        ->and((float) $location->limitingMagnitude)->toBe(-999.0);
});
