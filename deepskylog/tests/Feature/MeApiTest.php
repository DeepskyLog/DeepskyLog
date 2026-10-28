<?php

use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Http\Livewire\ApiTokenManager;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;

function meApiObservation(User $user, array $attributes = []): Observation
{
    return Observation::create($attributes + [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-03-01',
        'description' => 'Seen',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
    ]);
}

it('refuses requests without a token', function (string $route) {
    $this->getJson(route($route))->assertUnauthorized();
})->with(['api.me.profile', 'api.me.locations', 'api.me.observations', 'api.me.sessions', 'api.me.observing-lists']);

it('refuses a token without the read ability', function () {
    Sanctum::actingAs(User::factory()->create(), []);

    $this->getJson(route('api.me.locations'))->assertForbidden();
});

it('accepts a personal access token as bearer token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pydeepskylog', ['read'])->plainTextToken;

    $this->withToken($token)->getJson(route('api.me.profile'))
        ->assertOk()
        ->assertJson(['id' => $user->id, 'username' => $user->username]);
});

it('returns only the locations of the user, hidden ones included', function () {
    $user = User::factory()->create();
    $own = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'skyBackground' => 21.2, 'hidden' => true]);
    LocationFactory::new()->create(['name' => 'Somebody else', 'observer' => 'somebody']);
    $user->forceFill(['stdlocation' => $own->id])->save();
    Sanctum::actingAs($user, ['read']);

    $locations = $this->getJson(route('api.me.locations'))->assertOk()->json();

    expect($locations)->toHaveCount(1)
        ->and($locations[0])->toMatchArray([
            'id' => $own->id,
            'name' => 'Backyard',
            'sqm' => 21.2,
            'limiting_magnitude' => null,
            'hidden' => true,
            'standard' => true,
        ]);
});

it('pages and filters the observations of the user, with their details', function () {
    $user = User::factory()->create();
    $first = meApiObservation($user, ['observed_on' => '2026-01-10']);
    $second = meApiObservation($user, ['observed_on' => '2026-02-10', 'target_name' => 'M 66', 'target_key' => 'M 66']);
    meApiObservation(User::factory()->create());
    DB::table('deepsky_observation_details')->insert(['observation_id' => $second->id, 'visibility' => 2, 'magnification' => 120]);
    Sanctum::actingAs($user, ['read']);

    $page = $this->getJson(route('api.me.observations', ['per_page' => 1]))->assertOk()->json();
    expect($page['total'])->toBe(2)
        ->and($page['last_page'])->toBe(2)
        ->and($page['data'][0]['id'])->toBe($second->id)
        ->and($page['data'][0]['date'])->toBe('2026-02-10')
        ->and($page['data'][0]['details'])->toMatchArray(['visibility' => 2, 'magnification' => 120]);

    $filtered = $this->getJson(route('api.me.observations', ['to' => '2026-01-31']))->json('data');
    expect(collect($filtered)->pluck('id')->all())->toBe([$first->id]);

    expect($this->getJson(route('api.me.observations', ['object' => 'm 66']))->json('total'))->toBe(1);

    $this->getJson(route('api.me.observations', ['per_page' => 10000]))->assertUnprocessable();
});

it('does not show the observation of another observer', function () {
    $user = User::factory()->create();
    $other = meApiObservation(User::factory()->create());
    $own = meApiObservation($user);
    Sanctum::actingAs($user, ['read']);

    $this->getJson(route('api.me.observation', ['id' => $own->id]))->assertOk()->assertJson(['id' => $own->id, 'object' => 'M 65']);
    $this->getJson(route('api.me.observation', ['id' => $other->id]))->assertNotFound();
});

