<?php

use App\Http\Controllers\ObservationsController;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The page at the eyepiece of an observing list with times
// (AtTheEyepieceController, AtTheEyepiece): the current and next object by the
// clock, "Log it" with the observation form filled in, and the observations
// the page keeps while offline and sends when the connection returns.

function aeObject(string $name, float $ra, float $decl, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 8.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function aeObserver(): User
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Leuven',
        'latitude'                                        => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'active' => true]);
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => '', 'name' => 'Dobson', 'aperture_mm' => 300]);
    $user->forceFill(['stdlocation' => $location->id, 'stdtelescope' => $instrument->id])->save();

    return $user->fresh();
}

/** M 31 at 20:30 UT and M 42 at 22:00 UT in the night of $night. */
function aeList(User $owner, string $night = '2026-10-26', bool $public = false): ObservingList
{
    if (! DB::table('objects')->where('name', 'M 31')->exists()) {
        aeObject('M 31', 0.712, 41.27);
        aeObject('M 42', 5.588, -5.39);
    }
    $at = fn (string $time) => Carbon::parse($night.' '.$time, 'UTC')->timestamp;

    return ObservingList::createWithObjects($owner, 'Tonight', ['M 31', 'M 42', 'M 13'], null, $public, [
        'M 31' => ['at' => $at('20:30'), 'from' => $at('18:00'), 'until' => $at('23:59'), 'note' => 'Look for M 110'],
        'M 42' => ['at' => $at('22:00')],
    ], ['night' => $night, 'timezone' => 'Europe/Brussels', 'location_id' => $owner->stdlocation]);
}

beforeEach(fn () => Carbon::setTestNow('2026-10-26 21:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('shows the object whose time has come and the next one', function () {
    $owner = aeObserver();
    $list = aeList($owner);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 31')
        ->assertViewHas('next', fn ($next) => $next['name'] === 'M 42')
        ->assertViewHas('following', true)
        ->assertViewHas('untimed', 1)
        ->assertSee('Look for M 110')
        ->assertSee('Log it')
        ->assertSee('<svg', false);
});

it('shows a chosen object of the list', function () {
    $owner = aeObserver();
    $list = aeList($owner);
    $m42 = $list->items()->where('object_name', 'M 42')->first();

    $this->actingAs($owner)->get(route('observing-list.eyepiece', ['list' => $list, 'item' => $m42->id]))
        ->assertOk()
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 42')
        ->assertViewHas('previous', fn ($previous) => $previous['name'] === 'M 31')
        ->assertViewHas('next', null)
        ->assertViewHas('following', false);
});

it('moves the times of another night to tonight and says so', function () {
    $owner = aeObserver();
    $list = aeList($owner, '2026-10-20');

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertViewHas('shift_days', 6)
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 31' && $current['at']->utc()->format('Y-m-d H:i') === '2026-10-26 20:30')
        ->assertSee('The times were planned for the night of');
});

it('shows a list without times in its own order, from the first object not observed tonight', function () {
    $owner = aeObserver();
    aeObject('M 31', 0.712, 41.27);
    aeObject('M 42', 5.588, -5.39);
    $list = ObservingList::createWithObjects($owner, 'No times', ['M 42', 'M 31', 'M 13']);
    Observation::create([
        'observer_username' => $owner->username, 'observer_user_id' => $owner->id, 'observed_on' => '2026-10-26', 'observed_time' => '20:10:00',
        'target_table' => 'objects', 'target_key' => 'm-42', 'target_type' => 'deepsky', 'target_name' => 'M 42', 'description' => 'Seen',
    ]);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertViewHas('timed', false)
        ->assertViewHas('following', false)
        ->assertViewHas('entries', fn ($entries) => $entries->pluck('name')->all() === ['M 42', 'M 31', 'M 13'])
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 31' && $current['at'] === null)
        ->assertViewHas('previous', fn ($previous) => $previous['name'] === 'M 42' && $previous['done'])
        ->assertViewHas('next', fn ($next) => $next['name'] === 'M 13')
        ->assertSee('This list has no times')
        ->assertDontSee('Follow the clock')
        ->assertSee('<svg', false);

    $m13 = $list->items()->where('object_name', 'M 13')->first();
    $this->actingAs($owner)->get(route('observing-list.eyepiece', ['list' => $list, 'item' => $m13->id]))
        ->assertOk()
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 13')
        ->assertSee('There is no finder chart for M 13.');
});

it('sends an empty list back to the list page', function () {
    $owner = aeObserver();
    $list = ObservingList::createWithObjects($owner, 'Empty', []);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertRedirect(route('observing-list.show', $list));
});

it('keeps private lists to their owner', function () {
    $list = aeList(aeObserver());

    $this->actingAs(aeObserver())->get(route('observing-list.eyepiece', $list))->assertForbidden();
});

