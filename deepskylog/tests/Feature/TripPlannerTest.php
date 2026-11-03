<?php

use App\Livewire\TripPlanner as TripPlannerPage;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\MountType;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\TripPlanner;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

function tripObject(string $name, float $raHours, float $dec, float $mag, float $diam): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => 'GALXY', 'con' => 'SCO',
        'ra'            => $raHours, 'decl' => $dec, 'mag' => $mag, 'subr' => 0.0, 'pa' => 999,
        'urano'         => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $diam,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL'         => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

/**
 * An observer in Belgium under a suburban sky (SQM 19) with a 200 mm
 * Dobsonian, going to Namibia (SQM 21.8) for three nights in June.
 *
 * @return array{0: User, 1: Instrument}
 */
function tripObserver(): array
{
    $team = test()->getTeamFromString('Observers');
    $user = User::factory()->create();
    $user->teams()->attach($team);
    $user->switchTeam($team);
    $dobson = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => 'Dobson', 'aperture_mm' => 200, 'focal_length_mm' => 1200,
        'fixedMagnification' => 0, 'active' => true, 'lens_id' => null,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => 'Reflector'])->id,
        'mount_type_id'      => MountType::firstOrCreate(['name' => 'Alt/Azimuth'])->id,
    ]);
    foreach ([32, 10] as $focalLength) {
        EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => $focalLength.' mm', 'focal_length_mm' => $focalLength, 'apparentFOV' => 60, 'active' => true]);
    }
    $home = LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Backyard', 'latitude' => 50.8, 'longitude' => 4.7,
        'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => 19.0, 'active' => true,
    ]);
    $user->forceFill(['stdtelescope' => $dobson->id, 'stdlocation' => $home->id])->save();

    // never above 30° in Belgium, high in Namibia at night
    tripObject('Southern Galaxy', 16.0, -50.0, 10.0, 5.0);
    tripObject('Seen Southern Galaxy', 16.5, -55.0, 10.0, 5.0);
    // high enough in Belgium, but too faint for its sky
    tripObject('Faint Galaxy', 16.0, 5.0, 13.6, 2.0);
    // seen in Belgium too
    tripObject('Bright Galaxy', 16.0, 5.0, 9.0, 3.0);
    // in the daytime sky in June
    tripObject('Daytime Galaxy', 5.0, -50.0, 10.0, 5.0);
    // never rises high in Namibia
    tripObject('Northern Galaxy', 16.0, 70.0, 10.0, 5.0);

    DB::table('observations')->insert(['observer_username' => $user->username, 'observed_on' => '2025-10-01', 'instrument_id' => $dobson->id,
        'target_type'                                      => 'deepsky', 'target_table' => 'objects', 'target_key' => 'Seen Southern Galaxy', 'target_name' => 'Seen Southern Galaxy',
        'has_drawing'                                      => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);

    return [$user->fresh(), $dobson];
}

function namibia(array $overrides = []): array
{
    return [
        'latitude' => -23.3, 'longitude' => 16.4, 'sqm' => 21.8, 'timezone' => 'Africa/Windhoek',
        'from'     => '2026-06-10', 'until' => '2026-06-12', 'name' => 'Namibia', ...$overrides,
    ];
}

it('lists the objects that are new at the site of the trip', function () {
    [$user] = tripObserver();

    $planner = TripPlanner::for($user, namibia());
    $result  = $planner->compute();

    expect($planner->unavailable())->toBeNull()
        ->and(array_column($result['objects'], 'name'))->toEqualCanonicalizing(['Southern Galaxy', 'Seen Southern Galaxy', 'Faint Galaxy']);

    $byName = collect($result['objects'])->keyBy('name');
    expect($byName['Southern Galaxy'])->toMatchArray(['reason' => TripPlanner::LOW, 'nights' => ['2026-06-10', '2026-06-11', '2026-06-12']])
        ->and($byName['Southern Galaxy']['home_altitude'])->toBeLessThan(0)
        ->and($byName['Faint Galaxy']['reason'])->toBe(TripPlanner::FAINTER);

    // seen objects are not shown
    expect(array_column($planner->objects($result)['objects'], 'name'))->toEqualCanonicalizing(['Southern Galaxy', 'Faint Galaxy'])
        ->and(array_column($planner->objects($result, TripPlanner::FAINTER)['objects'], 'name'))->toBe(['Faint Galaxy']);
});

