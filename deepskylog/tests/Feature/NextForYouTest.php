<?php

use App\Livewire\NextForYou as NextForYouPage;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\AlsoSeenObjects;
use App\Services\NextForYou;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function nextObject(string $name): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'AND',
        'ra'            => 1.0, 'decl' => 40.0, 'mag' => 11.0, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 60, 'diam2' => 60,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function nextObservation(string $username, string $object, ?int $visibility = null): void
{
    $id = DB::table('observations')->insertGetId(['observer_username' => $username, 'observed_on' => '2025-10-01',
        'target_type'                                                 => 'deepsky', 'target_table' => 'objects', 'target_key' => $object, 'target_name' => $object,
        'has_drawing'                                                 => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
    if ($visibility !== null) {
        DB::table('deepsky_observation_details')->insert(['observation_id' => $id, 'visibility' => $visibility]);
    }
}

/**
 * Three galaxy observers and three nebula observers who all saw the
 * Popular object, and an observer with a standard telescope and location
 * who saw two of the galaxies and Popular (and Galaxy 3, but not seen), with
 * stored contrast reserves of the objects they have not seen.
 */
function nextObserver(): User
{
    foreach (['Galaxy 1', 'Galaxy 2', 'Galaxy 3', 'Faint Galaxy', 'Nebula 1', 'Nebula 2', 'Popular', 'Lonely'] as $name) {
        nextObject($name);
    }
    foreach (['g1', 'g2', 'g3'] as $observer) {
        foreach (['Galaxy 1', 'Galaxy 2', 'Galaxy 3', 'Faint Galaxy', 'Popular'] as $object) {
            nextObservation($observer, $object);
        }
    }
    foreach (['n1', 'n2', 'n3'] as $observer) {
        foreach (['Nebula 1', 'Nebula 2', 'Popular'] as $object) {
            nextObservation($observer, $object);
        }
    }
    nextObservation('g1', 'Lonely');

    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $instrument = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 300, 'focal_length_mm' => 1500,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    $location = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();

    foreach (['Galaxy 1', 'Galaxy 2', 'Popular'] as $object) {
        nextObservation($user->username, $object);
    }
    nextObservation($user->username, 'Galaxy 3', Observation::VISIBILITY_NOT_SEEN);

    foreach (['Galaxy 3' => 1.0, 'Faint Galaxy' => -0.5, 'Nebula 1' => 0.5, 'Nebula 2' => 0.4] as $object => $reserve) {
        DB::table('user_object_metrics')->insert([
            'user_id'                         => $user->id, 'instrument_id' => $instrument->id, 'location_id' => $location->id, 'lens_id' => null,
            'object_name'                     => $object, 'contrast_reserve' => $reserve, 'contrast_reserve_category' => 'easy',
            'optimum_detection_magnification' => 100, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return $user->fresh();
}

it('finds the objects the same observers saw', function () {
    nextObserver();

    $result = AlsoSeenObjects::rebuild();
    $names  = array_column(AlsoSeenObjects::forObject('Galaxy 1'), 'name');

    expect($result['rows'])->toBeGreaterThan(0)
        // Galaxy 3 has three observers: the one who did not see it does not count
        ->and($names)->toContain('Galaxy 2', 'Galaxy 3', 'Faint Galaxy', 'Popular')
        ->and($names)->not->toContain('Nebula 1', 'Lonely', 'Galaxy 1')
        // Popular is seen by everybody, so it comes after the galaxies
        ->and(array_search('Popular', $names))->toBeGreaterThan(array_search('Galaxy 3', $names))
        ->and(AlsoSeenObjects::forObject('Lonely'))->toBe([]);
});

it('suggests the unseen objects the standard telescope shows', function () {
    $user = nextObserver();
    AlsoSeenObjects::rebuild();

    $next   = NextForYou::for($user);
    $result = $next->compute();
    $shown  = $next->objects($result);

    expect($next->unavailable())->toBeNull()
        ->and(array_column($result['objects'], 'name'))->not->toContain('Galaxy 1', 'Galaxy 2', 'Popular')
        ->and(array_column($shown['objects'], 'name'))->toBe(['Galaxy 3', 'Nebula 1', 'Nebula 2'])
        ->and($shown['pending'])->toBeFalse()
        ->and(array_column($shown['objects'][0]['because'], 'name'))->toContain('Galaxy 1', 'Galaxy 2');
});

it('says why nothing can be suggested', function () {
    $user = nextObserver();

    expect(NextForYou::for($user)->unavailable())->toContain('not been calculated');

    AlsoSeenObjects::rebuild();
    $user->forceFill(['stdtelescope' => null])->save();
    expect(NextForYou::for($user->fresh())->unavailable())->toContain('standard instrument');
});

it('calculates again after a new observation', function () {
    $user = nextObserver();
    AlsoSeenObjects::rebuild();
    NextForYou::for($user)->compute();

    nextObservation($user->username, 'Nebula 1');

    expect(NextForYou::for($user)->result())->toBeNull();
});

it('shows the neighbours on the object page', function () {
    $user = nextObserver();
    AlsoSeenObjects::rebuild();

    $this->actingAs($user)
        ->blade('<x-also-seen object-name="Galaxy 1" />')
        ->assertSee('Observers who saw this also saw…')
        ->assertSee(route('object.show', 'galaxy-3'))
        ->assertSee(route('observing-list.next-for-you'))
        // Galaxy 2 is seen by the viewer
        ->assertSee('You have seen this object');

    $this->blade('<x-also-seen object-name="Lonely" />')->assertDontSee('Observers who saw this also saw…');
});

it('shows the page and makes a list of the objects', function () {
    $user = nextObserver();
    AlsoSeenObjects::rebuild();

    $this->actingAs($user)->get(route('observing-list.next-for-you'))
        ->assertOk()
        ->assertSee('Galaxy 3')
        ->assertDontSee('Faint Galaxy');
    $this->actingAs($user)->get(route('observing-lists.index'))
        ->assertOk()
        ->assertSee(route('observing-list.next-for-you'));

    Livewire::actingAs($user)
        ->test(NextForYouPage::class)
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    expect($list->name)->toBe('Next for you')
        ->and($list->items()->pluck('object_name')->all())->toEqualCanonicalizing(['Galaxy 3', 'Nebula 1', 'Nebula 2']);
});