it('opens the observation form filled in for now with "Log it"', function () {
    $owner = aeObserver();
    $list = aeList($owner);
    $m31 = $list->items()->where('object_name', 'M 31')->first();

    $response = $this->actingAs($owner)->get(route('observing-list.eyepiece.log', ['list' => $list, 'item' => $m31->id]));

    // 21:00 UT is 22:00 in Brussels (winter time from 25 October)
    $response->assertRedirect(route('observation.create', [
        'slug' => 'm-31',
        'observed_on' => '2026-10-26',
        'observed_time' => '22:00',
        'location' => $owner->stdlocation,
        'instrument' => $owner->stdtelescope,
        'return_to' => '/observing-list/'.$list->slug.'/eyepiece?item='.$m31->id,
    ]));
});

it('starts the observation form with the time and equipment of the query', function () {
    $owner = aeObserver();
    aeObject('M 31', 0.712, 41.27);
    $foreign = InstrumentFactory::new()->create(['observer' => '']);

    $this->actingAs($owner)->get(route('observation.create', [
        'slug' => 'm-31', 'observed_on' => '2026-10-26', 'observed_time' => '22:05',
        'instrument' => $foreign->id, 'return_to' => '/observing-list/tonight/eyepiece?item=4',
    ]))
        ->assertOk()
        ->assertViewHas('prefill', fn ($prefill) => $prefill['observed_on'] === '2026-10-26' && $prefill['observed_time'] === '22:05' && ! isset($prefill['instrument_id']))
        ->assertSee('value="22:05"', false)
        ->assertSee('name="return_to" value="/observing-list/tonight/eyepiece?item=4"', false);
});

it('only returns to the page at the eyepiece after saving', function (string $value, ?string $expected) {
    expect(ObservationsController::returnUrl($value))->toBe($expected);
})->with([
    ['/observing-list/tonight/eyepiece?item=4', '/observing-list/tonight/eyepiece?item=4'],
    ['/observing-list/tonight/eyepiece', '/observing-list/tonight/eyepiece'],
    ['//evil.example/observing-list/x/eyepiece', null],
    ['https://evil.example/observing-list/x/eyepiece', null],
    ['/observing-list/tonight/eyepiece?item=4&next=//evil.example', null],
    ['/observations', null],
]);

it('returns to the page at the eyepiece after saving the form', function () {
    $owner = aeObserver();
    $list = aeList($owner);
    $m31 = $list->items()->where('object_name', 'M 31')->first();
    $returnTo = '/observing-list/'.$list->slug.'/eyepiece?item='.$m31->id;

    $this->actingAs($owner)->post(route('observation.store'), [
        'target_table' => 'objects', 'target_slug' => 'm-31', 'observed_on' => '2026-10-26', 'observed_time' => '21:40',
        'description' => 'Seen', 'instrument_id' => $owner->stdtelescope, 'location_id' => $owner->stdlocation, 'return_to' => $returnTo,
    ])->assertRedirect($returnTo)->assertSessionHas('success');

    expect(Observation::where('observer_user_id', $owner->id)->value('observed_time'))->toStartWith('20:40');
});

it('saves an observation noted at the eyepiece once, also when it is sent twice', function () {
    $owner = aeObserver();
    $list = aeList($owner);
    $m31 = $list->items()->where('object_name', 'M 31')->first();
    $payload = ['item' => $m31->id, 'observed_at' => '2026-10-26T20:41:30Z', 'description' => "Bright core\nDust lane", 'visibility' => 2, 'user' => $owner->id];

    $first = $this->actingAs($owner)->postJson(route('observing-list.eyepiece.store', $list), $payload)->assertCreated();
    $this->actingAs($owner)->postJson(route('observing-list.eyepiece.store', $list), $payload)
        ->assertOk()->assertJson(['id' => $first->json('id'), 'updated' => true]);

    $observation = Observation::findOrFail($first->json('id'));
    expect(Observation::where('observer_user_id', $owner->id)->count())->toBe(1)
        ->and($observation->target_name)->toBe('M 31')
        ->and(substr((string) $observation->observed_on, 0, 10))->toBe('2026-10-26')
        ->and($observation->observed_time)->toStartWith('20:41')
        ->and((int) $observation->instrument_id)->toBe((int) $owner->stdtelescope)
        ->and((int) $observation->location_id)->toBe((int) $owner->stdlocation)
        ->and($observation->description)->toContain('Bright core')
        ->and(DB::table('deepsky_observation_details')->where('observation_id', $observation->id)->value('visibility'))->toBe(2);

    // The page now marks it as observed tonight
    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertViewHas('current', fn ($current) => $current['name'] === 'M 31' && $current['done']);
});

