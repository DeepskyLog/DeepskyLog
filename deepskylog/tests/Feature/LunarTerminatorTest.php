<?php

use App\Models\ObservingList;
use App\Models\User;
use App\Services\Moon\LunarEphemeris;
use App\Services\Moon\LunarGazetteer;
use App\Services\Moon\LunarTerminator;
use App\Services\Moon\MoonDiskRenderer;
use App\Services\News\SkyPositions;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

// The lunar features at the terminator: the physical ephemeris of the Moon
// (LunarEphemeris, Meeus chapter 53), the positions and diameters of the IAU
// gazetteer (LunarGazetteer), the features well placed in a night
// (LunarTerminator), the page /moon, the home page box and the section of a
// lunar feature's page.

/** The evening of 17 November 2026: the Moon just past first quarter, colongitude about 9°. */
const LUNAR_NIGHT = '2026-11-17';

beforeEach(fn () => Carbon::setTestNow('2026-11-17 15:00:00'));
afterEach(fn () => Carbon::setTestNow());

function lunarFeature(string $name, string $type, ?float $lat, ?float $lon, ?float $diameter): int
{
    return DB::table('lunar_features')->insertGetId([
        'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'feature_type' => $type,
        'lat'  => $lat, 'lon' => $lon, 'diameter' => $diameter, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function lunarFeatures(): void
{
    lunarFeature('Rupes Recta', 'other', -21.6748, -7.7019, 115.95);
    lunarFeature('Ptolemaeus', 'crater', -9.1647, -1.8448, 153.67);
    lunarFeature('Copernicus', 'crater', 9.6209, -20.0786, 96.07);
    lunarFeature('Mare Imbrium', 'mare', 34.7230, -14.9140, 1145.53);
    lunarFeature('Tiny', 'crater', -15.0, -5.0, 2.0);
    LunarTerminator::forgetCatalogue();
}

function lunarObserver(): User
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Leuven',
        'latitude'                                        => 50.85, 'longitude' => 4.35, 'timezone' => 'Europe/Brussels', 'active' => true]);
    $user->forceFill(['stdlocation' => $location->id])->save();

    return $user->fresh();
}

it('computes the physical ephemeris of Meeus example 53.a', function () {
    // 1992 April 12, 0h TD
    $tt        = gmmktime(0, 0, 0, 4, 12, 1992);
    $ephemeris = LunarEphemeris::at($tt - (int) round(SkyPositions::deltaT($tt)));

    expect($ephemeris['libration_lon'])->toEqualWithDelta(-1.23, 0.02)
        ->and($ephemeris['libration_lat'])->toEqualWithDelta(4.20, 0.02)
        ->and($ephemeris['axis'])->toEqualWithDelta(15.08, 0.02)
        ->and($ephemeris['sun_lon'])->toEqualWithDelta(67.90, 0.02)
        ->and($ephemeris['sun_lat'])->toEqualWithDelta(1.46, 0.02)
        ->and($ephemeris['colongitude'])->toEqualWithDelta(22.10, 0.02)
        ->and($ephemeris['fraction'])->toEqualWithDelta(0.6786, 0.001)
        ->and($ephemeris['waxing'])->toBeTrue();
});

it('names the phases from the colongitude', function () {
    $phase = fn (float $colongitude) => LunarTerminator::phase(['colongitude' => $colongitude]);

    expect($phase(270.0))->toBe('new')
        ->and($phase(300.0))->toBe('waxing_crescent')
        ->and($phase(3.0))->toBe('first_quarter')
        ->and($phase(357.0))->toBe('first_quarter')
        ->and($phase(40.0))->toBe('waxing_gibbous')
        ->and($phase(90.0))->toBe('full')
        ->and($phase(130.0))->toBe('waning_gibbous')
        ->and($phase(180.0))->toBe('last_quarter')
        ->and($phase(220.0))->toBe('waning_crescent');
});

it('puts the Sun low over the features at the morning terminator', function () {
    lunarFeatures();
    $night = LunarTerminator::night(50.85, 4.35, LUNAR_NIGHT, 'Europe/Brussels');

    expect($night['best'])->not->toBeNull()
        ->and($night['phase'])->toBe('waxing_gibbous')
        ->and($night['ephemeris']['colongitude'])->toBeGreaterThan(5.0)->toBeLessThan(13.0);

    $names = array_column(LunarTerminator::features($night['ephemeris']), 'name');
    // Copernicus is still in the night, Imbrium too large, Tiny too small
    expect($names)->toBe(['Ptolemaeus', 'Rupes Recta']);

    $rupes = LunarTerminator::placement($night['ephemeris'], ['lat' => -21.6748, 'lon' => -7.7019, 'diameter' => 115.95]);
    expect($rupes['rising'])->toBeTrue()
        ->and($rupes['sun_altitude'])->toBeGreaterThan(0.0)->toBeLessThan(LunarTerminator::maxSunAltitude(115.95));
    expect(LunarTerminator::sunriseColongitude(-7.7019))->toEqualWithDelta(7.7, 0.01);
});

