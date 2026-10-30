<?php

use App\Models\ObservingList;
use App\Models\User;
use App\Services\News\SkyPositions;
use App\Services\Planets\PlanetDisk;
use App\Services\Planets\PlanetMoons;
use App\Services\Planets\PlanetSketchTemplate;
use Carbon\Carbon;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

// The drawing templates of the planets on the sketch pad at the eyepiece
// (PlanetSketchTemplate, AtTheEyepiece::planetSketch()): the disk with its
// flattening and phase, Mars's caps, Saturn's rings and the central
// meridians, turned as the telescope view.

function pstPlanet(string $name): void
{
    DB::table('planets')->insert(['name' => $name, 'slug' => strtolower($name), 'designation' => $name, 'body_type' => 'planet', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('search_index')->insert([
        'name' => $name, 'name_normalized' => strtolower($name), 'source_table' => 'planets', 'display_name' => $name,
        'source_pk' => (string) DB::table('planets')->where('name', $name)->value('id'), 'source_type' => 'planet',
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function pstObserver(): User
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Leuven',
        'latitude' => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'active' => true]);
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => '', 'name' => 'Dobson', 'aperture_mm' => 300]);
    $user->forceFill(['stdlocation' => $location->id, 'stdtelescope' => $instrument->id])->save();

    return $user->fresh();
}

/** The distance of each point from the centre. */
function pstRadii(array $points): array
{
    return array_map(fn (array $point) => hypot($point[0], $point[1]), $points);
}

beforeEach(fn () => Carbon::setTestNow('2026-10-30 22:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('gives Jupiter\'s System I central meridian of Meeus example 43.a', function () {
    // 1992 December 16, 0h UT: ω1 268.06°, ω2 72.74° (with the correction for the phase, a few tenths)
    $jupiter = PlanetMoons::jupiter(gmmktime(0, 0, 0, 12, 16, 1992));

    expect($jupiter['cm1'])->toEqualWithDelta(268.06, 0.6)
        ->and($jupiter['cm2'])->toEqualWithDelta(72.74, 0.6);
});

it('gives the pole of Saturn the tilt of its rings', function () {
    $at = now()->getTimestamp();

    expect(PlanetDisk::at('saturn', $at)['de'])->toEqualWithDelta(PlanetMoons::saturn($at)['ring_tilt'], 0.1)
        ->and(PlanetDisk::at('jupiter', $at)['de'])->toEqualWithDelta(0.0, 4.0);
});

it('draws Jupiter flattened, with both central meridians and their rates', function () {
    $template = PlanetSketchTemplate::for('jupiter', now()->getTimestamp());
    $radii = pstRadii($template['lines'][0]['points']);

    expect(max($radii))->toEqualWithDelta(1.0, 0.002)
        ->and(min($radii))->toEqualWithDelta(PlanetMoons::FLATTENING['jupiter'], 0.005)
        ->and(array_column($template['meridians'], 'label'))->toBe(['CM I', 'CM II'])
        ->and($template['meridians'][0]['rate'])->toEqualWithDelta(36.58, 0.05)
        ->and($template['meridians'][1]['rate'])->toEqualWithDelta(36.26, 0.05)
        // The disk, then the night side
        ->and(array_column($template['fills'], 'level'))->toBe([0.7, 0.0])
        ->and($template['details'])->toBe(['Diameter 35.7″']);
});

it('draws Saturn\'s rings behind and in front of the globe', function () {
    $template = PlanetSketchTemplate::for('saturn', now()->getTimestamp());
    $tilt = PlanetDisk::at('saturn', now()->getTimestamp())['de'];

    // Far half of the B and A rings, the globe, its night side, the near half
    expect(array_column($template['fills'], 'level'))->toBe([0.75, 0.55, 0.7, 0.0, 0.75, 0.55])
        ->and($template['extent'])->toBe(PlanetMoons::RING_RADIUS)
        ->and($template['meridians'])->toBe([])
        ->and($template['details'][0])->toBe('Diameter 19.4″, ring tilt '.round($tilt, 1).'°');

    // No line of the rings within the globe on the far side
    $p = deg2rad(PlanetDisk::at('saturn', now()->getTimestamp())['p']);
    $farNorth = $tilt >= 0 ? 1 : -1;
    foreach ($template['lines'] as $line) {
        foreach ($line['points'] as [$west, $north]) {
            // back to the frame of the planet
            $x = $west * cos($p) + $north * sin($p);
            $y = -$west * sin($p) + $north * cos($p);
            if (hypot($x, $y) > 1.4) {
                expect($y * $farNorth > 0 && $x * $x + ($y / 0.9) ** 2 < 0.98)->toBeFalse();
            }
        }
    }
});

it('draws the phase of Venus and the caps of Mars', function () {
    // Venus near its inferior conjunction of October 2026: a thin crescent
    $venus = PlanetSketchTemplate::for('venus', now()->getTimestamp());
    expect($venus['details'][0])->toStartWith('Diameter ')
        ->and($venus['details'][0])->toEndWith('illuminated 2%')
        ->and(count($venus['lines']))->toBe(2);

    $mars = PlanetSketchTemplate::for('mars', now()->getTimestamp());
    expect($mars['meridians'][0]['label'])->toBe('CM')
        ->and($mars['meridians'][0]['rate'])->toEqualWithDelta(14.6, 0.1)
        ->and($mars['meridians'][0]['value'])->toEqualWithDelta(PlanetDisk::at('mars', now()->getTimestamp())['cm'], 0.01)
        // The disk, the cap(s) and the night side
        ->and($mars['fills'][0]['level'])->toBe(0.55)
        ->and($mars['fills'][1]['level'])->toBe(PlanetSketchTemplate::CAP_LEVEL)
        ->and(end($mars['fills'])['level'])->toBe(0.0)
        ->and($mars['details'][0])->toContain(', Ls ');
});

it('recognises the planets with a template by their name', function () {
    pstPlanet('Jupiter');
    pstPlanet('Uranus');

    expect(PlanetSketchTemplate::planetOf('Jupiter'))->toBe('jupiter')
        ->and(PlanetSketchTemplate::planetOf('jupiter'))->toBe('jupiter')
        ->and(PlanetSketchTemplate::planetOf('Uranus'))->toBeNull()
        ->and(PlanetSketchTemplate::planetOf('M 31'))->toBeNull();
});

it('offers the planet as a template on the sketch pad at the eyepiece, turned as the telescope view', function () {
    $owner = pstObserver();
    pstPlanet('Jupiter');
    $list = ObservingList::createWithObjects($owner, 'Planets', ['Jupiter'], null, false);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))->assertOk()
        ->assertSee('id="sketch-fill"', false)
        ->assertSee('id="sketch-outline-on"', false)
        ->assertSee('data-tool="dark"', false)
        ->assertViewHas('planetTemplate', fn (?array $template) => $template['planet'] === 'jupiter')
        ->assertViewHas('sketchOrientation', 'zenith')
        ->assertViewHas('sketchLabels', fn (array $labels) => in_array('Diameter 35.7″', $labels, true))
        ->assertViewHas('compass', fn ($compass) => is_array($compass)
            && abs(hypot(...$compass['north']) - 1) < 1e-3
            // Jupiter is not on the meridian at 23:00 in Leuven: the zenith is not north
            && abs($compass['north'][1] + 1) > 1e-3);
});