it('refuses an observation noted by another observer or of an object not in the list', function () {
    $owner = aeObserver();
    $list = aeList($owner);
    $m31 = $list->items()->where('object_name', 'M 31')->first();
    $other = aeList(aeObserver());

    $this->actingAs($owner)->postJson(route('observing-list.eyepiece.store', $list), [
        'item' => $m31->id, 'observed_at' => '2026-10-26T20:41:00Z', 'description' => 'Seen', 'user' => $owner->id + 1000,
    ])->assertStatus(409);

    $this->actingAs($owner)->postJson(route('observing-list.eyepiece.store', $list), [
        'item' => $other->items()->first()->id, 'observed_at' => '2026-10-26T20:41:00Z', 'description' => 'Seen',
    ])->assertStatus(422);

    $this->actingAs($owner)->postJson(route('observing-list.eyepiece.store', $list), [
        'item' => $m31->id, 'observed_at' => '2026-10-27T20:41:00Z', 'description' => 'Seen',
    ])->assertStatus(422);

    expect(Observation::count())->toBe(0);
});

it('adds the red night mode to every page', function () {
    $this->get('/')->assertOk()->assertSee('dsl-night-mode')->assertSee('data-dsl-night-toggle', false);
});

it('links the current object to SkySafari and the list to its skylist export', function () {
    $owner = aeObserver();
    $list = aeList($owner);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertSee('href="skyobject://M%2031"', false)
        ->assertSee('Show in SkySafari')
        ->assertSee(route('observing-list.export.skylist', $list), false);
});

it('links the current object to the GoTo app with its J2000 position', function () {
    $owner = aeObserver();
    $list = aeList($owner);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertViewHas('goto', 'dslgoto://goto?name=M%2031&ra=10.68&dec=41.27&epoch=J2000')
        ->assertSee('href="dslgoto://goto?name=M%2031&amp;ra=10.68&amp;dec=41.27&amp;epoch=J2000"', false);
});

it('links a planet to the GoTo app with its position of the date at its time', function () {
    $owner = aeObserver();
    DB::table('planets')->insert(['name' => 'Jupiter', 'slug' => 'jupiter', 'designation' => 'Jupiter', 'body_type' => 'planet', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('search_index')->insert([
        'name' => 'Jupiter', 'name_normalized' => 'jupiter', 'source_table' => 'planets', 'display_name' => 'Jupiter',
        'source_pk' => (string) DB::table('planets')->where('slug', 'jupiter')->value('id'), 'source_type' => 'planet',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $list = ObservingList::createWithObjects($owner, 'Planets', ['Jupiter'], null, false);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertOk()
        ->assertViewHas('goto', function (?string $link) {
            parse_str((string) parse_url((string) $link, PHP_URL_QUERY), $query);

            // Jupiter in Leo at the end of October 2026: 9h 45m, +14°
            return str_starts_with((string) $link, 'dslgoto://goto?')
                && $query['name'] === 'Jupiter' && $query['epoch'] === 'date'
                && (float) $query['ra'] > 144 && (float) $query['ra'] < 148
                && (float) $query['dec'] > 13 && (float) $query['dec'] < 16;
        });
});

it('lists the observer\'s lists and subscribed lists with objects to take to the telescope', function () {
    $owner = aeObserver();
    $planned = aeList($owner);
    $plain = ObservingList::createWithObjects($owner, 'Galaxies', ['M 31']);
    ObservingList::createWithObjects($owner, 'Empty', []);
    $other = aeObserver();
    $subscribed = ObservingList::createWithObjects($other, 'Shared', ['M 42'], null, true);
    $subscribed->subscriptions()->create(['user_id' => $owner->id]);
    ObservingList::createWithObjects($other, 'Private of another', ['M 42']);
    $owner->forceFill(['active_observing_list_id' => $plain->id])->save();

    $this->actingAs($owner)->get(route('observing-lists.eyepiece'))
        ->assertOk()
        ->assertViewHas('planned', fn ($lists) => $lists->pluck('id')->all() === [$planned->id])
        ->assertViewHas('other', fn ($lists) => $lists->pluck('id')->all() === [$plain->id, $subscribed->id])
        ->assertSee(route('observing-list.eyepiece', $planned), false)
        ->assertSee('planned for tonight')
        ->assertSee('Active')
        ->assertDontSee('Empty')
        ->assertDontSee('Private of another');
});

it('counts a list planned for an earlier night as another list', function () {
    $owner = aeObserver();
    $list = aeList($owner, '2026-10-20');

    $this->actingAs($owner)->get(route('observing-lists.eyepiece'))
        ->assertViewHas('planned', fn ($lists) => $lists->isEmpty())
        ->assertViewHas('other', fn ($lists) => $lists->pluck('id')->all() === [$list->id])
        ->assertSee('planned for the night of 20 October 2026');
});

it('does not tell the app that it did not open', function () {
    $owner = aeObserver();
    $list = aeList($owner);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))
        ->assertSee('DeepskyLogGoTo', false);
});
