<?php

use App\Mcp\Servers\DeepskyLogServer;
use App\Mcp\Tools\CreateObservingList;
use App\Mcp\Tools\GetCatalogProgress;
use App\Mcp\Tools\GetMyProfile;
use App\Mcp\Tools\GetMyStatistics;
use App\Mcp\Tools\GetObject;
use App\Mcp\Tools\GetObservation;
use App\Mcp\Tools\GetObservingList;
use App\Mcp\Tools\ListMyGoals;
use App\Mcp\Tools\ListMyInstrumentSets;
use App\Mcp\Tools\ListMyObservations;
use App\Mcp\Tools\ListMyObservingLists;
use App\Mcp\Tools\SearchObjects;
use App\Mcp\Tools\UpdateObservingList;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\InstrumentSetFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

function mcpObject(string $name, string $type = 'GALXY', array $attributes = []): void
{
    DB::table('deepskytypes')->insertOrIgnore([['code' => 'GALXY', 'name' => 'Galaxy'], ['code' => 'DS', 'name' => 'Double star']]);
    DB::table('constellations')->insertOrIgnore(['id' => 'LEO', 'name' => 'Leo']);
    $slug = Str::slug($name);
    DB::table('objects')->insert($attributes + [
        'name'           => $name, 'slug' => $slug, 'type' => $type, 'con' => 'LEO', 'ra' => 11.3155, 'decl' => 13.0922,
        'mag'            => 9.3, 'subr' => 12.4, 'pa' => 174, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1'          => 600.0, 'diam2' => 180.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa'            => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'          => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('search_index')->insert(['name' => $name, 'source_table' => 'objects', 'source_pk' => $slug, 'display_name' => $name, 'source_type' => $type]);
    [$catalog, $index] = explode(' ', $name, 2);
    DB::table('objectnames')->insertOrIgnore(['objectname' => $name, 'altname' => $name, 'catalog' => $catalog, 'catindex' => $index, 'slug' => $slug, 'timestamp' => now()]);
}

function mcpObservation(User $user, string $object, array $attributes = [], ?int $visibility = 2): Observation
{
    $observation = Observation::create($attributes + [
        'observer_username' => $user->username,
        'observer_user_id'  => $user->id,
        'observed_on'       => '2026-03-01',
        'description'       => '<p>Bright core, &amp; a dust lane</p>',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
    ]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => $visibility, 'magnification' => 120]);
    }

    return $observation;
}

/** An observer with a standard location (SQM 21) and a 250 mm telescope. */
function mcpObserver(): User
{
    // in the Observer role, which makes observing lists
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location   = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 51.0, 'longitude' => 4.5, 'skyBackground' => 21.0, 'timezone' => 'Europe/Brussels']);
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Dob', 'aperture_mm' => 250, 'focal_length_mm' => 1200]);
    $user->forceFill(['stdlocation' => $location->id, 'stdtelescope' => $instrument->id])->save();

    return $user->fresh();
}

function mcpRpc(string $method, array $params = []): array
{
    return ['jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params + [
        'protocolVersion' => '2025-06-18', 'capabilities' => (object) [], 'clientInfo' => ['name' => 'test', 'version' => '1'],
    ]];
}

it('refuses a request without a token', function () {
    $this->postJson('/mcp', mcpRpc('initialize'))->assertUnauthorized();
});

it('refuses a token without the read ability', function () {
    $token = User::factory()->create()->createToken('assistant', [])->plainTextToken;

    $this->withToken($token)->postJson('/mcp', mcpRpc('initialize'))->assertForbidden();
});

it('accepts a personal access token', function () {
    $token = User::factory()->create()->createToken('assistant', ['read'])->plainTextToken;

    $this->withToken($token)->postJson('/mcp', mcpRpc('initialize'))
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'DeepskyLog');
});

it('offers creating a list only to a token that may write', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user, ['read']);
    DeepskyLogServer::tools()->assertRegistered(GetMyProfile::class)->assertNotRegistered(CreateObservingList::class);

    Sanctum::actingAs($user, ['read', 'write']);
    DeepskyLogServer::tools()->assertRegistered(CreateObservingList::class);
});

it('gives the profile with the observer\'s own equipment only', function () {
    $user = mcpObserver();
    InstrumentFactory::new()->create(['name' => 'Somebody elses', 'observer' => 'somebody']);
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(GetMyProfile::class)
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('username', $user->username)
            ->where('standard_instrument_id', $user->stdtelescope)
            ->has('instruments', 1)
            ->where('instruments.0.aperture_mm', 250)
            ->where('locations.0.name', 'Backyard')
            ->where('locations.0.sqm', 21)
            ->etc());
});