it('keeps north up for a planet with an equatorial mount, and offers no template for a deep-sky object', function () {
    $owner = pstObserver();
    $mount = DB::table('mount_types')->where('name', 'Equatorial')->value('id')
        ?? DB::table('mount_types')->insertGetId(['name' => 'Equatorial']);
    DB::table('instruments')->where('id', $owner->stdtelescope)->update(['mount_type_id' => $mount, 'flip_image' => 0, 'flop_image' => 0]);
    pstPlanet('Saturn');
    $list = ObservingList::createWithObjects($owner, 'Planets', ['Saturn'], null, false);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $list))->assertOk()
        ->assertViewHas('sketchOrientation', 'north')
        ->assertViewHas('compass', ['north' => [0.0, -1.0], 'west' => [1.0, 0.0]]);

    DB::table('objects')->insert([
        'name' => 'M 31', 'slug' => 'm-31', 'type' => 'GALXY', 'con' => 'AND', 'ra' => 0.712, 'decl' => 41.27, 'mag' => 3.4, 'subr' => 13.0,
        'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0, 'diam2' => 200.0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '', 'torresB' => '', 'torresBC' => '',
        'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0',
        'DSLOP' => '0', 'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    $deepsky = ObservingList::createWithObjects($owner, 'Galaxies', ['M 31'], null, false);

    $this->actingAs($owner)->get(route('observing-list.eyepiece', $deepsky))->assertOk()
        ->assertDontSee('id="sketch-fill"', false)
        ->assertViewHas('planetTemplate', null);
});
