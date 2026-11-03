<?php

use App\Livewire\LocationHorizon;
use App\Models\Location;
use App\Models\User;
use App\Services\LocalHorizon;
use App\Services\NightPlanner;
use App\Services\NightWindow;
use App\Services\SeasonalVisibility;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// The local horizon of a location (LocalHorizon): the trees and houses
// around it, which the planners take into account, and its editor on the
// location page.

/** A horizon of 40° over the southern half (azimuth 90–270°), open in the north. */
function southernWall(): array
{
    return array_map(fn (int $i) => $i * LocalHorizon::STEP >= 90 && $i * LocalHorizon::STEP <= 270 ? 40 : 0, range(0, LocalHorizon::POINTS - 1));
}

function horizonLocation(User $owner, ?array $horizon = null, bool $hidden = false): Location
{
    $id = DB::table('locations')->insertGetId([
        'name'              => 'Garden', 'longitude' => 4.7, 'latitude' => 50.9, 'timezone' => 'Europe/Brussels',
        'limitingMagnitude' => -999, 'skyBackground' => -999, 'elevation' => 0, 'country' => 'Belgium', 'active' => true,
        'user_id'           => $owner->id, 'observer' => $owner->username, 'observations' => 0, 'slug' => 'garden-'.$owner->id,
        'hidden'            => $hidden, 'horizon' => $horizon === null ? null : json_encode($horizon),
        'created_at'        => now(), 'updated_at' => now(),
    ]);

    return Location::findOrFail($id);
}

it('interpolates the altitude between the points and goes round through north', function () {
    $altitudes     = array_fill(0, LocalHorizon::POINTS, 0);
    $altitudes[0]  = 20;
    $altitudes[23] = 10;
    $horizon       = LocalHorizon::fromArray($altitudes);

    expect($horizon->altitudeAt(0))->toBe(20.0)
        ->and($horizon->altitudeAt(7.5))->toBe(10.0)
        ->and($horizon->altitudeAt(352.5))->toBe(15.0)
        ->and($horizon->altitudeAt(360))->toBe(20.0)
        ->and($horizon->max)->toBe(20.0);
});

it('stores an open horizon as none', function () {
    expect(LocalHorizon::fromArray(array_fill(0, LocalHorizon::POINTS, 0)))->toBeNull()
        ->and(LocalHorizon::fromArray([10, 20]))->toBeNull()
        ->and(LocalHorizon::normalize(array_fill(0, LocalHorizon::POINTS, 95))[0])->toBe((float) LocalHorizon::MAX_ALTITUDE);
});

it('reads a horizon of azimuths and altitudes, interpolating between the points', function () {
    $altitudes = LocalHorizon::parse("# Stellarium horizon.txt\n0 10\n90, 30\n180;10\n270\t30\nnot a point\n");

    expect($altitudes)->toHaveCount(LocalHorizon::POINTS)
        ->and($altitudes[0])->toBe(10.0)
        ->and($altitudes[3])->toBe(20.0)
        ->and($altitudes[6])->toBe(30.0)
        ->and($altitudes[21])->toBe(20.0)
        ->and(LocalHorizon::parse("0 10\n"))->toBeNull();
});

it('gives the azimuth from the north through the east', function () {
    [$altitude, $azimuth] = LocalHorizon::horizontal(51.0, 0.0, 0.0);
    expect($altitude)->toEqualWithDelta(39.0, 1e-9)->and($azimuth)->toEqualWithDelta(180.0, 1e-9);

    [, $west] = LocalHorizon::horizontal(51.0, 90.0, 0.0);
    [, $east] = LocalHorizon::horizontal(51.0, -90.0, 0.0);
    expect($west)->toEqualWithDelta(270.0, 1e-9)->and($east)->toEqualWithDelta(90.0, 1e-9);
});

