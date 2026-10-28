<?php

use App\Models\NewsItem;
use App\Models\User;
use App\Services\News\LocalCircumstances;
use App\Services\Planets\PlanetMoonEvents;
use App\Services\Planets\PlanetMoons;
use App\Services\Planets\PlanetMoonsRenderer;
use Carbon\Carbon;
use Database\Factories\LocationFactory;

// The moons of Jupiter and Saturn: their positions (PlanetMoons, Meeus
// chapters 44 and 46), their events (PlanetMoonEvents) checked against JPL
// Horizons, the page /jupiter-saturn, the home page box and the news items of
// the shadows on Jupiter and Saturn.

/** The night of 18 December 2026: the shadows of Io and Europa on Jupiter together. */
const PLANET_MOONS_NIGHT = '2026-12-18';

afterEach(fn () => Carbon::setTestNow());

function planetMoonsObserver(): User
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

/** @return array<string, array<string, mixed>> the events keyed by moon and type */
function planetMoonEventsOn(string $from, int $hours): array
{
    $start  = Carbon::parse($from, 'UTC')->getTimestamp();
    $events = [];
    foreach (PlanetMoonEvents::between($start, $start + $hours * 3600) as $event) {
        $events[($event['moon'] ?? 'grs') . ':' . $event['type']] ??= $event;
    }

    return $events;
}

it('computes the positions of the Galilean moons of Meeus example 44.b', function () {
    $jupiter = PlanetMoons::jupiter(gmmktime(0, 0, 0, 12, 16, 1992));

    foreach (['io' => [-3.4502, 0.2137], 'europa' => [7.4418, 0.2753], 'ganymede' => [1.2011, 0.5900], 'callisto' => [7.0720, 1.0291]] as $moon => [$x, $y]) {
        expect($jupiter['earth'][$moon]['x'])->toEqualWithDelta($x, 0.0005)
            ->and($jupiter['earth'][$moon]['y'])->toEqualWithDelta($y, 0.0005);
    }
});

it('computes the positions of the moons of Saturn as Meeus chapter 46', function () {
    // The values of the JavaScript port of Meeus chapter 46 (astronomia) for 1992 December 16, 0h UT
    $saturn = PlanetMoons::saturn(gmmktime(0, 0, 0, 12, 16, 1992));

    foreach (['tethys' => [-4.8570, -0.2084], 'dione' => [-0.6947, 1.7608], 'rhea' => [-7.2148, 1.3830], 'titan' => [-17.2380, 2.7789], 'iapetus' => [-54.0969, 5.2493]] as $moon => [$x, $y]) {
        expect($saturn['earth'][$moon]['x'])->toEqualWithDelta($x, 0.002)
            ->and($saturn['earth'][$moon]['y'])->toEqualWithDelta($y, 0.002);
    }
    // Between the equinoxes of 1980 and 1995 the northern face of the rings was turned to us.
    expect($saturn['ring_tilt'])->toBeGreaterThan(10.0);
});

it('finds the transits, shadows, eclipses and occultations at the moments of JPL Horizons', function () {
    $events = planetMoonEventsOn('2026-11-20 06:00', 42);

    // Horizons: the moon's (or, seen from the Sun, its shadow's) centre crossing the limb of the flattened disk
    foreach ([
        'io:transit_start'       => '2026-11-20 17:01:09',
        'io:transit_end'         => '2026-11-20 19:18:57',
        'io:shadow_start'        => '2026-11-20 15:46:04',
        'io:shadow_end'          => '2026-11-20 18:03:05',
        'europa:shadow_start'    => '2026-11-20 11:44:21',
        'europa:transit_end'     => '2026-11-20 17:13:05',
        'io:eclipse_start'       => '2026-11-21 12:55:45',
        'io:occultation_end'     => '2026-11-21 16:31:08',
        'ganymede:eclipse_start' => '2026-11-21 23:12:33',
    ] as $key => $moment) {
        expect($events)->toHaveKey($key);
        expect($events[$key]['at'])->toEqualWithDelta(Carbon::parse($moment, 'UTC')->getTimestamp(), 90);
    }
    // Io disappears in the shadow and reappears from behind the disk: its occultation start and eclipse end are hidden
    expect($events)->not->toHaveKey('io:occultation_start')
        ->and($events)->not->toHaveKey('io:eclipse_end');
});