it('returns the sessions of the user with their observations', function () {
    $user = User::factory()->create();
    $observation = meApiObservation($user, ['observed_on' => '2026-03-14']);
    $session = ObservationSession::create([
        'id' => 4711,
        'name' => 'Messier marathon',
        'slug' => 'messier-marathon',
        'observerid' => $user->username,
        'locationid' => 1,
        'active' => 1,
        'begindate' => '2026-03-14 20:00:00',
        'enddate' => '2026-03-15 04:00:00',
        'weather' => '',
        'equipment' => '',
    ]);
    DB::table('sessionObservations')->insert(['sessionid' => $session->id, 'observationid' => $observation->id]);
    DB::table('sessionObservers')->insert(['sessionid' => $session->id, 'observer' => 'friend']);
    Sanctum::actingAs($user, ['read']);

    $sessions = $this->getJson(route('api.me.sessions'))->assertOk()->json('data');
    expect($sessions)->toHaveCount(1)
        ->and($sessions[0])->toMatchArray([
            'id' => 4711,
            'name' => 'Messier marathon',
            'begin' => '2026-03-14 20:00:00',
            'observation_count' => 1,
            'other_observers' => ['friend'],
        ]);

    $this->getJson(route('api.me.session', ['id' => 4711]))
        ->assertOk()
        ->assertJson(['observation_ids' => [$observation->id]]);

    expect($this->getJson(route('api.me.observations', ['session_id' => 4711]))->json('total'))->toBe(1);
});

it('returns the observing lists of the user and the items of a visible list', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $own = ObservingList::create(['owner_user_id' => $user->id, 'name' => 'Galaxies', 'public' => false]);
    ObservingListItem::create(['observing_list_id' => $own->id, 'object_name' => 'M 65', 'sort_order' => 2, 'added_by_user_id' => $user->id]);
    ObservingListItem::create(['observing_list_id' => $own->id, 'object_name' => 'M 66', 'sort_order' => 1, 'added_by_user_id' => $user->id]);
    $public = ObservingList::create(['owner_user_id' => $other->id, 'name' => 'Public list', 'public' => true]);
    $private = ObservingList::create(['owner_user_id' => $other->id, 'name' => 'Private list', 'public' => false]);
    DB::table('observing_list_subscriptions')->insert(['user_id' => $user->id, 'observing_list_id' => $public->id]);
    Sanctum::actingAs($user, ['read']);

    $lists = $this->getJson(route('api.me.observing-lists'))->assertOk()->json();
    expect(collect($lists)->pluck('name')->all())->toBe(['Galaxies'])
        ->and($lists[0])->toMatchArray(['own' => true, 'item_count' => 2]);

    $all = $this->getJson(route('api.me.observing-lists', ['include' => 'subscribed']))->json();
    expect(collect($all)->pluck('name')->all())->toBe(['Galaxies', 'Public list']);

    $items = $this->getJson(route('api.me.observing-list', ['id' => $own->id]))->assertOk()->json('items');
    expect(collect($items)->pluck('object_name')->all())->toBe(['M 66', 'M 65']);

    $this->getJson(route('api.me.observing-list', ['id' => $public->id]))->assertOk();
    $this->getJson(route('api.me.observing-list', ['id' => $private->id]))->assertNotFound();
});

it('returns the equipment and instrument sets of the user', function () {
    $user = User::factory()->create();
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $set = \App\Models\InstrumentSet::factory()->create(['user_id' => $user->id, 'name' => 'Travel kit']);
    $set->locations()->attach($location->id);
    Sanctum::actingAs($user, ['read']);

    foreach (['api.me.instruments', 'api.me.eyepieces', 'api.me.lenses', 'api.me.filters'] as $route) {
        $this->getJson(route($route))->assertOk()->assertJsonIsArray();
    }

    $this->getJson(route('api.me.instrument-sets'))
        ->assertOk()
        ->assertJson([['name' => 'Travel kit', 'location_ids' => [$location->id]]]);
});

it('shows the page on which observers make their API tokens', function () {
    $this->actingAs(User::factory()->withPersonalTeam()->create())
        ->get(route('api-tokens.index'))
        ->assertOk()
        ->assertSee(__('API Tokens'));
});

it('gives every token made on the page the read permission, without permission checkboxes', function () {
    $user = User::factory()->withPersonalTeam()->create();
    $this->actingAs($user);

    Livewire::test(ApiTokenManager::class)
        ->assertDontSee(__('Permissions'))
        ->set('createApiTokenForm.name', 'pydeepskylog')
        ->call('createApiToken')
        ->assertHasNoErrors();

    expect($user->fresh()->tokens()->sole()->abilities)->toBe(['read']);
});
