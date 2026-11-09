<?php

use App\Console\Commands\SendClearNightAlerts;
use App\Livewire\LocationTable;
use App\Livewire\NightListWizard;
use App\Models\Instrument;
use App\Models\InstrumentType;
use App\Models\Location;
use App\Models\Message;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\Weather\CoolDownAdvice;
use App\Services\Weather\NightForecast;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

// The cloud forecast of the coming nights (Open-Meteo) at a location, on the
// home page and the location page, and the message when a clear, dark night
// is coming. At the Ardennes site the night of 2026-11-09 has no Moon, the
// night of 2026-11-24 a full Moon all night.

beforeEach(function () {
    config(['services.open_meteo.enabled' => true]);
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * Fakes Open-Meteo with the cloud cover $cloud(timestamp) every hour of
 * seven days from $from (UTC), and the temperature, dew point and humidity
 * of the callables (else 10 °C, 2 °C and 80%).
 */
function fakeForecast(callable $cloud, string $from = '2026-11-08 00:00', int $status = 200, ?callable $temperature = null, ?callable $dewPoint = null, ?callable $humidity = null): void
{
    $start = Carbon::parse($from, 'UTC')->getTimestamp();
    $time  = array_map(fn (int $i) => $start + 3600 * $i, range(0, 7 * 24 - 1));
    $cover = array_map($cloud, $time);

    Http::fake(['api.open-meteo.com/*' => Http::response($status === 200 ? ['hourly' => [
        'time'                 => $time,
        'cloud_cover'          => $cover,
        'cloud_cover_low'      => $cover,
        'cloud_cover_mid'      => array_fill(0, count($time), 0),
        'cloud_cover_high'     => array_fill(0, count($time), 0),
        'relative_humidity_2m' => array_map($humidity ?? fn () => 80, $time),
        'wind_speed_10m'       => array_fill(0, count($time), 10),
        'temperature_2m'       => array_map($temperature ?? fn () => 10.0, $time),
        'dew_point_2m'         => array_map($dewPoint ?? fn () => 2.0, $time),
    ]] : ['reason' => 'Too many requests'], $status)]);
}

function forecastNight(string $night): array
{
    return NightForecast::night(NightForecast::hourly(50.1, 5.5), 50.1, 5.5, 400, 'Europe/Brussels', $night);
}

function forecastObserver(array $attributes = []): User
{
    $user     = User::factory()->create($attributes + ['clear_night_alert' => true, 'language' => 'en']);
    $location = LocationFactory::new()->create([
        'user_id'   => $user->id,
        'observer'  => $user->username,
        'name'      => 'Ardennes field',
        'latitude'  => 50.1,
        'longitude' => 5.5,
        'timezone'  => 'Europe/Brussels',
        'elevation' => 400,
    ]);
    $user->forceFill(['stdlocation' => $location->id])->save();

    return $user->fresh();
}

it('calls a clear night without Moon clear and dark', function () {
    fakeForecast(fn () => 0);

    $night = forecastNight('2026-11-09');

    expect($night['verdict'])->toBe('clear_dark')
        ->and($night['cloud'])->toBe(0)
        ->and($night['moon'])->toBeNull()
        ->and($night['clear_dark']['minutes'])->toBeGreaterThan(600)
        ->and($night['hours'])->toHaveCount(12);
});

it('calls a clear night with a full Moon clear with Moon', function () {
    fakeForecast(fn () => 0, '2026-11-23 00:00');

    $night = forecastNight('2026-11-24');

    expect($night['verdict'])->toBe('clear_moon')
        ->and($night['moon']['fraction'])->toBeGreaterThan(95)
        ->and($night['clear_dark'])->toBeNull();
});

it('finds the clear part of a night that clouds over', function () {
    // Clear until 21:00 UTC on 9 November, overcast after
    $clouds = Carbon::parse('2026-11-09 21:00', 'UTC')->getTimestamp();
    fakeForecast(fn (int $t) => $t < $clouds ? 10 : 100);

    $night = forecastNight('2026-11-09');

    expect($night['verdict'])->toBe('clear_dark')
        ->and($night['clear_dark']['from'])->toBe('18:53')
        ->and($night['clear_dark']['until'])->toBe('21:30');
});

it('calls a clear midsummer night clear in twilight, and sends no message for it', function () {
    // Near the new Moon of 15 June, the Sun never 18° below the horizon
    Carbon::setTestNow(Carbon::parse('2026-06-14 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0, '2026-06-13 00:00');

    $night = forecastNight('2026-06-14');
    expect($night['verdict'])->toBe('clear_twilight')
        ->and($night['dark']['twilight'])->toBe('nautical')
        ->and($night['moon'])->toBeNull()
        ->and($night['clear']['minutes'])->toBeGreaterThan(200)
        ->and($night['clear_dark'])->toBeNull()
        ->and($night['clear_dark_minutes'])->toBe(0)
        ->and(collect($night['hours'])->every(fn (array $hour) => $hour['twilight']))->toBeTrue()
        ->and(NightForecast::verdictLabel('clear_twilight'))->toBe('Clear, in twilight');

    forecastObserver();
    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0')->assertSuccessful();
});

it('calls a night with one clear hour partly cloudy and an overcast night cloudy', function () {
    $clear = Carbon::parse('2026-11-09 23:00', 'UTC')->getTimestamp();
    fakeForecast(fn (int $t) => $t === $clear ? 0 : 100);

    expect(forecastNight('2026-11-09')['verdict'])->toBe('partly')
        ->and(forecastNight('2026-11-10')['verdict'])->toBe('cloudy');
});

it('has no verdict for a night beyond the forecast', function () {
    fakeForecast(fn () => 0);

    expect(forecastNight('2026-11-20')['verdict'])->toBe('unknown');
});

it('asks Open-Meteo once per cell and caches the forecast', function () {
    fakeForecast(fn () => 0);

    NightForecast::hourly(50.1, 5.5);
    NightForecast::hourly(50.12, 5.52);

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'latitude=50.1') && str_contains($request->url(), 'cloud_cover_high'));
});

it('pauses after Open-Meteo refused a request', function () {
    fakeForecast(fn () => 0, status: 429);

    expect(NightForecast::hourly(50.1, 5.5))->toBeNull()
        ->and(NightForecast::hourly(-33.9, 18.4))->toBeNull();

    Http::assertSentCount(1);
});

it('does not wait for Open-Meteo in a page request', function () {
    fakeForecast(fn () => 0);

    expect(NightForecast::hourly(50.1, 5.5, fetch: false))->toBeNull();

    Http::assertNothingSent();
});

it('does nothing when the forecast is turned off', function () {
    config(['services.open_meteo.enabled' => false]);
    fakeForecast(fn () => 0);

    expect(NightForecast::hourly(50.1, 5.5))->toBeNull();

    Http::assertNothingSent();
});

it('shows the coming nights on the home page', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $user = forecastObserver(['clear_night_alert' => false]);
    NightForecast::hourly(50.1, 5.5);

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertSee('Coming nights')
        ->assertSee('Clear and dark')
        ->assertSee('observing-lists/night?night=2026-11-09', false)
        ->assertSee('Get a message when a clear, dark night is coming');
});

it('shows the forecast on the location page instead of ClearOutside once it is there', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $user     = forecastObserver();
    $location = Location::find($user->stdlocation);
    $url      = '/location/'.$user->slug.'/'.$location->slug;

    // Not cached yet: ClearOutside, and Open-Meteo is asked after the response
    $this->actingAs($user)->get($url)->assertOk()->assertSee('clearoutside.com', false)->assertDontSee('Open-Meteo.com');

    NightForecast::hourly(50.1, 5.5);
    $this->actingAs($user)->get($url)
        ->assertOk()
        ->assertSee('Clear and dark')
        ->assertSee('Open-Meteo.com')
        ->assertSee('observing-lists/night?night=2026-11-09', false)
        ->assertDontSee('clearoutside.com', false);

    // Another observer sees the forecast, without the links to a list for their own standard location
    $this->actingAs(User::factory()->create())->get($url)
        ->assertOk()
        ->assertSee('Clear and dark')
        ->assertDontSee('observing-lists/night?night=', false);
});

