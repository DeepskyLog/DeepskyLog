<?php

use App\Models\Location;
use App\Models\Message;
use App\Models\User;
use App\Services\Weather\NightForecast;
use App\Services\Weather\SiteComparison;
use Database\Factories\LocationFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

// Which of my sites tonight? The observer's locations compared for the
// coming nights, and the clear night alert naming the best of them. The
// night of 2026-11-09 has no Moon in the Ardennes.

beforeEach(function () {
    config(['services.open_meteo.enabled' => true]);
    Carbon::setTestNow(Carbon::parse('2026-11-09 14:00', 'Europe/Brussels'));
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * Fakes Open-Meteo: every hour of seven days from 2026-11-08 clear at the
 * latitudes of $clear, overcast elsewhere.
 *
 * @param  list<float>  $clear
 */
function fakeSiteForecasts(array $clear): void
{
    $start = Carbon::parse('2026-11-08 00:00', 'UTC')->getTimestamp();
    $time  = array_map(fn (int $i) => $start + 3600 * $i, range(0, 7 * 24 - 1));

    Http::fake(['api.open-meteo.com/*' => function (Request $request) use ($time, $clear) {
        $cover = in_array(round((float) $request['latitude'], 1), $clear, true) ? 0 : 100;

        return Http::response(['hourly' => [
            'time'                 => $time,
            'cloud_cover'          => array_fill(0, count($time), $cover),
            'cloud_cover_low'      => array_fill(0, count($time), $cover),
            'cloud_cover_mid'      => array_fill(0, count($time), 0),
            'cloud_cover_high'     => array_fill(0, count($time), 0),
            'relative_humidity_2m' => array_fill(0, count($time), 70),
            'wind_speed_10m'       => array_fill(0, count($time), 10),
        ]]);
    }]);
}

function siteObserver(): User
{
    $user = User::factory()->create(['clear_night_alert' => true, 'language' => 'en']);
    $home = observerSite($user, 'Backyard', 50.9, 4.7, 18.5);
    $user->forceFill(['stdlocation' => $home->id])->save();

    return $user->fresh();
}

function observerSite(User $user, string $name, float $latitude, float $longitude, float $sqm, bool $active = true): Location
{
    return LocationFactory::new()->create([
        'user_id'           => $user->id,
        'observer'          => $user->username,
        'name'              => $name,
        'latitude'          => $latitude,
        'longitude'         => $longitude,
        'timezone'          => 'Europe/Brussels',
        'elevation'         => 100,
        'skyBackground'     => $sqm,
        'limitingMagnitude' => -999,
        'active'            => $active,
    ]);
}

it('scores a darker sky higher for deep-sky objects and the seeing for planets', function () {
    $night = ['verdict' => 'clear_dark', 'clear_minutes' => 480, 'clear_dark_minutes' => 480, 'seeing' => ['class' => 'poor']];

    expect(SiteComparison::score($night, 21.75))->toBe(100)
        ->and(SiteComparison::score($night, 19.875))->toBe(50)
        ->and(SiteComparison::score($night, 16.0))->toBe(10)
        ->and(SiteComparison::score($night, 21.75, SiteComparison::MODE_PLANETS))->toBe(40)
        // Three clear hours without and three with a bright Moon: 3 + 0.3 × 3 of 6
        ->and(SiteComparison::score(['verdict' => 'clear_moon', 'clear_minutes' => 360, 'clear_dark_minutes' => 180], 21.75))->toBe(65)
        ->and(SiteComparison::score(['verdict' => 'no_dark'], 21.75))->toBe(0)
        ->and(SiteComparison::score(['verdict' => 'unknown'], 21.75))->toBeNull();
});

it('takes the sky of a site from the observations, else the location', function () {
    $user = siteObserver();
    $site = Location::find($user->stdlocation);

    expect(SiteComparison::sky($site))->toBe(['sqm' => 18.5, 'source' => 'location']);

    $site->forceFill(['skyBackground' => -999])->save();
    expect(SiteComparison::sky($site->fresh())['source'])->toBe('unknown');
});

it('ranks the clear, dark site first on the page', function () {
    fakeSiteForecasts([50.1]);
    $user = siteObserver();
    observerSite($user, 'Ardennes field', 50.1, 5.5, 21.3);
    observerSite($user, 'Old site', 50.5, 5.0, 21.0, active: false);
    // The page fetches missing forecasts after the response
    NightForecast::hourly(50.9, 4.7);
    NightForecast::hourly(50.1, 5.5);

    $this->actingAs($user)->get(route('location.best-site', ['night' => '2026-11-09']))
        ->assertOk()
        ->assertSee('Which of my sites tonight?')
        ->assertSeeInOrder(['Best site:', 'Ardennes field', 'Backyard'], false)
        ->assertDontSee('Old site');
});

it('says which forecasts are still being fetched', function () {
    fakeSiteForecasts([]);
    $user = siteObserver();

    $this->actingAs($user)->get(route('location.best-site'))
        ->assertOk()
        ->assertSee('The forecast of 1 site is being fetched.');
});

it('names the best clear site within reach in the clear night alert', function () {
    fakeSiteForecasts([50.1, 45.0]);
    $user = siteObserver();
    observerSite($user, 'Ardennes field', 50.1, 5.5, 21.3);
    observerSite($user, 'Alps', 45.0, 6.5, 21.8);

    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 1')->assertSuccessful();

    $message = Message::sole();
    expect($message->subject)->toBe('Clear night forecast at Ardennes field')
        ->and($message->message)->toContain('At your standard location Backyard: Cloudy')
        ->and($message->message)->not->toContain('Alps')
        ->and($message->message)->toContain('location/best-site?night=2026-11-09');
});

it('sends no alert when no site within reach is clear', function () {
    fakeSiteForecasts([45.0]);
    $user = siteObserver();
    observerSite($user, 'Alps', 45.0, 6.5, 21.8);

    $this->artisan('forecast:send-clear-night-alerts --apply')->expectsOutput('Messages sent: 0');
});
