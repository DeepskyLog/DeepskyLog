<?php

use App\Livewire\CreateSession;
use App\Livewire\NightConditions;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use App\Services\Weather\NightWeather;
use Database\Factories\LocationFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

// The weather of past nights from Open-Meteo's historical archive: stored per
// cell and UTC day, summarised over the dark hours, shown on the observation
// page and on the session page without weather, and filled in on the session
// form. The faked archive is clear from 20h to 4h UTC and overcast by day.

beforeEach(function () {
    config(['services.open_meteo.archive_enabled' => true]);
    Carbon::setTestNow('2026-10-30 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function fakeArchive(int $status = 200): void
{
    Http::fake(['archive-api.open-meteo.com/*' => function (Request $request) use ($status) {
        if ($status !== 200) {
            return Http::response(['reason' => 'Too many requests'], $status);
        }
        $start = Carbon::parse($request->data()['start_date'], 'UTC')->getTimestamp();
        $end   = Carbon::parse($request->data()['end_date'], 'UTC')->addDay()->getTimestamp();
        $time  = range($start, $end - 3600, 3600);
        $clear = fn (int $t) => (int) gmdate('G', $t) >= 20 || (int) gmdate('G', $t) < 4;

        return Http::response(['hourly' => [
            'time'                 => $time,
            'temperature_2m'       => array_map(fn (int $t) => $clear($t) ? 4.2 : 9.8, $time),
            'relative_humidity_2m' => array_map(fn (int $t) => $clear($t) ? 92 : 70, $time),
            'dew_point_2m'         => array_fill(0, count($time), 3.1),
            'cloud_cover'          => array_map(fn (int $t) => $clear($t) ? 0 : 100, $time),
            'wind_speed_10m'       => array_fill(0, count($time), 7.6),
        ]]);
    }]);
}

function weatherLocation(?User $user = null): Location
{
    $user ??= User::factory()->create();

    return LocationFactory::new()->create([
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Ardennes field',
        'latitude' => 50.1, 'longitude' => 5.5, 'timezone' => 'Europe/Brussels', 'elevation' => 400,
    ]);
}

function weatherObservation(Location $location, string $date, ?string $time): Observation
{
    $user = User::find($location->user_id);

    return Observation::create([
        'observer_username' => $user->username, 'observer_user_id' => $user->id, 'location_id' => $location->id,
        'observed_on'       => $date, 'observed_time' => $time, 'target_type' => 'deepsky', 'target_table' => 'objects',
        'target_key'        => 'M 31', 'target_name' => 'M 31', 'description' => 'Seen',
    ]);
}

it('summarises the dark hours of a past night and stores the days', function () {
    fakeArchive();

    $night = NightWeather::night(50.1, 5.5, 'Europe/Brussels', '2026-10-01');

    expect($night['kind'])->toBe('astronomical')
        ->and($night['cloud'])->toBeGreaterThan(0)->toBeLessThan(50)
        ->and($night['clear_minutes'])->toBe(480)
        ->and($night['temperature'])->toBe(['min' => 4.2, 'max' => 9.8])
        ->and($night['wind'])->toBe(8)
        ->and($night['dew'])->toBeTrue()
        ->and(NightWeather::summary($night))->toContain('clear 8 of')->toContain('4 °C to 10 °C')->toContain('Risk of dew');

    // One request; the days are stored, so the next page asks nothing
    Http::assertSentCount(1);
    expect(DB::table('night_weather')->count())->toBe(2);
    NightWeather::night(50.1, 5.5, 'Europe/Brussels', '2026-10-01');
    Http::assertSentCount(1);
});

it('does not ask for nights the archive does not hold yet', function () {
    fakeArchive();
    $observation = weatherObservation(weatherLocation(), '2026-10-28', '21:00:00');

    expect(NightWeather::appliesToObservation($observation))->toBeFalse()
        ->and(NightWeather::night(50.1, 5.5, 'Europe/Brussels', '2026-10-27'))->toBeNull();
    Http::assertNothingSent();
});

it('gives a morning observation the night before and the hour of the observation', function () {
    fakeArchive();
    // 01:00 UT on 2 October is 03:00 in Brussels: the night of 1 October
    $observation = weatherObservation(weatherLocation(), '2026-10-02', '01:00:00');

    $weather = NightWeather::forObservation($observation);

    expect($weather['night']['night'])->toBe('2026-10-01')
        ->and($weather['at'])->toMatchArray(['time' => '03:00', 'cloud' => 0, 'temperature' => 4.2]);
});

it('finds the nights of a session', function () {
    $location = weatherLocation();
    $session  = fn (string $begin, string $end) => new ObservationSession(['locationid' => $location->id, 'begindate' => $begin, 'enddate' => $end]);

    // The form stores dates: one date is one night, several dates end on the morning of the last one
    expect(array_column(NightWeather::sessionNights($session('2026-10-01 00:00:00', '2026-10-01 23:59:59')), 'night'))->toBe(['2026-10-01'])
        ->and(array_column(NightWeather::sessionNights($session('2026-10-01 00:00:00', '2026-10-03 23:59:59')), 'night'))->toBe(['2026-10-01', '2026-10-02']);

    // A session with times keeps its hours
    $nights = NightWeather::sessionNights($session('2026-10-01 21:00:00', '2026-10-02 01:30:00'));
    expect($nights)->toHaveCount(1)
        ->and($nights[0]['night'])->toBe('2026-10-01')
        ->and($nights[0]['hours'][1] - $nights[0]['hours'][0])->toBe(16200);
});

it('pauses after Open-Meteo refused a request', function () {
    fakeArchive(429);

    expect(NightWeather::night(50.1, 5.5, 'Europe/Brussels', '2026-10-01'))->toBeNull()
        ->and(NightWeather::paused())->toBeTrue();
    NightWeather::night(50.1, 5.5, 'Europe/Brussels', '2026-09-01');
    Http::assertSentCount(1);
});

it('shows the conditions of the night on the observation', function () {
    fakeArchive();
    $observation = weatherObservation(weatherLocation(), '2026-10-01', '21:00:00');

    Livewire::withoutLazyLoading()->test(NightConditions::class, ['observationId' => $observation->id])
        ->assertSee('Conditions that night')
        ->assertSee('At 23:00')
        ->assertSee('Open-Meteo.com');
});

it('shows the weather of the night on a session without weather', function () {
    fakeArchive();
    $location = weatherLocation();
    $session  = ObservationSession::create([
        'name'      => 'Ardennes', 'observerid' => User::find($location->user_id)->username, 'locationid' => $location->id,
        'begindate' => '2026-10-01 00:00:00', 'enddate' => '2026-10-01 23:59:59', 'weather' => '', 'active' => 1,
    ]);

    expect(NightWeather::appliesToSession($session))->toBeTrue();
    Livewire::withoutLazyLoading()->test(NightConditions::class, ['sessionId' => $session->id])
        ->assertSee('Not written by the observer')
        ->assertSee('Clouds');
});

it('fills in the weather on the session form', function () {
    fakeArchive();
    $user     = User::factory()->create();
    $location = weatherLocation($user);

    Livewire::actingAs($user)->test(CreateSession::class)
        ->call('fillWeather')
        ->assertHasErrors('weather')
        ->set('locationid', $location->id)
        ->set('begindate', '2026-10-01')
        ->set('enddate', '2026-10-01')
        ->call('fillWeather')
        ->assertHasNoErrors()
        ->assertDispatched('session-weather-filled', fn (string $name, array $params) => str_contains($params['html'], 'Clouds') && str_contains($params['html'], 'Open-Meteo'));
});

it('fetches the nights of the sessions and observations ahead', function () {
    fakeArchive();
    $location = weatherLocation();
    weatherObservation($location, '2026-08-01', '22:00:00');
    ObservationSession::create([
        'name'      => 'Ardennes', 'observerid' => User::find($location->user_id)->username, 'locationid' => $location->id,
        'begindate' => '2026-10-01 00:00:00', 'enddate' => '2026-10-01 23:59:59', 'active' => 1,
    ]);

    $this->artisan('weather:fill-nights')->expectsOutputToContain('requests: 2')->assertSuccessful();
    Http::assertNothingSent();

    $this->artisan('weather:fill-nights --apply')->expectsOutputToContain('Fetched: 2')->assertSuccessful();
    // The session first
    $sent = Http::recorded()->map(fn ($pair) => $pair[0]->data()['start_date'])->all();
    expect($sent)->toBe(['2026-10-01', '2026-07-31']);

    $this->artisan('weather:fill-nights')->expectsOutputToContain('requests: 0')->assertSuccessful();
});

it('puts the weather of the night on the session page only when the observer wrote none', function () {
    fakeArchive();
    $location = weatherLocation();
    $user     = User::find($location->user_id);
    $session  = fn (string $weather) => ObservationSession::create([
        'name'      => 'Ardennes '.$weather, 'observerid' => $user->username, 'locationid' => $location->id,
        'begindate' => '2026-10-01 00:00:00', 'enddate' => '2026-10-01 23:59:59', 'weather' => $weather, 'active' => 1,
    ]);

    $this->get(route('session.show', [$user->slug, $session('')->slug]))->assertOk()->assertSee('night-conditions');
    $this->get(route('session.show', [$user->slug, $session('Clear and cold')->slug]))->assertOk()
        ->assertSee('Clear and cold')->assertDontSee('night-conditions');
});