it('finds the elongations of Titan and the transits of the Great Red Spot', function () {
    $events = planetMoonEventsOn('2026-11-20 06:00', 30);

    // Horizons: Titan 194″ east of Saturn around 2026 November 21, 02:30 UT
    expect($events['titan:elongation_east']['at'])->toEqualWithDelta(Carbon::parse('2026-11-21 02:30', 'UTC')->getTimestamp(), 30 * 60)
        ->and($events['titan:elongation_east']['distance'])->toEqualWithDelta(194, 2);

    $transit = $events['grs:grs_transit']['at'];
    expect(PlanetMoons::jupiter($transit)['cm2'])->toEqualWithDelta(PlanetMoonEvents::grsLongitude($transit), 0.1)
        ->and(PlanetMoonEvents::grsLongitude(Carbon::parse(PlanetMoonEvents::GRS_EPOCH, 'UTC')->getTimestamp()))->toBe(PlanetMoonEvents::GRS_LONGITUDE);
});

it('finds the shadows of two moons on Jupiter and of Titan on Saturn for the news', function () {
    $december = PlanetMoonEvents::newsEvents(Carbon::parse('2026-12-17', 'UTC')->getTimestamp(), Carbon::parse('2026-12-20', 'UTC')->getTimestamp());
    expect(array_column($december, 'key'))->toBe(['double_shadow_transit:europa:io:2026-12-18'])
        ->and(gmdate('H:i', $december[0]['data']['start']))->toBe('23:17')
        ->and(gmdate('H:i', $december[0]['data']['end']))->toBe('01:02');

    // Titan's shadow in the season around Saturn's equinox of 2025 (Horizons: 16:37 to 21:26 UT)
    $titan = PlanetMoonEvents::newsEvents(Carbon::parse('2025-01-07', 'UTC')->getTimestamp(), Carbon::parse('2025-01-08', 'UTC')->getTimestamp());
    expect(array_column($titan, 'kind'))->toBe(['titan_shadow_transit'])
        ->and($titan[0]['data']['start'])->toEqualWithDelta(Carbon::parse('2025-01-07 16:37', 'UTC')->getTimestamp(), 6 * 60);
});

it('keeps the events of a night that can be seen from the place', function () {
    $night = PlanetMoonEvents::night(50.85, 4.35, PLANET_MOONS_NIGHT, 'Europe/Brussels');

    expect($night['planets']['jupiter']['best'])->not->toBeNull();
    foreach ($night['events'] as $event) {
        expect($event['altitude'])->toBeGreaterThanOrEqual(PlanetMoonEvents::MIN_ALTITUDE)
            ->and($event['at'])->toBeGreaterThanOrEqual($night['planets'][$event['planet']]['from']);
    }
    expect(array_map(fn ($event) => $event['moon'] . ':' . $event['type'], array_slice($night['events'], 0, 2)))
        ->toBe(['europa:shadow_start', 'io:shadow_start']);
});

it('draws the planets with their moons and shadows', function () {
    $at  = Carbon::parse('2026-12-19 00:00', 'UTC')->getTimestamp();
    $svg = (new PlanetMoonsRenderer('jupiter', PlanetMoons::jupiter($at), 'south', PlanetMoonEvents::grsLongitude($at)))->svg();
    expect($svg)->toStartWith('<svg')->toContain('The shadow of Io')->toContain('The shadow of Europa')->toContain('Ganymede');

    $svg = (new PlanetMoonsRenderer('saturn', PlanetMoons::saturn($at)))->svg();
    expect($svg)->toContain('Titan')->toContain('<path');
});

it('shows the page of a night with the events and the coming nights', function () {
    $this->actingAs(planetMoonsObserver());

    $this->get('/jupiter-saturn?night=' . PLANET_MOONS_NIGHT . '&view=mirror')
        ->assertOk()
        ->assertSee('The shadow of Europa appears on Jupiter')
        ->assertSee('Io starts its transit across Jupiter')
        ->assertSee('The coming nights')
        ->assertSee('<svg', false);
});

it('asks for a location without one', function () {
    $this->get('/jupiter-saturn')->assertOk()->assertSee('Add a location with coordinates');
});

it('shows the events still to come tonight on the home page', function () {
    Carbon::setTestNow(Carbon::parse(PLANET_MOONS_NIGHT . ' 17:00', 'Europe/Brussels'));
    $this->actingAs(planetMoonsObserver());

    $this->get('/')->assertOk()->assertSee('Jupiter and Saturn tonight')->assertSee('The shadow of Io appears on Jupiter');
});