it('keeps the trip within the maximum number of nights', function () {
    $trip = TripPlanner::tripSpec(namibia(['until' => '2026-08-01', 'sqm' => 30]));

    expect($trip['until'])->toBe('2026-06-23')
        ->and($trip['sqm'])->toBe(22.5);
});

it('says when it does not get dark at the site', function () {
    [$user] = tripObserver();

    expect(TripPlanner::for($user, namibia(['latitude' => 78.2, 'longitude' => 15.6, 'timezone' => 'Arctic/Longyearbyen']))->unavailable())
        ->toBe('It does not get dark there in these nights.');
});

it('proposes the time zone of a location nearby, else of the longitude', function () {
    [$user] = tripObserver();
    LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'name' => 'Farm', 'latitude' => -22.9, 'longitude' => 17.1, 'timezone' => 'Africa/Windhoek']);

    expect(TripPlanner::guessTimezone(-23.3, 16.4))->toBe('Africa/Windhoek')
        ->and(TripPlanner::guessTimezone(-45.0, -120.0))->toBe('Etc/GMT+8')
        ->and(TripPlanner::suggestSqm(-45.0, -120.0))->toBe(TripPlanner::DEFAULT_SQM);
});

it('makes an observing list with times for each night', function () {
    [$user]  = tripObserver();
    $planner = TripPlanner::for($user, namibia());
    $result  = $planner->compute();

    $lists = $planner->createLists($result);

    expect($lists)->toHaveCount(2);
    foreach ($lists as $list) {
        expect($list->planned_timezone)->toBe('Africa/Windhoek')
            ->and($list->name)->toStartWith('Namibia, night of ')
            ->and($list->items()->whereNull('planned_at')->count())->toBe(0);
    }
    expect(collect($lists)->flatMap(fn ($list) => $list->items()->pluck('object_name'))->all())
        ->toEqualCanonicalizing(['Southern Galaxy', 'Faint Galaxy']);

    $single = $planner->createLists($result, null, '2026-06-11', 10, 'My trip');
    expect($single)->toHaveCount(1)
        ->and($single[0]->name)->toBe('My trip')
        ->and($single[0]->planned_night->toDateString())->toBe('2026-06-11')
        ->and($single[0]->items()->count())->toBe(2);
});

it('picks a place on the map, calculates and makes the lists', function () {
    [$user] = tripObserver();

    $this->actingAs($user)->get(route('observing-list.trip'))->assertOk();

    Livewire::actingAs($user)
        ->test(TripPlannerPage::class)
        ->call('pick', -23.3, 16.4)
        ->assertSet('lat', '-23.3')
        ->assertSet('sqm', (string) TripPlanner::DEFAULT_SQM)
        ->set('timezone', 'Africa/Windhoek')
        ->set('from', '2026-06-10')
        ->set('until', '2026-06-12')
        ->set('place', 'Namibia')
        ->call('calculate')
        ->assertHasNoErrors()
        ->assertSee('Southern Galaxy')
        ->assertSee('Faint Galaxy')
        ->assertDontSee('Bright Galaxy')
        ->set('listNight', '2026-06-10')
        ->call('create')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(ObservingList::where('owner_user_id', $user->id)->count())->toBe(1);
});

it('refuses a trip longer than the maximum number of nights', function () {
    [$user] = tripObserver();

    Livewire::actingAs($user)
        ->test(TripPlannerPage::class)
        ->set('lat', '-23.3')
        ->set('lon', '16.4')
        ->set('timezone', 'Africa/Windhoek')
        ->set('from', '2026-06-10')
        ->set('until', '2026-07-10')
        ->call('calculate')
        ->assertHasErrors('until');
});

it('calculates at once from the link of a starparty', function () {
    [$user] = tripObserver();

    Livewire::withQueryParams(['lat' => '-23.3', 'lon' => '16.4', 'tz' => 'Africa/Windhoek', 'from' => '2026-06-10', 'until' => '2026-06-12', 'place' => 'Namibia'])
        ->actingAs($user)
        ->test(TripPlannerPage::class)
        ->assertSee('Southern Galaxy');

    $this->actingAs($user)->get(route('observing-lists.index'))
        ->assertOk()
        ->assertSee(route('observing-list.trip'));
});
