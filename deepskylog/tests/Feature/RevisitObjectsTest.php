<?php

use App\Livewire\RevisitObjects as RevisitObjectsPage;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\RevisitObjects;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function revisitObject(string $name, float $mag, float $diam, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => 1.0, 'decl' => 40.0, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function revisitInstrument(User $user, string $name, int $aperture, int $focalLength): Instrument
{
    return InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => $name, 'aperture_mm' => $aperture, 'focal_length_mm' => $focalLength,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
}

function revisitObservation(User $user, string $object, Instrument $instrument, bool $drawing = false, ?int $visibility = null): void
{
    $id = DB::table('observations')->insertGetId(['observer_username' => $user->username, 'observed_on' => '2025-10-01', 'instrument_id' => $instrument->id,
        'target_type'                                                 => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'has_drawing'                                                 => $drawing, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $id, 'visibility' => $visibility]);
    }
}

/**
 * An observer at a suburban site (SQM 20) with a 80 mm refractor, a 280 mm
 * and a 400 mm Dobsonian (the standard telescope), and faint galaxies seen
 * with each of them.
 *
 * @return array{0: User, 1: Instrument, 2: Instrument, 3: Instrument}
 */
function revisitObserver(): array
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $small  = revisitInstrument($user, 'Refractor', 80, 480);
    $medium = revisitInstrument($user, 'Medium Dobson', 280, 1400);
    $large  = revisitInstrument($user, 'Dobson', 400, 1800);
    foreach ([32, 10] as $focalLength) {
        EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => $focalLength.' mm', 'focal_length_mm' => $focalLength, 'apparentFOV' => 60, 'active' => true]);
    }
    $location = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $large->id, 'stdlocation' => $location->id])->save();

    foreach (['Small Galaxy', 'Drawn Small Galaxy', 'Medium Galaxy', 'Drawn Medium Galaxy', 'Large Galaxy', 'Not Seen Galaxy', 'Unseen Galaxy'] as $name) {
        revisitObject($name, 11.5, 60);
    }
    // only seen with the refractor: listed, drawn or not
    revisitObservation($user, 'Small Galaxy', $small);
    revisitObservation($user, 'Drawn Small Galaxy', $small, true);
    // seen with a telescope not much smaller: only listed when never drawn
    revisitObservation($user, 'Medium Galaxy', $small);
    revisitObservation($user, 'Medium Galaxy', $medium);
    revisitObservation($user, 'Drawn Medium Galaxy', $medium, true);
    // already seen with the standard telescope
    revisitObservation($user, 'Large Galaxy', $small);
    revisitObservation($user, 'Large Galaxy', $large);
    revisitObservation($user, 'Not Seen Galaxy', $small, false, Observation::VISIBILITY_NOT_SEEN);

    return [$user->fresh(), $small, $medium, $large];
}

it('lists the seen objects the standard telescope shows much better', function () {
    [$user] = revisitObserver();

    $revisit = RevisitObjects::for($user);
    $result  = $revisit->compute();

    expect($revisit->unavailable())->toBeNull()
        ->and(array_column($result['objects'], 'name'))->toEqualCanonicalizing(['Small Galaxy', 'Drawn Small Galaxy', 'Medium Galaxy']);

    $byName = collect($result['objects'])->keyBy('name');
    expect($byName['Small Galaxy'])->toMatchArray(['aperture' => 80.0, 'drawn' => false, 'reason' => RevisitObjects::SMALLER])
        ->and($byName['Medium Galaxy'])->toMatchArray(['aperture' => 280.0, 'drawn' => false, 'reason' => RevisitObjects::UNDRAWN])
        ->and($byName['Small Galaxy']['gain'])->toBeGreaterThan($byName['Medium Galaxy']['gain'])
        ->and($result['objects'][0]['gain'])->toBeGreaterThanOrEqual($result['objects'][2]['gain']);

    expect(array_column($revisit->objects($result, RevisitObjects::SMALLER)['objects'], 'name'))->toEqualCanonicalizing(['Small Galaxy', 'Drawn Small Galaxy'])
        ->and(array_column($revisit->objects($result, RevisitObjects::UNDRAWN)['objects'], 'name'))->toEqualCanonicalizing(['Small Galaxy', 'Medium Galaxy']);
});

it('finds nothing to revisit with the smallest telescope', function () {
    [$user, $small] = revisitObserver();

    expect(RevisitObjects::for($user, $small->id)->compute()['objects'])->toBe([]);
});

it('says why nothing can be compared without a standard telescope', function () {
    [$user] = revisitObserver();
    $user->forceFill(['stdtelescope' => null])->save();

    expect(RevisitObjects::for($user->fresh())->unavailable())->not->toBeNull();
});

it('calculates again after a new observation', function () {
    [$user, , , $large] = revisitObserver();
    RevisitObjects::for($user)->compute();

    revisitObservation($user, 'Small Galaxy', $large);

    expect(RevisitObjects::for($user)->result())->toBeNull();
});

it('shows the page and makes a list of the objects', function () {
    [$user, , , $large] = revisitObserver();

    $this->actingAs($user)->get(route('observing-list.revisit'))
        ->assertOk()
        ->assertSee('Small Galaxy')
        ->assertDontSee('Large Galaxy');

    Livewire::actingAs($user)
        ->test(RevisitObjectsPage::class)
        ->assertSet('instrument', (string) $large->id)
        ->set('reason', RevisitObjects::SMALLER)
        ->assertDontSee('Medium Galaxy')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    expect($list->name)->toBe('Revisit with '.$large->fullName())
        ->and($list->items()->pluck('object_name')->all())->toEqualCanonicalizing(['Small Galaxy', 'Drawn Small Galaxy']);
});

it('links to the page from the own instrument page and the observing lists', function () {
    [$user, , , $large] = revisitObserver();

    $this->actingAs($user)->get('/instrument/'.$user->slug.'/'.$large->slug)
        ->assertOk()
        ->assertSee(route('observing-list.revisit', ['instrument' => $large->id]), false);
    $this->actingAs($user)->get(route('observing-lists.index'))
        ->assertOk()
        ->assertSee(route('observing-list.revisit'));
});