it('shows the coming night on the home page once the events of the past night are over', function () {
    // The morning after: the night of the ephemeris date is the one that just ended
    Carbon::setTestNow(Carbon::parse('2026-12-19 10:00', 'Europe/Brussels'));
    $result = PlanetMoonEvents::forHomePage(planetMoonsObserver());

    expect($result['night']['night'])->toBe('2026-12-19')
        ->and($result['events'])->not->toBeEmpty();
});

it('announces two shadows on Jupiter two days before, with the part seen from the reader', function () {
    $this->travelTo(Carbon::parse('2026-12-15 12:00', 'UTC'));

    $this->artisan('news:astronomical-events', ['--days' => 5, '--apply' => true])->assertSuccessful();
    $item = NewsItem::where('event_key', 'double_shadow_transit:europa:io:2026-12-18')->firstOrFail();
    expect($item->published_at->format('Y-m-d H:i'))->toBe('2026-12-16 23:17');

    $local = LocalCircumstances::compute($item->kind, $item->event_at->timestamp, $item->data, 50.85, 4.35);
    expect($local['visible']['from'])->toBe($item->data['start'])
        ->and($local['visible']['altitude'])->toBeGreaterThan(30.0);

    $this->travelTo(Carbon::parse('2026-12-18 12:00', 'UTC'));
    $this->actingAs(planetMoonsObserver());
    $this->get(route('news.show', $item))->assertOk()
        ->assertSee('Two shadows on Jupiter on 19 December 2026')
        ->assertSee('The shadow of Europa from');
});

it('lets only administrators and database experts keep the longitude of the Great Red Spot', function (string $team, int $status) {
    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('admin.great-red-spot'))
        ->assertStatus($status);
})->with([
    ['Observers', 403],
    ['Database Experts', 200],
    ['Administrators', 200],
]);

it('uses the newest longitude of the Great Red Spot entered by an administrator', function () {
    $at = Carbon::parse('2026-11-01', 'UTC')->getTimestamp();
    // The migration starts with the value of Sky & Telescope: 91° on 1 June 2026, 21° a year
    expect(PlanetMoonEvents::grsLongitude($at))->toEqualWithDelta(91.0 + 21.0 * 153 / 365.25, 0.01);

    $this->actingAs($this->createUserAndAssignToTeam('Administrators'))
        ->post(route('admin.great-red-spot.store'), ['longitude' => 100.5, 'measured_on' => '2026-10-15', 'drift' => 18, 'source' => 'JUPOS'])
        ->assertRedirect(route('admin.great-red-spot'));
    expect(PlanetMoonEvents::grsLongitude($at))->toEqualWithDelta(100.5 + 18.0 * 17 / 365.25, 0.01);

    $this->get(route('admin.great-red-spot'))->assertOk()->assertSee('JUPOS');
    $this->post(route('admin.great-red-spot.store'), ['longitude' => 400, 'measured_on' => '2099-01-01', 'drift' => 18])
        ->assertSessionHasErrors(['longitude', 'measured_on']);

    $this->delete(route('admin.great-red-spot.destroy', \App\Models\GreatRedSpotPosition::where('source', 'JUPOS')->first()))->assertRedirect();
    expect(PlanetMoonEvents::grsLongitude($at))->toEqualWithDelta(91.0 + 21.0 * 153 / 365.25, 0.01);
});

it('asks the administrators and database experts every month to check the Great Red Spot', function () {
    $member = function (string $team): User {
        $user = User::factory()->create();
        $user->teams()->attach($this->getTeamFromString($team));
        $user->switchTeam($this->getTeamFromString($team));

        return $user;
    };
    $admin    = $member('Administrators');
    $expert   = $member('Database Experts');
    $observer = $member('Observers');

    $this->artisan('planets:grs-reminder')->assertSuccessful();
    expect(\App\Models\Message::count())->toBe(0);

    $this->artisan('planets:grs-reminder', ['--apply' => true])->assertSuccessful();
    expect(\App\Models\Message::whereIn('receiver', [$admin->username, $expert->username])->where('subject', 'Check the longitude of the Great Red Spot')->count())->toBe(2)
        ->and(\App\Models\Message::where('receiver', $observer->username)->exists())->toBeFalse();
});