it('finds objects in another spelling', function () {
    mcpObject('M 65');
    Sanctum::actingAs(User::factory()->create(), ['read']);

    DeepskyLogServer::tool(SearchObjects::class, ['query' => 'm65'])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('results.0.name', 'M 65')
            ->where('results.0.type', 'Galaxy')
            ->where('results.0.size_arcmin', '10 x 3')
            ->etc());
});

it('describes an object with the observer\'s observations and contrast reserve', function () {
    mcpObject('M 65');
    $user = mcpObserver();
    mcpObservation($user, 'M 65', ['observed_on' => '2025-04-01']);
    mcpObservation($user, 'M 65', ['observed_on' => '2026-03-01', 'has_drawing' => true]);
    mcpObservation(User::factory()->create(), 'M 65');
    Sanctum::actingAs($user, ['read']);

    $response = DeepskyLogServer::tool(GetObject::class, ['name' => 'm 65'])->assertOk();
    $response->assertStructuredContent(fn ($json) => $json->where('name', 'M 65')
        ->where('constellation', 'Leo')
        ->where('my_observations.count', 2)
        ->where('my_observations.drawings', 1)
        ->where('my_observations.first_date', '2025-04-01')
        ->where('visibility.instrument', fn ($name) => str_contains($name, 'Dob'))
        ->where('visibility.contrast_reserve', fn ($reserve) => is_numeric($reserve))
        ->etc());
});

it('says when an object is unknown', function () {
    Sanctum::actingAs(User::factory()->create(), ['read']);

    DeepskyLogServer::tool(GetObject::class, ['name' => 'NGC 99999'])->assertHasErrors();
});

it('refuses an instrument of another observer', function () {
    mcpObject('M 65');
    $other = InstrumentFactory::new()->create(['observer' => 'somebody']);
    Sanctum::actingAs(mcpObserver(), ['read']);

    DeepskyLogServer::tool(GetObject::class, ['name' => 'M 65', 'instrument_id' => $other->id])->assertHasErrors();
});

it('lists and filters only the observer\'s own observations', function () {
    mcpObject('M 65');
    mcpObject('M 66');
    $user = mcpObserver();
    mcpObservation($user, 'M 65', ['observed_on' => '2026-01-10']);
    mcpObservation($user, 'M 66', ['observed_on' => '2026-02-10'], visibility: 7);
    mcpObservation(User::factory()->create(), 'M 65');
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(ListMyObservations::class)
        ->assertStructuredContent(fn ($json) => $json->where('total', 2)
            ->where('observations.0.object', 'M 66')
            ->where('observations.1.description', 'Bright core, & a dust lane')
            ->etc());
    DeepskyLogServer::tool(ListMyObservations::class, ['seen' => true])
        ->assertStructuredContent(fn ($json) => $json->where('total', 1)->where('observations.0.object', 'M 65')->etc());
    DeepskyLogServer::tool(ListMyObservations::class, ['object' => 'm66', 'constellation' => 'LEO'])
        ->assertStructuredContent(fn ($json) => $json->where('total', 1)->etc());
});

it('gives one observation only to its observer', function () {
    mcpObject('M 65');
    $user  = mcpObserver();
    $own   = mcpObservation($user, 'M 65');
    $other = mcpObservation(User::factory()->create(), 'M 65');
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(GetObservation::class, ['id' => $own->id])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('object', 'M 65')->where('details.magnification', 120)->etc());
    DeepskyLogServer::tool(GetObservation::class, ['id' => $other->id])->assertHasErrors();
});

it('counts the statistics', function () {
    mcpObject('M 65');
    mcpObject('M 66');
    $user = mcpObserver();
    mcpObservation($user, 'M 65', ['observed_on' => '2025-01-10']);
    mcpObservation($user, 'M 65', ['observed_on' => '2026-01-10', 'has_drawing' => true]);
    mcpObservation($user, 'M 66', ['observed_on' => '2026-02-10'], visibility: 7);
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(GetMyStatistics::class)
        ->assertStructuredContent(fn ($json) => $json->where('observations', 3)
            ->where('different_deepsky_objects_seen', 1)
            ->where('drawings', 1)
            ->where('per_year.1.year', 2026)
            ->where('per_year.1.observations', 2)
            ->etc());
    DeepskyLogServer::tool(GetMyStatistics::class, ['year' => 2025])
        ->assertStructuredContent(fn ($json) => $json->where('observations', 1)->etc());
});

