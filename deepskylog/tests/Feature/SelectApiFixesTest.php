<?php

use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\Observation;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Illuminate\Support\Facades\Auth;

it('ignores a selected make, type or color that does not exist', function (string $route) {
    $this->actingAs(User::factory()->create());

    $this->getJson(route($route, ['selected' => 999999]))
        ->assertOk()
        ->assertJsonMissing(['id' => 999999]);
})->with([
    'instrument_makes.api',
    'instrument_types.api',
    'mount_types.api',
    'eyepiece_makes.api',
    'eyepiece_types.api',
    'lens_makes.api',
    'filter_makes.api',
    'filter_colors.api',
    'filter_types.api',
]);

it('returns the selected make first', function () {
    $this->actingAs(User::factory()->create());
    $make = EyepieceMake::create(['name' => 'Televue']);

    expect($this->getJson(route('eyepiece_makes.api', ['selected' => $make->id]))->json('0'))
        ->toBe(['id' => $make->id, 'name' => 'Televue']);
});

it('does not fail when the standard atlas of the user does not exist', function () {
    $user = User::factory()->create();
    $user->forceFill(['standardAtlasCode' => 'no-such-atlas'])->save();
    $this->actingAs($user);

    $this->getJson(route('atlas.index', ['selected' => 1]))
        ->assertOk()
        ->assertJsonMissing(['id' => 'no-such-atlas']);
});

it('returns all eyepiece types when no make is given', function () {
    $this->actingAs(User::factory()->create());
    $televue = EyepieceMake::create(['name' => 'Televue']);
    $baader = EyepieceMake::create(['name' => 'Baader']);
    EyepieceType::create(['name' => 'Nagler', 'eyepiece_makes_id' => $televue->id]);
    EyepieceType::create(['name' => 'Hyperion', 'eyepiece_makes_id' => $baader->id]);

    $names = collect($this->getJson(route('eyepiece_types.api'))->json())->pluck('name');
    expect($names->all())->toContain('', 'Nagler', 'Hyperion');

    $names = collect($this->getJson(route('eyepiece_types.api', ['make' => $televue->id]))->json())->pluck('name');
    expect($names->all())->toContain('Nagler')->not->toContain('Hyperion');

    $names = collect($this->getJson(route('eyepiece_types.api', ['search' => 'hyp']))->json())->pluck('name');
    expect($names->all())->toContain('Hyperion')->not->toContain('Nagler');
});

it('returns the full name of eyepieces', function () {
    $user = User::factory()->create();
    $eyepiece = EyepieceFactory::new()->create([
        'user_id' => $user->id,
        'observer' => $user->username,
        'name' => ' Nagler 13mm ',
    ]);
    $this->actingAs($user);

    $selected = $this->getJson(route('eyepiece.select.api', ['selected' => $eyepiece->id]))->json();
    $all = $this->getJson(route('eyepiece.select.api'))->json();

    expect($selected[0]['name'])->toBe($eyepiece->fullName())
        ->and(collect($all)->firstWhere('id', $eyepiece->id)['name'])->toBe($eyepiece->fullName());
});

it('leaves the description out of the eyepiece options', function () {
    $user = User::factory()->create();
    $eyepiece = EyepieceFactory::new()->create([
        'user_id' => $user->id,
        'observer' => $user->username,
        'description' => '<p>Bought second-hand</p>',
    ]);
    $this->actingAs($user);

    // WireUI's select prints an option's description under its name
    foreach ([['selected' => $eyepiece->id], []] as $query) {
        $option = collect($this->getJson(route('eyepiece.select.api', $query))->json())->firstWhere('id', $eyepiece->id);
        expect($option)->not->toHaveKey('description');
    }
});

it('links the mobile admin menu to the eyepiece types page', function () {
    expect(file_get_contents(resource_path('views/components/menu/admin-responsive-dropdown.blade.php')))
        ->toContain('href="/admin/eyepiece-types"')
        ->not->toContain('href="/admin/eyepiece-type"');
});

it('refuses guests on the endpoints that read the logged-in user', function (string $route) {
    $this->getJson(route($route))->assertUnauthorized();
})->with([
    'countries.index',
    'licenses.index',
    'locations.api',
    'location.select.api',
    'instrument.api',
    'instrumentset.api',
    'instrumentset.select.api',
    'instrument.select.api',
    'atlas.index',
    'ui_languages.index',
    'observation_languages.index',
    'addUserToTeam.index',
    'eyepiece_makes.api',
    'eyepiece.select.api',
    'lens.select.api',
    'filter.select.api',
    'magnification.compute',
]);

it('keeps the observer list public for the observation search', function () {
    $observer = User::factory()->create();
    Observation::query()->create([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'observed_on' => '2026-03-01',
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65',
        'has_drawing' => false,
    ]);
    $withoutObservations = User::factory()->create();

    $this->getJson(route('users.select.api'))
        ->assertOk()
        ->assertJsonFragment(['id' => $observer->username])
        ->assertJsonMissing(['id' => $withoutObservations->username])
        ->assertJsonMissing(['email' => $observer->email]);

    // Guests cannot find a username from an e-mail address.
    expect($this->getJson(route('users.select.api', ['selected' => $observer->email]))->json())->toBe([]);
});

it('lists all users to logged-in users, searched in the database', function () {
    $alice = User::factory()->create(['name' => 'Alice Andromeda', 'username' => 'alice_a']);
    $bob = User::factory()->create(['name' => 'Bob Betelgeuse', 'username' => 'bob_b']);
    $this->actingAs($bob);

    $this->getJson(route('users.select.api', ['search' => 'androm']))
        ->assertOk()
        ->assertExactJson([['id' => 'alice_a', 'name' => 'Alice Andromeda']]);

    // A search term with LIKE wildcards matches them literally.
    expect($this->getJson(route('users.select.api', ['search' => '%']))->json())->toBe([]);

    // Users are not looked up by e-mail address, which would tell which
    // addresses are registered.
    expect($this->getJson(route('users.select.api', ['selected' => $alice->email]))->json())->toBe([]);
    $this->getJson(route('users.select.api', ['selected' => 'alice_a']))
        ->assertExactJson([['id' => 'alice_a', 'name' => 'Alice Andromeda']]);

    expect($this->getJson(route('users.select.api', ['selected' => 'bob_b', 'search' => 'alice']))->json('*.id'))
        ->toBe(['bob_b', 'alice_a']);
});

it('keeps the documented equipment api public', function () {
    $user = User::factory()->create();

    $this->getJson('/api/eyepieces/'.$user->username)->assertOk();
});

it('lists the e-mail addresses of users only to administrators', function () {
    $observer = User::factory()->create();
    $this->actingAs($observer)->getJson(route('addUserToTeam.index'))->assertForbidden();

    $admin = $this->createUserAndAssignToTeam('Administrators');
    $this->actingAs($admin)->getJson(route('addUserToTeam.index'))
        ->assertOk()
        ->assertJsonFragment(['id' => $observer->email]);
});

it('accepts the session of a logged-in user on the site pages', function () {
    $user = User::factory()->create();

    $this->withSession([Auth::guard('web')->getName() => $user->id])
        ->withHeader('Referer', config('app.url').'/profile')
        ->getJson(route('countries.index', ['lang' => 'en']))
        ->assertOk();
});
