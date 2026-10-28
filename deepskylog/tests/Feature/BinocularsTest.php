<?php

use App\Livewire\MessierMarathonPlanner;
use App\Models\DeepskyObject;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\Location;
use App\Models\User;
use App\Services\ContrastReserveCalculator;
use App\Services\FieldViews\FieldViewBuilder;
use App\Services\FilterAdvice;
use App\Services\MessierMarathon;
use App\Services\TelescopicLimitingMagnitude;
use Database\Factories\FilterFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Binoculars are used with both eyes, which see fainter than one eye at a
// telescope of the same aperture: in the contrast reserve, the limiting
// magnitude, the field views, the filter advice and the Messier marathon.

function binocularsObject(string $name, float $mag, float $diam, string $type = 'GALXY', float $ra = 12.514, float $decl = 12.39): DeepskyObject
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'VIR',
        'ra'            => $ra, 'decl' => $decl, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);

    return DeepskyObject::where('name', $name)->firstOrFail();
}

/**
 * An instrument of the observer of the type (Binoculars, Refractor, Finderscope).
 */
function binocularsInstrument(User $user, string $type, array $attributes = []): Instrument
{
    return InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => $type, 'aperture_mm' => 50, 'focal_length_mm' => 1,
        'fixedMagnification' => 10, 'field_of_view_deg' => null, 'obstruction_perc' => 0, 'flip_image' => false, 'flop_image' => false,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => $type])->id, 'finder_id' => null, 'lens_id' => null,
        'active'             => true,
        ...$attributes,
    ])->fresh();
}

function binocularsLocation(User $user): Location
{
    return LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Dark site', 'latitude' => 50.85, 'longitude' => 4.35,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 21.0, 'active' => true,
    ]);
}

it('reaches fainter stars with both eyes', function () {
    $user       = User::factory()->create();
    $binoculars = binocularsInstrument($user, 'Binoculars');
    $refractor  = binocularsInstrument($user, 'Refractor');

    expect($binoculars->isBinocular())->toBeTrue()
        ->and($refractor->isBinocular())->toBeFalse()
        ->and(TelescopicLimitingMagnitude::forInstrument(6.0, $binoculars, 10) - TelescopicLimitingMagnitude::forInstrument(6.0, $refractor, 10))
        ->toEqualWithDelta(TelescopicLimitingMagnitude::BINOCULAR_GAIN, 0.11)
        // the naked-eye limit is already one of both eyes
        ->and(TelescopicLimitingMagnitude::forInstrument(6.0, binocularsInstrument($user, 'Naked Eye', ['aperture_mm' => 7, 'fixedMagnification' => 1]), 1))->toBe(6.0);
});

it('estimates the field of binoculars wider than that of a finder', function () {
    $user = User::factory()->create();

    expect(binocularsInstrument($user, 'Binoculars')->fixedFieldOfViewDeg())->toEqualWithDelta(6.0, 1e-9)
        ->and(binocularsInstrument($user, 'Finderscope')->fixedFieldOfViewDeg())->toEqualWithDelta(5.0, 1e-9)
        ->and(binocularsInstrument($user, 'Binoculars', ['field_of_view_deg' => 6.5])->fixedFieldOfViewDeg())->toBe(6.5);
});

it('gives binoculars a higher contrast reserve than one eye at the same aperture and magnification', function () {
    $user       = User::factory()->create();
    $location   = binocularsLocation($user);
    $binoculars = binocularsInstrument($user, 'Binoculars');
    $refractor  = binocularsInstrument($user, 'Refractor');
    $target     = ContrastReserveCalculator::target(300, 300, 8.0);

    $withBoth = ContrastReserveCalculator::calculate($target, $binoculars, $location, $user);
    $withOne  = ContrastReserveCalculator::calculate($target, $refractor, $location, $user);
    $star     = fn (Instrument $instrument) => ContrastReserveCalculator::pointSource(9.0, $instrument, $location, $user)['contrast_reserve'];

    expect($withBoth['magnification'])->toBe(10)
        ->and($withBoth['contrast_reserve'] - $withOne['contrast_reserve'])->toEqualWithDelta(ContrastReserveCalculator::BINOCULAR_GAIN, 1e-9)
        ->and($star($binoculars))->toBeGreaterThan($star($refractor));
});

it('gives binoculars without a magnification that of a 5 mm exit pupil', function () {
    $user = User::factory()->create();

    expect(ContrastReserveCalculator::magnifications(binocularsInstrument($user, 'Binoculars', ['aperture_mm' => 70, 'fixedMagnification' => null]), $user))
        ->toBe([14 => []]);
});

