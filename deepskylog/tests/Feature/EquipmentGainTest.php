<?php

use App\Jobs\ComputeEquipmentGain;
use App\Livewire\WhatsNewEquipment;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\EquipmentGain;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

function equipmentGainObject(string $name, float $mag, float $diam, float $decl = 40.0, string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => 1.0, 'decl' => $decl, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function equipmentGainInstrument(User $user, string $name, int $aperture, int $focalLength, array $attributes = []): Instrument
{
    return InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => $name, 'aperture_mm' => $aperture, 'focal_length_mm' => $focalLength,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
        ...$attributes,
    ]);
}

/**
 * An observer at a suburban site (SQM 20) with a 80 mm refractor, a 400 mm
 * Dobsonian and eyepieces of 32 and 10 mm, and objects visible with both,
 * only with the Dobsonian, only with it at 360x, with neither, and below
 * the horizon.
 *
 * @return array{0: User, 1: Instrument, 2: Instrument}
 */
function equipmentGainObserver(): array
{
    // in the Observer role, which makes observing lists
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $small = equipmentGainInstrument($user, 'Refractor', 80, 480);
    $large = equipmentGainInstrument($user, 'Dobson', 400, 1800);
    foreach ([32, 10] as $focalLength) {
        EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => $focalLength.' mm', 'focal_length_mm' => $focalLength, 'apparentFOV' => 60, 'active' => true]);
    }
    $location = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 20.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $small->id, 'stdlocation' => $location->id])->save();

    equipmentGainObject('Bright Galaxy', 8.0, 300);
    equipmentGainObject('Faint Galaxy', 12.5, 60);
    equipmentGainObject('Seen Galaxy', 12.5, 60);
    equipmentGainObject('Hopeless Galaxy', 18.0, 30);
    // only visible at the highest magnification
    equipmentGainObject('Tiny Nebula', 14.8, 10, 40.0, 'PLNNB');
    equipmentGainObject('Southern Galaxy', 12.5, 60, -70.0);
    equipmentGainObject('Some Double', 6.0, 10, 40.0, 'DS');
    DB::table('object_observation_stats')->insert([['object_name' => 'Faint Galaxy', 'observations' => 5], ['object_name' => 'Seen Galaxy', 'observations' => 9]]);
    DB::table('observations')->insert(['observer_username' => $user->username, 'observed_on' => '2025-10-01', 'instrument_id' => $small->id,
        'target_type'                                      => 'deepsky', 'target_table' => 'objects', 'target_key' => 'Seen Galaxy', 'target_name' => 'Seen Galaxy',
        'has_drawing'                                      => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);

    return [$user->fresh(), $small, $large];
}

it('lists the objects a larger telescope shows that the other instruments do not', function () {
    [$user, , $large] = equipmentGainObserver();

    $gain   = EquipmentGain::forInstrument($user, $large, EquipmentGain::location($user, null));
    $result = $gain->compute();

    expect($gain->unavailable())->toBeNull()
        ->and(array_column($result['objects'], 'name'))->toBe(['Seen Galaxy', 'Faint Galaxy'])
        ->and($result['compared'])->toHaveCount(1)
        ->and($result['objects'][1]['before'])->toBeLessThanOrEqual(EquipmentGain::VISIBLE_ABOVE)
        ->and($result['objects'][1]['contrast_reserve'])->toBeGreaterThan(EquipmentGain::VISIBLE_ABOVE);

    // what the observer has seen is left out when shown
    $shown = $gain->objects($result);
    expect($shown['total'])->toBe(1)
        ->and($shown['objects'][0])->toMatchArray(['name' => 'Faint Galaxy', 'slug' => 'faint-galaxy', 'observations' => 5]);
});

it('compares only with the chosen instruments', function () {
    [$user, $small, $large] = equipmentGainObserver();
    $location               = EquipmentGain::location($user, null);

    $result = EquipmentGain::forInstrument($user, $large, $location, [])->compute();
    expect($result['compared'])->toBe([])
        ->and(array_column($result['objects'], 'name'))->toContain('Bright Galaxy', 'Faint Galaxy');

    $result = EquipmentGain::forInstrument($user, $large, $location, [$small->id])->compute();
    expect(array_column($result['objects'], 'name'))->toBe(['Seen Galaxy', 'Faint Galaxy']);

    // the page starts with all other instruments, and can leave them out
    Livewire::actingAs($user)
        ->test(WhatsNewEquipment::class, ['kind' => 'instrument', 'equipmentId' => $large->id])
        ->assertSet('compare', [(string) $small->id])
        ->assertDontSee('Bright Galaxy')
        ->call('compareNone')
        ->assertSee('Bright Galaxy')
        ->call('compareAll')
        ->assertDontSee('Bright Galaxy');
});

