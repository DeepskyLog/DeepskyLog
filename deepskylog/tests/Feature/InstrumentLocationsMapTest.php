<?php

use App\Models\User;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// The map of the locations an instrument was used at, on the instrument page.

function mapTestLocation(int $userId, string $name, array $attributes = []): int
{
    return (int) DB::table('locations')->insertGetId($attributes + [
        'name' => $name, 'longitude' => 4.35, 'latitude' => 50.85, 'timezone' => 'UTC',
        'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium',
        'active' => true, 'user_id' => $userId, 'observer' => 'mapper', 'observations' => 0,
        'hidden' => false, 'slug' => Str::slug($name), 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function mapTestObservation(string $username, int $instrumentId, ?int $locationId): void
{
    DB::table('observations')->insert([
        'observer_username' => $username, 'observed_on' => '2024-01-01',
        'instrument_id' => $instrumentId, 'location_id' => $locationId,
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 31', 'target_name' => 'M 31',
        'has_drawing' => false, 'language' => 'en', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->instrument = InstrumentFactory::new()->create(['user_id' => $this->owner->id, 'observer' => $this->owner->username]);
});

it('lists the locations with the number of observations made there', function () {
    $home = mapTestLocation($this->owner->id, 'Home', ['latitude' => 51.0, 'longitude' => 4.5]);
    $dark = mapTestLocation($this->owner->id, 'Dark site', ['latitude' => 44.1, 'longitude' => 6.2]);
    $unused = mapTestLocation($this->owner->id, 'Unused');
    mapTestObservation($this->owner->username, $this->instrument->id, $home);
    mapTestObservation($this->owner->username, $this->instrument->id, $home);
    mapTestObservation($this->owner->username, $this->instrument->id, $dark);
    mapTestObservation($this->owner->username, $this->instrument->id, null);
    // Another instrument at the unused location
    mapTestObservation($this->owner->username, $this->instrument->id + 1, $unused);

    expect($this->instrument->usedLocationsForMap($this->owner))->toBe([
        ['name' => 'Dark site', 'url' => '/location/'.$this->owner->slug.'/dark-site', 'latitude' => 44.1, 'longitude' => 6.2, 'observations' => 1],
        ['name' => 'Home', 'url' => '/location/'.$this->owner->slug.'/home', 'latitude' => 51.0, 'longitude' => 4.5, 'observations' => 2],
    ]);
});

it('leaves out hidden locations unless the viewer owns them or is an administrator', function () {
    $hidden = mapTestLocation($this->owner->id, 'Secret spot', ['hidden' => true]);
    mapTestObservation($this->owner->username, $this->instrument->id, $hidden);
    $names = fn ($viewer) => array_column($this->instrument->usedLocationsForMap($viewer), 'name');

    // The helper always creates user 2, so the administrator comes first
    $administrator = $this->createUserAndAssignToTeam('Administrators');

    expect($names($this->owner))->toBe(['Secret spot'])
        ->and($names($administrator))->toBe(['Secret spot'])
        ->and($names(User::factory()->create()))->toBe([])
        ->and($names(null))->toBe([]);
});

it('leaves out locations without real coordinates', function () {
    $placeholder = mapTestLocation($this->owner->id, 'Old placeholder', ['latitude' => -999, 'longitude' => -999]);
    mapTestObservation($this->owner->username, $this->instrument->id, $placeholder);

    expect($this->instrument->usedLocationsForMap($this->owner))->toBe([]);
});

it('shows the map to the owner of the instrument', function () {
    $home = mapTestLocation($this->owner->id, 'Home <b>sweet</b> home');
    mapTestObservation($this->owner->username, $this->instrument->id, $home);
    $url = '/instrument/'.$this->owner->slug.'/'.$this->instrument->slug;

    $this->actingAs($this->owner)->get($url)
        ->assertOk()
        ->assertSee('id="instrument-locations-map"', false)
        ->assertDontSee('<b>sweet</b>', false);

    $this->actingAs(User::factory()->create())->get($url)
        ->assertOk()
        ->assertDontSee('id="instrument-locations-map"', false);
});

it('shows no map when the instrument was not used anywhere', function () {
    $this->actingAs($this->owner)->get('/instrument/'.$this->owner->slug.'/'.$this->instrument->slug)
        ->assertOk()
        ->assertDontSee('id="instrument-locations-map"', false);
});