it('shows the verdicts of the coming nights in the location table', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $user = forecastObserver();
    NightForecast::hourly(50.1, 5.5);

    Livewire::actingAs($user)
        ->test(LocationTable::class)
        ->assertSee('Mon 9 Nov: Clear and dark · Clouds 0%', false);
});

it('shows the forecast of the nights of a starparty and puts it in the reminder', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-08 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $organiser = forecastObserver();
    $party     = new StarParty([
        'user_id'     => $organiser->id,
        'name'        => 'November starparty',
        'description' => '<p>Bring a warm coat.</p>',
        'language'    => 'en',
        'visibility'  => StarParty::PUBLIC,
        'start_date'  => '2026-11-09',
        'end_date'    => '2026-11-10',
    ]);
    $party->useLocation(Location::find($organiser->stdlocation));
    $party->save();
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $organiser->id, 'status' => StarPartyParticipant::GOING]);

    expect(array_keys(NightForecast::forStarParty($party)))->toBe(['2026-11-09', '2026-11-10']);

    $this->get(route('star-parties.show', $party))
        ->assertOk()
        ->assertSee('Weather')
        ->assertSee('Clear and dark')
        ->assertSee('Open-Meteo.com');

    $this->artisan('star-parties:send-reminders --apply')->assertSuccessful();
    expect(Message::sole()->message)->toContain('Forecast: Clear and dark · Clouds 0%')
        ->and(Message::sole()->message)->toContain('Weather data by Open-Meteo.com');
});