it('stops queueing after calculations that end without a result', function () {
    Queue::fake();
    [$user, $small, $large] = equipmentGainObserver();
    // the page compares with all other instruments, ticked
    $gain = EquipmentGain::forInstrument($user, $large, EquipmentGain::location($user, null), [$small->id]);

    // each job ends (its pending mark gone) without caching the result
    for ($attempt = 1; $attempt <= EquipmentGain::MAX_ATTEMPTS; $attempt++) {
        expect($gain->failed())->toBeFalse()
            ->and($gain->queue())->toBeTrue();
        $gain->forgetPending();
    }
    expect($gain->failed())->toBeTrue()
        ->and($gain->queue())->toBeFalse();
    Queue::assertPushed(ComputeEquipmentGain::class, EquipmentGain::MAX_ATTEMPTS);

    $page = Livewire::actingAs($user)
        ->test(WhatsNewEquipment::class, ['kind' => 'instrument', 'equipmentId' => $large->id])
        ->assertSee('The objects could not be calculated.')
        ->assertDontSee('wire:poll', false);
    Queue::assertPushed(ComputeEquipmentGain::class, EquipmentGain::MAX_ATTEMPTS);

    $page->call('retry')
        ->assertSee('The objects are being calculated');
    Queue::assertPushed(ComputeEquipmentGain::class, EquipmentGain::MAX_ATTEMPTS + 1);
});

it('finds nothing new for a smaller telescope', function () {
    [$user, $small] = equipmentGainObserver();

    $result = EquipmentGain::forInstrument($user, $small, EquipmentGain::location($user, null))->compute();

    expect($result['total'])->toBe(0);
});

it('compares the standard telescope with and without a new eyepiece', function () {
    [$user, , $large] = equipmentGainObserver();
    $user->forceFill(['stdtelescope' => $large->id])->save();
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => '5 mm', 'focal_length_mm' => 5, 'apparentFOV' => 60, 'active' => true]);

    $gain   = EquipmentGain::forEyepiece($user->fresh(), $eyepiece, EquipmentGain::location($user, null));
    $result = $gain->compute();

    expect($gain->unavailable())->toBeNull()
        ->and($result['compared'][0])->toContain('without the 5 mm')
        ->and(array_column($result['objects'], 'name'))->toBe(['Tiny Nebula'])
        ->and($result['objects'][0]['magnification'])->toBe(360);
});

it('says why nothing can be compared', function () {
    [$user, , $large] = equipmentGainObserver();
    $location         = EquipmentGain::location($user, null);

    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'focal_length_mm' => 5, 'active' => true]);
    $user->forceFill(['stdtelescope' => null])->save();
    expect(EquipmentGain::forEyepiece($user->fresh(), $eyepiece, $location)->unavailable())->toBe('Set your standard instrument to see what this eyepiece adds to it.');

    $location->forceFill(['skyBackground' => -999, 'limitingMagnitude' => -999])->save();
    expect(EquipmentGain::forInstrument($user, $large, $location->fresh())->unavailable())->toContain('sky brightness');
});

it('shows the page to the owner and makes a list of the objects', function () {
    [$user, , $large] = equipmentGainObserver();

    $this->actingAs($user)->get(route('instrument.whats-new', [$user->slug, $large->slug]))
        ->assertOk()
        ->assertSee('Faint Galaxy')
        ->assertDontSee('Bright Galaxy');

    Livewire::actingAs($user)
        ->test(WhatsNewEquipment::class, ['kind' => 'instrument', 'equipmentId' => $large->id])
        ->assertSee('Faint Galaxy')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $user->id)->firstOrFail();
    expect($list->name)->toBe('New with '.$large->fullName())
        ->and($list->items()->pluck('object_name')->all())->toBe(['Faint Galaxy']);
});

it('does not show the page for equipment of another observer', function () {
    [$owner, , $large] = equipmentGainObserver();
    $other             = User::factory()->create();

    $this->actingAs($other)->get(route('instrument.whats-new', [$owner->slug, $large->slug]))->assertNotFound();
    $this->actingAs($other)->get(route('instrument.whats-new', [$other->slug, $large->slug]))->assertNotFound();
});

it('shows the page of an eyepiece by its slug', function () {
    [$user, , $large] = equipmentGainObserver();
    $user->forceFill(['stdtelescope' => $large->id])->save();
    $eyepiece = $user->fresh()->eyepieces()->where('focal_length_mm', 10)->firstOrFail();

    $this->actingAs($user)->get(route('eyepiece.whats-new', [$user->slug, $eyepiece->slug]))
        ->assertOk()
        ->assertSee('without the 10 mm');
});

it('links to the page from the own instrument page', function () {
    [$user, , $large] = equipmentGainObserver();

    $this->actingAs($user)->get('/instrument/'.$user->slug.'/'.$large->slug)
        ->assertOk()
        ->assertSee(route('instrument.whats-new', [$user->slug, $large->slug]))
        ->assertSee("What's new for me")
        ->assertDontSee('&amp;#039;', false);
});