it('gives the progress in a catalogue with the objects still to see', function () {
    mcpObject('Tst 1');
    mcpObject('Tst 2');
    $user = mcpObserver();
    mcpObservation($user, 'Tst 1');
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(GetCatalogProgress::class, ['catalog' => 'Tst'])
        ->assertStructuredContent(fn ($json) => $json->where('objects', 2)
            ->where('seen', 1)
            ->where('still_unseen', 1)
            ->where('unseen_objects.0.name', 'Tst 2')
            ->etc());
    DeepskyLogServer::tool(GetCatalogProgress::class, ['catalog' => 'Nonexistent'])->assertHasErrors();
});

it('lists the observing lists and gives the objects with what was seen', function () {
    mcpObject('M 65');
    mcpObject('M 66');
    $user = mcpObserver();
    mcpObservation($user, 'M 66');
    $list    = ObservingList::createWithObjects($user, 'Leo triplet', ['M 65', 'M 66']);
    $private = ObservingList::createWithObjects(User::factory()->create(), 'Private', ['M 65']);
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(ListMyObservingLists::class)
        ->assertStructuredContent(fn ($json) => $json->where('returned', 1)->where('lists.0.objects', 2)->etc());
    DeepskyLogServer::tool(GetObservingList::class, ['id' => $list->id, 'only_unseen' => true])
        ->assertStructuredContent(fn ($json) => $json->where('seen_by_me', 1)->has('objects', 1)->where('objects.0.name', 'M 65')->etc());
    DeepskyLogServer::tool(GetObservingList::class, ['id' => $private->id])->assertHasErrors();
});

it('lists the goals', function () {
    $user = mcpObserver();
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(ListMyGoals::class)->assertOk()->assertStructuredContent(fn ($json) => $json->where('count', 0)->etc());
});

it('creates an observing list with a token that may write', function () {
    mcpObject('M 65');
    mcpObject('M 66');
    $user = mcpObserver();
    Sanctum::actingAs($user, ['read', 'write']);

    DeepskyLogServer::tool(CreateObservingList::class, ['name' => 'For tonight', 'objects' => ['m66', 'M65', 'Nothing 1']])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('objects_added', 2)->where('unknown_names_left_out', ['Nothing 1'])->etc());

    $list = ObservingList::query()->where('owner_user_id', $user->id)->sole();
    expect($list->public)->toBeFalse()
        ->and($list->items()->orderBy('sort_order')->pluck('object_name')->all())->toBe(['M 66', 'M 65']);
});

it('does not create a list with a token that only reads', function () {
    mcpObject('M 65');
    $user = mcpObserver();
    Sanctum::actingAs($user, ['read']);

    // not offered (tools/list), and refused when called anyway
    DeepskyLogServer::tool(CreateObservingList::class, ['name' => 'x', 'objects' => ['M 65']])->assertHasErrors();
    expect(ObservingList::query()->where('owner_user_id', $user->id)->exists())->toBeFalse();
});

it('lists the observer\'s own instrument sets with their magnifications', function () {
    $user     = mcpObserver();
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Wide', 'focal_length_mm' => 30, 'apparentFOV' => 68, 'field_stop_mm' => null, 'active' => true]);
    $lens     = LensFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Barlow', 'factor' => 2, 'active' => true]);
    $set      = InstrumentSetFactory::new()->create(['user_id' => $user->id, 'name' => 'Home kit']);
    $set->instruments()->attach($user->stdtelescope);
    $set->eyepieces()->attach($eyepiece->id);
    $set->lenses()->attach($lens->id);
    $user->forceFill(['stdinstrumentset' => $set->id])->save();
    InstrumentSetFactory::new()->create(['name' => 'Somebody elses']);
    InstrumentSetFactory::new()->create(['user_id' => $user->id, 'name' => 'Old kit', 'active' => false]);
    Sanctum::actingAs($user->fresh(), ['read']);

    DeepskyLogServer::tool(ListMyInstrumentSets::class)
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('count', 1)
            ->where('standard_instrument_set_id', $set->id)
            ->where('instrument_sets.0.name', 'Home kit')
            ->where('instrument_sets.0.standard', true)
            ->where('instrument_sets.0.instruments.0.aperture_mm', 250)
            ->where('instrument_sets.0.lenses.0.factor', 2)
            ->missing('instrument_sets.0.magnifications')
            ->etc());
    DeepskyLogServer::tool(ListMyInstrumentSets::class, ['include_retired' => true])
        ->assertStructuredContent(fn ($json) => $json->where('count', 2)->etc());
    DeepskyLogServer::tool(ListMyInstrumentSets::class, ['id' => $set->id])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->has('magnifications.0.combinations', 2)
            ->where('magnifications.0.combinations.0.magnification', 40)
            ->where('magnifications.0.combinations.0.exit_pupil_mm', 6.3)
            ->where('magnifications.0.combinations.0.true_field_deg', 1.7)
            ->where('magnifications.0.combinations.1.magnification', 80)
            ->where('magnifications.0.combinations.1.lens', fn ($name) => str_contains($name, 'Barlow'))
            ->etc());
});