it('leaves out the nights of a starparty beyond the forecast', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-08 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $organiser = forecastObserver();
    $party     = new StarParty([
        'user_id'    => $organiser->id, 'name' => 'Later', 'language' => 'en', 'visibility' => StarParty::PUBLIC,
        'start_date' => '2026-11-20', 'end_date' => '2026-11-21',
    ]);
    $party->useLocation(Location::find($organiser->stdlocation));

    expect(NightForecast::forStarParty($party))->toBeNull();
    Http::assertNothingSent();
});

it('sends one message for a clear, dark night', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0);
    $user = forecastObserver();

    $this->artisan('forecast:send-clear-night-alerts')->expectsOutput('Messages to send: 1')->assertSuccessful();
    expect(Message::count())->toBe(0);

    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 1')->assertSuccessful();
    $message = Message::sole();
    expect($message->receiver)->toBe($user->username)
        ->and($message->subject)->toBe('Clear night forecast at Ardennes field')
        ->and($message->message)->toContain('observing-lists/night?night=2026-11-09')
        ->and($user->fresh()->clear_night_alert_night->toDateString())->toBe('2026-11-09');

    Carbon::setTestNow(Carbon::parse('2026-11-09 15:00', 'Europe/Brussels'));
    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0');
});

it('sends no message for a short clear gap in a cloudy night', function () {
    // Only two hours before dawn on 10 November clear: clear_dark, but 83% cloud on average
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    $from  = Carbon::parse('2026-11-10 02:00', 'UTC')->getTimestamp();
    $until = Carbon::parse('2026-11-10 03:00', 'UTC')->getTimestamp();
    fakeForecast(fn (int $t) => $t >= $from && $t <= $until ? 0 : 100);
    forecastObserver();

    $night = forecastNight('2026-11-09');
    expect($night['verdict'])->toBe('clear_dark')
        ->and($night['clear_dark']['minutes'])->toBeLessThan(SendClearNightAlerts::ALERT_MIN_CLEAR_MINUTES)
        ->and(SendClearNightAlerts::worthAlert($night))->toBeFalse();

    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0')->assertSuccessful();
});