it('keeps the gain of both eyes in the filter advice', function () {
    $user       = User::factory()->create();
    $binoculars = binocularsInstrument($user, 'Binoculars');
    $refractor  = binocularsInstrument($user, 'Refractor');
    $filter     = FilterFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'UHC', 'type_id' => 3, 'active' => true]);
    $target     = ContrastReserveCalculator::target(3600, 3600, 4.0);
    $advise     = fn (Instrument $instrument) => FilterAdvice::advise($target, 'EMINB', 'NGC 7000', [$filter], $instrument, 19.5, [10 => []], [], 10.0)[0];

    $withBoth = $advise($binoculars);
    $withOne  = $advise($refractor);

    expect($withBoth['contrast_reserve'] - $withOne['contrast_reserve'])->toEqualWithDelta(ContrastReserveCalculator::BINOCULAR_GAIN, 1e-9)
        ->and($withBoth['gain'])->toEqualWithDelta($withOne['gain'], 1e-9)
        ->and($withBoth['current']['contrast_reserve'] - $withOne['current']['contrast_reserve'])->toEqualWithDelta(ContrastReserveCalculator::BINOCULAR_GAIN, 1e-9)
        ->and($withBoth['ideal_magnification'])->toBeNull();
});

it('shows the view through binoculars upright, without a finder', function () {
    $user       = User::factory()->create();
    $location   = binocularsLocation($user);
    $binoculars = binocularsInstrument($user, 'Binoculars', ['flip_image' => true, 'flop_image' => true]);
    $user->forceFill(['stdtelescope' => $binoculars->id, 'stdlocation' => $location->id])->save();
    $object = binocularsObject('M 33', 5.7, 3600, 'GALXY', 1.564, 30.66);

    $result = app(FieldViewBuilder::class)->build($user->fresh(), $object, ['set' => 'all']);
    $view   = $result['views']['telescope'];

    expect($result['views'])->not->toHaveKey('finder')
        ->and($view['title'])->toBe(__('Binoculars'))
        ->and($view['fov'])->toBe('6° ('.__('estimated').')')
        ->and($view['limit'])->toBeGreaterThan(TelescopicLimitingMagnitude::calculate($result['nelm'], 50, 10) - 1)
        ->and($view['contrast']['value'])->not->toBeNull()
        ->and($view['svg'])->not->toContain('scale(-1')
        ->and($result['views']['naked_eye']['svg'])->toContain(__('Binoculars'));
});

it('plans the Messier marathon with the objects binoculars can see', function () {
    $user       = User::factory()->create();
    $location   = binocularsLocation($user);
    $binoculars = binocularsInstrument($user, 'Binoculars');
    // Three galaxies at the place of M 87: bright, faint and too faint for 10x50.
    binocularsObject('M 1', 5.0, 300);
    binocularsObject('M 2', 9.0, 300);
    binocularsObject('M 3', 12.5, 300);
    DB::table('objectnames')->insert(collect([1, 2, 3])->map(fn (int $n) => [
        'objectname' => 'M '.$n, 'catalog' => 'M', 'catindex' => (string) $n, 'altname' => 'M '.$n, 'slug' => 'm-'.$n, 'timestamp' => now(),
    ])->all());

    $plain    = MessierMarathon::forLocation($location, 'Europe/Brussels');
    $marathon = MessierMarathon::forLocation($location, 'Europe/Brussels', instrument: $binoculars, user: $user);
    $plan     = $marathon->plan('2027-04-06');
    $objects  = collect($plan['objects'])->keyBy('name');

    expect(array_column($plain->plan('2027-04-06')['objects'], 'name'))->toEqualCanonicalizing(['M 1', 'M 2', 'M 3'])
        ->and($objects['M 1'])->not->toHaveKey('min_altitude')
        ->and($objects['M 2']['min_altitude'])->toBeGreaterThan(MessierMarathon::MIN_ALTITUDE)
        ->and($objects['M 2']['altitude'])->toBeGreaterThanOrEqual($objects['M 2']['min_altitude'] - 0.1)
        ->and(array_column($plan['missed'], 'name'))->toBe(['M 3'])
        ->and(array_column($plan['missed'], 'reason'))->toBe(['faint']);

    $list = $marathon->createList($user, $plan, $location);
    expect($list->items()->where('object_name', 'M 3')->value('item_description'))
        ->toBe(__('Too faint for :instrument at the altitudes it reaches in this night.', ['instrument' => html_entity_decode($binoculars->fullName())]));
});

it('chooses the standard telescope for the marathon, or none', function () {
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location   = binocularsLocation($user);
    $binoculars = binocularsInstrument($user, 'Binoculars');
    $user->forceFill(['stdtelescope' => $binoculars->id, 'stdlocation' => $location->id])->save();

    Livewire::actingAs($user->fresh())->test(MessierMarathonPlanner::class)
        ->assertSet('instrumentId', $binoculars->id)
        ->assertSee(__('None: only the altitude counts'))
        ->set('instrumentId', null)
        ->assertHasNoErrors()
        ->assertDontSee('(≥');
});