it('leaves out the hours a point stands behind the trees', function () {
    $horizon = LocalHorizon::fromArray(southernWall());
    // The equator transits at 39° in the south, behind the 40° wall: never clear
    expect($horizon->clearHourAngles(51.0, 0.0, 10.0))->toBe([])
        // a point at +30° transits at 69°, above the wall
        ->and($horizon->isClear(51.0, 0.0, 30.0))->toBeTrue()
        ->and($horizon->clearHourAngles(51.0, 30.0, 10.0))->not->toBe([]);

    // Above the wall the planners need not look at the horizon
    expect($horizon->blocksAbove(40.0))->toBeFalse()->and($horizon->blocksAbove(30.0))->toBeTrue();
});

it('makes the night planner skip the objects behind the horizon', function () {
    $window = NightWindow::for(50.9, 4.7, '2026-01-15', 'Europe/Brussels');
    // M 42 (dec -5°) stands at most 34° high, in the south
    $points = [['ra' => 83.8, 'dec' => -5.4], ['ra' => 10.7, 'dec' => 41.3]];

    $open   = (new NightPlanner(50.9, 10.0, 10))->visibility($window, $points);
    $walled = (new NightPlanner(50.9, 10.0, 10, LocalHorizon::fromArray(southernWall())))->visibility($window, $points);

    expect($open[0]['intervals'])->not->toBe([])
        ->and($walled[0]['intervals'])->toBe([])
        ->and($walled[0]['transits'])->toBe([])
        // M 31 stands in the north-west and the zenith: not behind the southern wall
        ->and($walled[1]['intervals'])->not->toBe([]);
});

it('takes the horizon into account for the months of a list', function () {
    $owner  = User::factory()->create();
    $open   = SeasonalVisibility::for(horizonLocation($owner), 2026, 1, 'Europe/Brussels');
    $walled = SeasonalVisibility::for(horizonLocation($owner, array_fill(0, LocalHorizon::POINTS, 70)), 2026, 1, 'Europe/Brussels');

    // M 42 is well placed in winter, but not over a 70° wall
    expect($open->wellPlacedMonths(83.8, -5.4))->toContain(1)
        ->and($walled->wellPlacedMonths(83.8, -5.4))->toBe([]);
});

it('lets the owner draw, paste and save the horizon', function () {
    $owner    = User::factory()->create();
    $location = horizonLocation($owner);

    $this->actingAs($owner);
    $component = Livewire::test(LocationHorizon::class, ['location' => $location])
        ->assertSet('canEdit', true)
        ->call('edit')
        ->set('import', "0 0\n90 0\n180 40\n270 0")
        ->call('importText')
        ->assertHasNoErrors()
        ->call('save')
        ->assertSet('editing', false);

    expect($location->fresh()->horizon[12])->toEqual(40);

    $component->call('edit')->call('clear')->call('save');
    expect($location->fresh()->horizon)->toBeNull();
});

it('refuses a pasted horizon without points and an altitude out of range', function () {
    $owner = User::factory()->create();
    $this->actingAs($owner);

    Livewire::test(LocationHorizon::class, ['location' => horizonLocation($owner)])
        ->call('edit')
        ->set('import', 'nothing here')
        ->call('importText')
        ->assertHasErrors('import')
        ->set('altitudes.3', 95)
        ->call('save')
        ->assertHasErrors('altitudes.3');
});

it('does not let other observers change the horizon', function () {
    $location = horizonLocation(User::factory()->create(), southernWall());
    $this->actingAs(User::factory()->create());

    Livewire::test(LocationHorizon::class, ['location' => $location])
        ->assertSet('canEdit', false)
        ->assertSee(__('Local horizon'))
        ->call('save')
        ->assertForbidden();
});

it('shows the horizon on the location page, not of a hidden location to others', function () {
    $owner    = User::factory()->create();
    $location = horizonLocation($owner, southernWall(), hidden: true);
    $url      = route('location.show', [$owner->slug, $location->slug]);

    $this->actingAs($owner)->get($url)->assertOk()->assertSee(__('Local horizon'));
    $this->actingAs(User::factory()->create())->get($url)->assertOk()->assertDontSee(__('Local horizon'));
});