it('sends no message before noon, after dark, for a cloudy night or without the setting', function () {
    $clouds = Carbon::parse('2026-11-10 12:00', 'UTC')->getTimestamp();
    fakeForecast(fn (int $t) => $t < $clouds ? 0 : 100);
    forecastObserver();
    forecastObserver(['clear_night_alert' => false]);

    Carbon::setTestNow(Carbon::parse('2026-11-09 10:00', 'Europe/Brussels'));
    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0');

    Carbon::setTestNow(Carbon::parse('2026-11-09 21:00', 'Europe/Brussels'));
    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0');

    Carbon::setTestNow(Carbon::parse('2026-11-10 14:00', 'Europe/Brussels'));
    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0');

    expect(Message::count())->toBe(0);
});

it('opens the night list wizard on the night of the link', function () {
    $user = forecastObserver();

    Livewire::actingAs($user)
        ->withQueryParams(['night' => '2026-11-09'])
        ->test(NightListWizard::class)
        ->assertSet('night', '2026-11-09');

    Livewire::actingAs($user)
        ->withQueryParams(['night' => '2026-02-31'])
        ->test(NightListWizard::class)
        ->assertNotSet('night', '2026-02-31');
});

/**
 * The air cooling from 12 °C at 15:00 UTC on 9 November by half a degree an hour.
 */
function coolingAir(): callable
{
    $from = Carbon::parse('2026-11-09 15:00', 'UTC')->getTimestamp();

    return fn (int $t) => 12.0 - 0.5 * ($t - $from) / 3600;
}

function coolDownTelescope(User $user, string $type, float $aperture, string $name = 'Dobson'): Instrument
{
    $instrument = InstrumentFactory::new()->create([
        'user_id'            => $user->id, 'observer' => $user->username, 'name' => $name, 'aperture_mm' => $aperture,
        'instrument_type_id' => InstrumentType::firstOrCreate(['name' => $type])->id,
    ]);
    $user->forceFill(['stdtelescope' => $instrument->id])->save();

    return $instrument->fresh();
}

it('warns per hour from when the dew heater is needed and dew forms', function () {
    // The air at 10 °C; the dew point 8 °C from 22:00 UTC, humid from 01:00 UTC
    $heater = Carbon::parse('2026-11-09 22:00', 'UTC')->getTimestamp();
    $dew    = Carbon::parse('2026-11-10 01:00', 'UTC')->getTimestamp();
    fakeForecast(fn () => 0, dewPoint: fn (int $t) => $t < $heater ? 2.0 : 8.0, humidity: fn (int $t) => $t < $dew ? 80 : 95);

    $night = forecastNight('2026-11-09');
    $hours = collect($night['hours'])->keyBy('time');

    expect($night['heater_from'])->toBe('23:00')
        ->and($night['dew_from'])->toBe('02:00')
        ->and($night['dew'])->toBeTrue()
        ->and($hours['22:00']['heater'])->toBeFalse()
        ->and($hours['23:00']['heater'])->toBeTrue()
        ->and($hours['23:00']['dew'])->toBeFalse()
        ->and($hours['02:00']['dew'])->toBeTrue()
        ->and($hours['23:00']['temperature'])->toBe(10.0)
        ->and($hours['23:00']['dew_point'])->toBe(8.0)
        ->and(NightForecast::dewLabel($night))->toBe('Dew heater from 23:00, dew from 02:00')
        ->and(NightForecast::summary($night))->toContain('Dew heater from 23:00, dew from 02:00');
});

