<?php

use App\Models\User;
use App\Services\News\SkyPositions;
use App\Services\Planets\MarsFeatures;
use App\Services\Planets\MarsVenus;
use App\Services\Planets\PlanetDisk;
use App\Services\Planets\PlanetDiskRenderer;
use Carbon\Carbon;
use Database\Factories\LocationFactory;

// Mars and Venus: the physical ephemeris of Mars (Meeus chapter 42), the
// phase of Venus, the features of Mars on the central meridian, the next
// opposition and elongations, and the page /mars-venus.

/** The night of the opposition of Mars in 2027. */
const MARS_OPPOSITION_NIGHT = '2027-02-19';

function marsVenusObserver(): User
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

it('computes the physical ephemeris of Mars of Meeus example 42.a', function () {
    // 1992 November 9, 0h TD
    $at   = gmmktime(0, 0, 0, 11, 9, 1992);
    $mars = PlanetDisk::at('mars', $at - (int) round(SkyPositions::deltaT($at)));

    expect($mars['de'])->toEqualWithDelta(12.44, 0.02)
        ->and($mars['ds'])->toEqualWithDelta(-2.76, 0.02)
        ->and($mars['p'])->toEqualWithDelta(347.64, 0.05)
        ->and($mars['cm'])->toEqualWithDelta(111.55, 0.3)
        ->and($mars['diameter'])->toEqualWithDelta(10.75, 0.01)
        ->and($mars['illuminated'])->toEqualWithDelta(0.9012, 0.0005)
        ->and($mars['defect'])->toEqualWithDelta(1.06, 0.01)
        // Q, the position angle of the greatest defect, opposite the bright limb
        ->and(SkyPositions::wrap($mars['bright_limb'] + 180))->toEqualWithDelta(279.91, 0.1);
});

it('starts the Mars years at Ls 0', function () {
    // Mars years 37 and 38 (Piqueux et al.)
    foreach (['2022-12-26 12:00', '2024-11-12 12:00'] as $start) {
        $ls = PlanetDisk::at('mars', Carbon::parse($start, 'UTC')->getTimestamp())['ls'];
        expect(SkyPositions::wrap180($ls))->toEqualWithDelta(0.0, 0.6);
    }
    expect(MarsFeatures::season(65.0))->toBe('spring')
        ->and(MarsFeatures::dustStormSeason(250.0))->toBeTrue()
        ->and(MarsFeatures::capEdge('south', 300.0))->toBeLessThan(MarsFeatures::capEdge('south', 180.0));
});

it('gives Venus half lit at its greatest elongation', function () {
    $venus = PlanetDisk::at('venus', Carbon::parse('2026-08-15 12:00', 'UTC')->getTimestamp());

    expect($venus['elongation'])->toEqualWithDelta(45.9, 0.2)
        ->and($venus['illuminated'])->toEqualWithDelta(0.49, 0.02)
        ->and($venus['diameter'])->toEqualWithDelta(24.4, 0.3);
});

it('finds the next opposition of Mars and the phases of Venus', function () {
    $events = MarsVenus::events('2026-10-01');
    $days   = fn (string $planet) => collect($events[$planet])->mapWithKeys(fn (array $event) => [$event['kind'] => gmdate('Y-m-d', $event['at'])]);

    expect($days('mars')['opposition'])->toBe('2027-02-19')
        ->and($events['mars'][1]['diameter'])->toEqualWithDelta(13.8, 0.1)
        ->and($days('venus')['inferior_conjunction'])->toBe('2026-10-24')
        ->and($days('venus')['greatest_elongation_west'])->toBe('2027-01-03')
        ->and($days('venus')['superior_conjunction'])->toBe('2027-08-12');
});

it('names the features on the central meridian and finds their transits', function () {
    $disk = PlanetDisk::at('mars', Carbon::parse('2027-02-19 23:00', 'UTC')->getTimestamp());
    $on   = collect(MarsFeatures::facing($disk))->where('on_meridian', true)->pluck('name');
    expect($on)->toContain('Olympus Mons (Nix Olympica)');

    $location = LocationFactory::new()->make(['latitude' => 50.85, 'longitude' => 4.35]);
    $transits = MarsVenus::featureTransits($location, 'syrtis-major', MARS_OPPOSITION_NIGHT, 'Europe/Brussels');
    expect($transits)->not->toBeEmpty();
    foreach ($transits as $transit) {
        $cm = PlanetDisk::at('mars', $transit['at'])['cm'];
        expect(abs(MarsFeatures::fromMeridian(MarsFeatures::FEATURES['syrtis-major']['lon'], $cm)))->toBeLessThan(0.5);
    }

    $svg = (new PlanetDiskRenderer($disk))->svg();
    expect($svg)->toStartWith('<svg')->toContain('Tharsis')->toContain('North polar cap');
});

it('shows the page of a night', function () {
    $this->actingAs(marsVenusObserver());

    $this->get('/mars-venus?night=' . MARS_OPPOSITION_NIGHT . '&view=mirror&feature=syrtis-major')
        ->assertOk()
        ->assertSee('Mars and Venus in the night of')
        ->assertSee('Central meridian')
        ->assertSee('crosses the central meridian')
        ->assertSee('Opposition')
        ->assertSee('The coming weeks')
        ->assertSee('<svg', false);
});

it('asks for a location without one', function () {
    $this->get('/mars-venus')->assertOk()->assertSee('Add a location with coordinates');
});
