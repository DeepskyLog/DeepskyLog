<?php

use App\Jobs\BuildSessionCopies;
use App\Livewire\CreateSession;
use App\Livewire\PopularSessionsTable;
use App\Models\Location;
use App\Models\ObservationLike;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObservingListItemNameResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function sloLocation(User $owner): Location
{
    return Location::query()->create([
        'user_id' => $owner->id, 'name' => 'Test Site', 'longitude' => 4.5, 'latitude' => 50.5,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 120,
        'country' => 'Belgium', 'active' => true, 'observer' => '', 'observations' => 0, 'slug' => 'test-site',
    ]);
}

function sloSession(User $owner, Location $location, string $name, int $active = 1): ObservationSession
{
    return ObservationSession::query()->create([
        'name' => $name, 'observerid' => $owner->username, 'begindate' => '2026-03-01 00:00:00',
        'enddate' => '2026-03-01 23:59:59', 'locationid' => $location->id, 'weather' => '', 'equipment' => '',
        'comments' => null, 'language' => 'en', 'active' => $active,
    ]);
}

function sloObject(string $name, array $aliases = []): void
{
    $slug = str($name)->slug()->toString();
    DB::table('objects')->insert([
        'name' => $name, 'slug' => $slug, 'type' => 'GALXY', 'con' => 'AND', 'ra' => 0.71, 'decl' => 41.27,
        'mag' => 3.4, 'subr' => 13.5, 'pa' => 35, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 10000.0, 'diam2' => 3000.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    foreach ([$name => 'GALXY'] + array_fill_keys($aliases, 'alias') as $indexName => $type) {
        DB::table('search_index')->insert([
            'name' => $indexName, 'name_normalized' => strtolower($indexName), 'source_table' => 'objects',
            'source_pk' => $slug, 'display_name' => $indexName, 'source_type' => $type,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}

function sloList(User $owner, string $name = 'My list', bool $public = true): ObservingList
{
    return ObservingList::create(['owner_user_id' => $owner->id, 'name' => $name, 'public' => $public]);
}

it('links popular sessions to the observer and leaves drafts out', function () {
    $owner = User::factory()->create(['username' => 'sessowner']);
    $liker = User::factory()->create();
    $location = sloLocation($owner);
    $published = sloSession($owner, $location, 'Published night');
    $draft = sloSession($owner, $location, 'Draft night', 0);
    foreach ([$published, $draft] as $session) {
        ObservationLike::create(['user_id' => $liker->id, 'observation_type' => ObservationLike::TYPE_SESSION, 'observation_id' => $session->id]);
    }

    Livewire::test(PopularSessionsTable::class)
        ->assertSee(route('session.show', [$owner->slug, $published->slug]), false)
        ->assertSee('Published night')
        ->assertDontSee('Draft night');
});

it('lists sessions in the sitemap', function () {
    $owner = User::factory()->create(['username' => 'mapowner']);
    $session = sloSession($owner, sloLocation($owner), 'Mapped night');

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/session/' . $owner->slug . '/' . $session->slug, false);
});

it('only offers "Set as active" to owners and subscribers', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = sloList($owner);

    $this->actingAs($other)->get(route('observing-list.show', $list))->assertOk()->assertDontSee('Set as active');
    $this->actingAs($owner)->get(route('observing-list.show', $list))->assertOk()->assertSee('Set as active');

    $list->subscribers()->attach($other->id);
    $this->actingAs($other)->get(route('observing-list.show', $list))->assertSee('Set as active');
});

it('rejects a duplicate observing list name with a validation error', function () {
    $owner = $this->createUserAndAssignToTeam('Observers');
    $list = sloList($owner, 'Galaxies');
    $other = sloList($owner, 'Nebulae');

    $this->actingAs($owner)->post(route('observing-list.store'), ['name' => 'Galaxies'])
        ->assertSessionHasErrors('name');
    $this->actingAs($owner)->put(route('observing-list.update', $other), ['name' => 'Galaxies'])
        ->assertSessionHasErrors('name');
    // Keeping its own name is fine, and the name is only unique per owner.
    $this->actingAs($owner)->put(route('observing-list.update', $list), ['name' => 'Galaxies', 'description' => 'x'])
        ->assertSessionHasNoErrors();
    sloList(User::factory()->create(), 'Nebulae 2');
    $this->actingAs($owner)->post(route('observing-list.store'), ['name' => 'Nebulae 2'])
        ->assertSessionHasNoErrors();
});

it('adds the search results to the active list under their canonical names', function () {
    $owner = User::factory()->create();
    $list = sloList($owner);
    app(\App\Services\ActiveObservingListService::class)->setActiveList($owner, $list);
    sloObject('M 31', ['NGC 224', 'Andromeda Galaxy']);
    sloObject('M 33', ['NGC 598']);

    // The search hits an alias: the object is stored under its own name.
    $this->actingAs($owner)->post(route('observing-list.active.batch-add'), ['search_query' => 'NGC 224'])
        ->assertSessionHas('success');
    expect($list->items()->pluck('object_name')->all())->toBe(['M 31']);

    // Wildcards work as they do in the search results.
    $this->actingAs($owner)->post(route('observing-list.active.batch-add'), ['search_query' => 'M 3*']);
    expect($list->items()->pluck('object_name')->sort()->values()->all())->toBe(['M 31', 'M 33']);
});

it('resolves typed and imported object names, and reports unknown ones', function () {
    sloObject('M 31', ['NGC 224', 'Andromeda Galaxy']);
    $resolver = app(ObservingListItemNameResolver::class);

    expect($resolver->resolve('M 31'))->toBe('M 31')
        ->and($resolver->resolve('M31'))->toBe('M 31')
        ->and($resolver->resolve('NGC0224'))->toBe('M 31')
        ->and($resolver->resolve('  Andromeda   Galaxy '))->toBe('M 31')
        ->and($resolver->resolve('Not an object'))->toBeNull();

    $owner = User::factory()->create();
    $list = sloList($owner);
    $file = UploadedFile::fake()->createWithContent('list.csv', "M31\nNot an object\nNGC 224\n");

    $this->actingAs($owner)->post(route('observing-list.import', $list), ['file' => $file])
        ->assertSessionHas('warning', fn ($message) => str_contains($message, 'Not an object'));
    expect($list->items()->pluck('object_name')->all())->toBe(['M 31']);

    $this->actingAs($owner)->post(route('observing-list.items.store', $list), ['object_name' => 'Not an object'])
        ->assertSessionHasErrors('object_name');
    expect($list->items()->count())->toBe(1);
});

it('stores sessions in the language their author writes in', function () {
    Bus::fake();
    $owner = User::factory()->create(['username' => 'dutchowner', 'observationlanguage' => 'nl']);
    $location = sloLocation($owner);

    Livewire::actingAs($owner)
        ->test(CreateSession::class)
        ->set('name', 'Nacht in de Ardennen')
        ->set('observer', $owner->username)
        ->set('locationid', $location->id)
        ->set('begindate', '2026-03-01')
        ->set('enddate', '2026-03-01')
        ->call('save')
        ->assertHasNoErrors();

    $session = ObservationSession::where('name', 'Nacht in de Ardennen')->firstOrFail();
    expect($session->language)->toBe('nl');

    User::factory()->create(['username' => 'guest', 'observationlanguage' => 'fr']);
    (new BuildSessionCopies($session->id, ['dutchowner', 'guest'], ['guest']))->handle();
    expect(ObservationSession::where('observerid', 'guest')->value('language'))->toBe('nl');
});

it('rejects session names longer than the column', function () {
    $owner = User::factory()->create();
    $location = sloLocation($owner);

    Livewire::actingAs($owner)
        ->test(CreateSession::class)
        ->set('name', str_repeat('a', 201))
        ->set('observer', $owner->username)
        ->set('locationid', $location->id)
        ->set('begindate', '2026-03-01')
        ->set('enddate', '2026-03-01')
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

it('takes back the point of an observing list like when it is withdrawn', function () {
    $owner = User::factory()->create();
    $liker = User::factory()->create();
    $list = sloList($owner);
    $owner->addPoints(5);

    foreach (range(1, 3) as $_) {
        ObservingList::find($list->id)->toggleLike($liker);
        ObservingList::find($list->id)->toggleLike($liker);
    }

    expect($owner->fresh()->getPoints())->toBe(5)
        ->and($list->fresh()->likes_count)->toBe(0);
});

it('resolves the catalogue spellings of other programs', function () {
    sloObject('NGC 1275');
    sloObject('B 103');
    sloObject('Sh 2-91');
    sloObject('Hickson 68');
    $resolver = app(ObservingListItemNameResolver::class);

    expect($resolver->resolve('N1275'))->toBe('NGC 1275')
        ->and($resolver->resolve('Barnard 103'))->toBe('B 103')
        ->and($resolver->resolve('Sh2-091'))->toBe('Sh 2-91')
        ->and($resolver->resolve('HCG 68'))->toBe('Hickson 68');
});

it('resolves Perek-Kohoutek designations in their other spellings', function () {
    sloObject('PK 198-06.01');
    sloObject('PK 089-00.01');
    $resolver = app(ObservingListItemNameResolver::class);

    expect($resolver->resolve('PK 198-06.1'))->toBe('PK 198-06.01')
        ->and($resolver->resolve('PK198-6 1'))->toBe('PK 198-06.01')
        ->and($resolver->resolve('pk 89-000.01'))->toBe('PK 089-00.01')
        // The sign is part of the designation, also at latitude 00.
        ->and($resolver->resolve('PK 089+00.1'))->toBeNull();
});

it('renames and merges wrongly stored list items', function () {
    sloObject('M 31', ['NGC 224']);
    $owner = User::factory()->create();
    $list = sloList($owner);
    $other = sloList($owner, 'Other');
    $item = fn (ObservingList $list, string $name, ?string $note = null) => DB::table('observing_list_items')->insertGetId([
        'observing_list_id' => $list->id, 'object_name' => $name, 'item_description' => $note,
        'source_mode' => 'manual', 'added_by_user_id' => $owner->id, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $kept = $item($list, 'M 31', 'Kept note');
    $item($list, 'NGC 224', 'Alias note');
    $item($list, 'Not an object');
    $renamed = $item($other, 'm31');

    $this->artisan('observing-lists:normalize-items')->assertSuccessful();
    expect(DB::table('observing_list_items')->count())->toBe(4);

    $this->artisan('observing-lists:normalize-items', ['--apply' => true])->assertSuccessful();

    expect(DB::table('observing_list_items')->where('observing_list_id', $list->id)->orderBy('id')->pluck('object_name')->all())
        ->toBe(['M 31', 'Not an object'])
        ->and(DB::table('observing_list_items')->where('id', $kept)->value('item_description'))->toBe("Kept note\n\nAlias note")
        ->and(DB::table('observing_list_items')->where('id', $renamed)->value('object_name'))->toBe('M 31');
});