it('leaves out the dew under clouds', function () {
    fakeForecast(fn () => 100, dewPoint: fn () => 9.5, humidity: fn () => 98);

    $night = forecastNight('2026-11-09');

    expect($night['heater_from'])->toBeNull()
        ->and($night['dew'])->toBeFalse()
        ->and(NightForecast::dewLabel($night))->toBeNull();
});

it('tells when to put a mirror outside to cool down', function () {
    fakeForecast(fn () => 0, temperature: coolingAir());
    $user  = forecastObserver();
    $night = forecastNight('2026-11-09');

    $dobson = CoolDownAdvice::forNight($night, coolDownTelescope($user, 'Reflector', 305));
    $small  = CoolDownAdvice::forNight($night, coolDownTelescope($user, 'Reflector', 150, 'Small'));
    $sct    = CoolDownAdvice::forNight($night, coolDownTelescope($user, 'Schmidt Cassegrain', 280, 'SCT'));

    // Dark from 18:53: the 12" without a fan two hours before, with a fan an hour later
    expect($dobson['ready'])->toBe('18:53')
        ->and($dobson['minutes'])->toBeGreaterThan(90)->toBeLessThan(150)
        ->and($dobson['put_out_fan'])->toBeGreaterThan($dobson['put_out'])
        ->and($dobson['air_start'])->toBeGreaterThan($dobson['air_end'])
        ->and($small['minutes'])->toBeLessThan($dobson['minutes'])
        ->and($sct['minutes'])->toBeGreaterThan($dobson['minutes'])
        ->and($sct['put_out_fan'])->toBeNull()
        ->and(CoolDownAdvice::forNight($night, coolDownTelescope($user, 'Refractor', 120, 'Refractor')))->toBeNull()
        ->and(CoolDownAdvice::label($dobson, Instrument::where('name', 'Dobson')->sole()))
        ->toContain('Put the Dobson outside at '.$dobson['put_out'].' (with a fan behind the mirror at '.$dobson['put_out_fan'].') to have it cooled at 18:53');
});

it('says so when the air cools too fast for the mirror to be cooled in time', function () {
    // The air falls 3 °C an hour around the evening: a 16" without a fan never catches up
    fakeForecast(fn () => 0, temperature: fn (int $t) => max(0.0, 14.0 - 3.0 * ($t - Carbon::parse('2026-11-09 14:00', 'UTC')->getTimestamp()) / 3600));
    $user       = forecastObserver();
    $instrument = coolDownTelescope($user, 'Reflector', 406);

    $advice = CoolDownAdvice::forNight(forecastNight('2026-11-09'), $instrument);

    expect($advice['in_time'])->toBeFalse()
        ->and($advice['behind'])->toBeGreaterThan(CoolDownAdvice::TOLERANCE_C)
        ->and($advice['fan_in_time'])->toBeTrue()
        ->and(CoolDownAdvice::label($advice, $instrument))->toContain('Put the Dobson outside as early as you can: without a fan the mirror is still');
});

it('needs no cool-down when the air is as warm as the room', function () {
    fakeForecast(fn () => 0, temperature: fn () => 22.0);
    $user = forecastObserver();

    $advice = CoolDownAdvice::forNight(forecastNight('2026-11-09'), coolDownTelescope($user, 'Reflector', 305));

    expect($advice['put_out'])->toBeNull()
        ->and($advice['minutes'])->toBe(0)
        ->and($advice['lag'])->toEqual(0);
});

it('shows the cool-down of the standard telescope on the home page and in the clear night message', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
    fakeForecast(fn () => 0, temperature: coolingAir());
    $user = forecastObserver();
    coolDownTelescope($user, 'Reflector', 305);
    NightForecast::hourly(50.1, 5.5);

    $this->actingAs($user->fresh())->get('/')
        ->assertOk()
        ->assertSee('Put the Dobson outside at');

    $this->artisan('forecast:send-clear-night-alerts --apply')->assertSuccessful();
    expect(Message::sole()->message)->toContain('Put the Dobson outside at');
});