it('finds the coming nights a feature is at the terminator', function () {
    lunarFeatures();
    $user   = lunarObserver();
    $nights = LunarTerminator::nightsFor(['lat' => 9.6209, 'lon' => -20.0786, 'diameter' => 96.07], $user->standardLocation, LUNAR_NIGHT, 'Europe/Brussels');

    expect($nights)->not->toBeEmpty()
        // the morning terminator reaches Copernicus a day or two later
        ->and($nights[0]['night'])->toBeIn(['2026-11-18', '2026-11-19'])
        ->and($nights[0]['rising'])->toBeTrue();
});

it('draws the disk with the features numbered', function () {
    lunarFeatures();
    $night = LunarTerminator::night(50.85, 4.35, LUNAR_NIGHT, 'Europe/Brussels');
    $svg   = (new MoonDiskRenderer($night['ephemeris'], 'south'))->svg(LunarTerminator::features($night['ephemeris']));

    expect($svg)->toStartWith('<svg')
        ->toContain('<title>Ptolemaeus</title>')
        ->toContain('<title>Rupes Recta</title>')
        ->toContain('stroke="#f59e0b"');
});

it('fills the features from the gazetteer, renames the cut names and adds the missing ones', function () {
    lunarFeature('Bel', 'crater', null, null, null);
    lunarFeature('Plato', 'crater', null, null, null);

    $plan = LunarGazetteer::plan();
    expect($plan['renamed'])->toHaveCount(1)
        ->and($plan['renamed'][0]['to'])->toBe("Bel'kovich")
        ->and(collect($plan['updated'])->pluck('name')->all())->toBe(["Bel'kovich", 'Plato'])
        ->and(collect($plan['added'])->pluck('name'))->toContain('Copernicus', 'Rupes Recta')
        ->not->toContain('Plato');

    $this->artisan('lunar-features:fill-from-gazetteer')->assertSuccessful();
    expect(DB::table('lunar_features')->where('name', 'Plato')->value('lat'))->toBeNull();

    $this->artisan('lunar-features:fill-from-gazetteer', ['--apply' => true])->assertSuccessful();
    $plato = DB::table('lunar_features')->where('name', 'Plato')->first();
    expect((float) $plato->lat)->toEqualWithDelta(51.6, 0.1)
        ->and((float) $plato->diameter)->toBeGreaterThan(90.0)
        ->and(DB::table('lunar_features')->where('name', "Bel'kovich")->value('slug'))->toBe('bel')
        ->and(DB::table('search_index')->where('source_table', 'lunar_features')->where('name', 'Rupes Recta')->exists())->toBeTrue();

    // a second run changes nothing
    $again = LunarGazetteer::plan();
    expect($again['renamed'])->toBe([])->and($again['updated'])->toBe([])->and($again['added'])->toBe([]);
});

it('shows the Moon page with the features at the terminator', function () {
    lunarFeatures();
    $this->actingAs(lunarObserver());

    $this->get('/moon?night='.LUNAR_NIGHT.'&view=mirror')
        ->assertOk()
        ->assertSee('Colongitude')
        ->assertSee('Rupes Recta')
        ->assertSee('Ptolemaeus')
        ->assertDontSee('Mare Imbrium')
        ->assertSee('The coming nights');
});

it('asks for a location without one', function () {
    $this->get('/moon')->assertOk()->assertSee('Add a location with coordinates');
});

it('makes an observing list of the features at the terminator', function () {
    lunarFeatures();
    $user = lunarObserver();
    $this->actingAs($user);

    $this->post('/moon/observing-list', ['night' => LUNAR_NIGHT, 'time' => '20:00', 'location' => $user->stdlocation])
        ->assertRedirect();

    $list = ObservingList::where('owner_user_id', $user->id)->latest('id')->first();
    expect($list->items()->orderBy('sort_order')->pluck('object_name')->all())->toBe(['Ptolemaeus', 'Rupes Recta'])
        ->and((string) $list->planned_night)->toStartWith(LUNAR_NIGHT)
        ->and((int) $list->planned_location_id)->toBe((int) $user->stdlocation)
        ->and(Carbon::parse($list->items()->first()->planned_at, 'UTC')->setTimezone('Europe/Brussels')->format('H:i'))->toBe('20:00');
});

it('shows the Moon tonight on the home page', function () {
    lunarFeatures();
    $this->actingAs(lunarObserver());

    $this->get('/')->assertOk()->assertSee('The Moon tonight')->assertSee('Rupes Recta');
});

it('shows when a lunar feature is at the terminator on its page', function () {
    lunarFeatures();
    $this->actingAs(lunarObserver());
    DB::table('search_index')->insert([
        'name'         => 'Rupes Recta', 'name_normalized' => 'rupes recta', 'source_table' => 'lunar_features',
        'source_pk'    => (string) DB::table('lunar_features')->where('name', 'Rupes Recta')->value('id'),
        'display_name' => 'Rupes Recta', 'source_type' => 'other', 'name_unique' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->get('/object/rupes-recta')
        ->assertOk()
        ->assertSee('At the terminator')
        ->assertSee('at colongitude 8° and 188°')
        ->assertSee('Well placed!');
});