it('refuses an instrument set of another observer', function () {
    $other = InstrumentSetFactory::new()->create();
    Sanctum::actingAs(mcpObserver(), ['read']);

    DeepskyLogServer::tool(ListMyInstrumentSets::class, ['id' => $other->id])->assertHasErrors();
});

it('filters the lists on public and description and gives the description', function () {
    mcpObject('M 65');
    $user  = mcpObserver();
    $bare  = ObservingList::createWithObjects($user, 'Bare public', ['M 65'], null, true);
    $blank = ObservingList::createWithObjects($user, 'Blank public', ['M 65'], '  ', true);
    ObservingList::createWithObjects($user, 'Described public', ['M 65'], "Galaxies\nfor spring", true);
    ObservingList::createWithObjects($user, 'Bare private', ['M 65']);
    Sanctum::actingAs($user, ['read']);

    DeepskyLogServer::tool(ListMyObservingLists::class, ['own_only' => true, 'public' => true, 'has_description' => false])
        ->assertStructuredContent(fn ($json) => $json->where('returned', 2)
            ->where('lists', fn ($lists) => collect($lists)->pluck('id')->sort()->values()->all() === [$bare->id, $blank->id])
            ->where('lists.0.description', '')->etc());
    DeepskyLogServer::tool(ListMyObservingLists::class, ['has_description' => true])
        ->assertStructuredContent(fn ($json) => $json->where('returned', 1)->where('lists.0.description', 'Galaxies for spring')->etc());
    DeepskyLogServer::tool(GetObservingList::class, ['id' => $bare->id])
        ->assertStructuredContent(fn ($json) => $json->where('description', '')->where('has_description', false)->etc());
});

it('changes the description and publicity of an own list with a token that may write', function () {
    mcpObject('M 65');
    $user  = mcpObserver();
    $list  = ObservingList::createWithObjects($user, 'Thin galaxies', ['M 65'], null, true);
    $other = ObservingList::createWithObjects(User::factory()->create(), 'Not mine', ['M 65'], 'Theirs', true);

    Sanctum::actingAs($user, ['read']);
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'description' => 'x'])->assertHasErrors();
    expect($list->fresh()->description)->toBeNull();

    Sanctum::actingAs($user, ['read', 'write']);
    DeepskyLogServer::tools()->assertRegistered(UpdateObservingList::class);
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'description' => "Edge-on galaxies\n<b>thin</b>"])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('description', "Edge-on galaxies\nthin")->where('previous.description', '')->where('public', true)->etc());
    expect($list->fresh()->description)->toBe("Edge-on galaxies\nthin");

    // publicity alone keeps the description
    $subscriber = User::factory()->create();
    $list->subscriptions()->create(['user_id' => $subscriber->id]);
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'public' => false])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('public', false)->where('previous.public', true)->where('subscriptions_ended', 1)->etc());
    expect($list->fresh())->public->toBeFalse()->description->toBe("Edge-on galaxies\nthin")
        ->and($list->subscriptions()->count())->toBe(0);

    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'public' => true, 'description' => ''])->assertOk();
    expect($list->fresh())->public->toBeTrue()->description->toBeNull();

    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id])->assertHasErrors();
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $other->id, 'public' => false])->assertHasErrors();
    expect($other->fresh()->public)->toBeTrue();
});

it('renames an own list with a token that may write, keeping its link', function () {
    mcpObject('M 65');
    $user  = mcpObserver();
    $list  = ObservingList::createWithObjects($user, 'Thin galaxies', ['M 65'], 'Edge-on', true);
    $taken = ObservingList::createWithObjects($user, 'Friday night', ['M 65']);
    $slug  = $list->slug;

    Sanctum::actingAs($user, ['read']);
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'name' => 'Flat galaxies'])->assertHasErrors();

    Sanctum::actingAs($user, ['read', 'write']);
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'name' => '  Flat <b>galaxies</b> '])
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('name', 'Flat galaxies')->where('previous.name', 'Thin galaxies')->where('description', 'Edge-on')->where('public', true)->etc());
    expect($list->fresh())->name->toBe('Flat galaxies')->slug->toBe($slug)->description->toBe('Edge-on');

    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'name' => 'friday night'])->assertHasErrors();
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'name' => ' '])->assertHasErrors();
    DeepskyLogServer::tool(UpdateObservingList::class, ['id' => $list->id, 'name' => 'Flat galaxies'])->assertOk();
    expect($list->fresh()->name)->toBe('Flat galaxies')
        ->and($taken->fresh()->name)->toBe('Friday night');
});
