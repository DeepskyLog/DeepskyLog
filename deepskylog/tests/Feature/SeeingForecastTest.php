<?php

use App\Models\User;
use App\Services\TonightsBest;
use App\Services\Weather\NightForecast;
use App\Services\Weather\SeeingForecast;
use Carbon\Carbon;
use Database\Factories\InstrumentFactory;
use Database\Factories\LocationFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

// The seeing forecast of 7Timer, in the forecast of the coming nights and in
// Tonight's best, which adds close double stars on a steady night without
// pushing out the deep-sky objects. The night of 2026-11-09 has no Moon.

const SF_NIGHT = '2026-11-09';

beforeEach(function () {
    config(['services.seven_timer.enabled' => true]);
});

/**
 * Fakes 7Timer: each request gets the next of the answers, the seeing index
 * $seeing(timestamp) every 3 hours for 72 hours from noon (UT) of the night,
 * or an HTTP status.
 */
function fakeSeeing(callable|int ...$answers): void
{
    $init     = Carbon::parse(SF_NIGHT.' 12:00', 'UTC');
    $sequence = Http::fakeSequence('www.7timer.info/*');
    foreach ($answers as $seeing) {
        if (is_int($seeing)) {
            $sequence->push('', $seeing);

            continue;
        }
        $series = array_map(fn (int $hours) => [
            'timepoint' => $hours, 'cloudcover' => 1, 'seeing' => $seeing($init->getTimestamp() + 3600 * $hours), 'transparency' => 3,
        ], range(3, 72, 3));
        // 7Timer sends its JSON as text/plain
        $sequence->push(json_encode(['product' => 'astro', 'init' => $init->format('YmdH'), 'dataseries' => $series]), 200, ['Content-Type' => 'text/plain']);
    }
}

function sfObject(string $name, float $ra, float $decl, string $type, int $observations, float $diam, float $companion = 13.0): void
{
    DB::table('objects')->insert([
        'name'          => $name, 'slug' => Str::slug($name), 'type' => $type, 'con' => 'AND',
        'ra'            => $ra, 'decl' => $decl, 'mag' => 7.0, 'subr' => $companion, 'pa' => 0, 'urano' => 0,
        'urano_new'     => 0, 'sky' => 0, 'millenium' => '', 'diam1' => $diam, 'diam2' => $type === 'DS' ? 0 : $diam * 2 / 3,
        'datasource'    => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB'       => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP'         => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    DB::table('object_observation_stats')->insert(['object_name' => $name, 'observations' => $observations]);
}

/**
 * An observer with a 300 mm telescope in Brussels, galaxies with a contrast
 * reserve, close double stars (1″–2.5″, a pair the telescope does not split)
 * and a wide one, all well placed in the evening.
 */
function sfObserver(): User
{
    DB::table('deepskytypes')->insertOrIgnore([['code' => 'GALXY', 'name' => 'Galaxy'], ['code' => 'DS', 'name' => 'Double star']]);
    $user       = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'aperture_mm' => 300, 'focal_length_mm' => 1500, 'fixedMagnification' => null]);
    $location   = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username, 'latitude' => 50.85, 'longitude' => 4.35,
        'timezone'                                          => 'Europe/Brussels', 'skyBackground' => 21.5]);
    $user->forceFill(['stdtelescope' => $instrument->id, 'stdlocation' => $location->id])->save();

    foreach (range(1, 6) as $i) {
        sfObject("Galaxy {$i}", 0.5 + $i * 0.3, 40.0, 'GALXY', 1000 - $i, 300.0);
        DB::table('user_object_metrics')->insert(['user_id' => $user->id, 'instrument_id' => $instrument->id, 'location_id' => $location->id,
            'lens_id'                                       => null, 'object_name' => "Galaxy {$i}", 'contrast_reserve' => 1.2, 'created_at' => now(), 'updated_at' => now()]);
    }
    foreach ([1.0, 1.5, 2.0, 2.5] as $i => $separation) {
        sfObject("Close Double {$i}", 1.0 + $i * 0.4, 38.0, 'DS', 500 - $i, $separation, 7.5);
    }
    sfObject('Too Close Double', 1.2, 38.0, 'DS', 900, 0.2, 7.5);
    sfObject('Wide Double', 1.4, 38.0, 'DS', 2000, 20.0, 8.0);

    return $user->fresh();
}

function sfDeepsky(array $result): array
{
    return collect($result['objects'])->where('kind', 'deepsky')->where('type_code', '<>', 'DS')->pluck('name')->sort()->values()->all();
}

it('averages the seeing of the dark hours and classes it', function () {
    // in Brussels steady; at the next cell turbulent until midnight (UT), steady after
    $midnight = Carbon::parse('2026-11-10 00:00', 'UTC')->getTimestamp();
    fakeSeeing(fn () => 3, fn (int $t) => $t <= $midnight ? 8 : 2);

    $steady = SeeingForecast::forNight(50.85, 4.35, SF_NIGHT, 'Europe/Brussels');
    expect($steady)->toBe(['index' => 3.0, 'class' => 'steady', 'arcsec' => '0.75–1″']);

    // weighed by the hours (each point stands for 1.5 hours before and after it)
    $mixed = SeeingForecast::forNight(50.85, 4.65, SF_NIGHT, 'Europe/Brussels');
    expect($mixed['index'])->toBe(6.1)
        ->and($mixed['class'])->toBe('average')
        ->and(SeeingForecast::classOf(4.5))->toBe('average')
        // beyond the 72 hours of the forecast
        ->and(SeeingForecast::forNight(50.85, 4.35, '2026-11-14', 'Europe/Brussels'))->toBeNull()
        ->and(SeeingForecast::arcsec(1))->toBe('< 0.5″')
        ->and(SeeingForecast::arcsec(8))->toBe('> 2.5″')
        ->and(SeeingForecast::classOf(7.0))->toBe('poor');
});

it('asks 7Timer once per cell and pauses after a failure', function () {
    fakeSeeing(fn () => 5, 503);
    expect(SeeingForecast::points(50.85, 4.35)['seeing'])->toHaveCount(24);
    SeeingForecast::points(50.8, 4.3);
    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request['lat'] == 50.75 && $request['lon'] == 4.25 && $request['output'] === 'json');

    // 7Timer is down: no more requests for a while
    expect(SeeingForecast::points(40.0, 10.0))->toBeNull()
        ->and(SeeingForecast::points(45.0, 12.0))->toBeNull();
    Http::assertSentCount(2);
});

it('does not ask 7Timer when it is turned off or the page must not wait', function () {
    fakeSeeing(fn () => 5);
    expect(SeeingForecast::points(50.85, 4.35, fetch: false))->toBeNull();
    config(['services.seven_timer.enabled' => false]);
    expect(SeeingForecast::points(50.85, 4.35))->toBeNull();
    Http::assertNothingSent();
});

it('shows the seeing in the forecast of a night', function () {
    config(['services.open_meteo.enabled' => true]);
    fakeSeeing(fn () => 7);
    $start = Carbon::parse(SF_NIGHT.' 00:00', 'UTC')->getTimestamp();
    $time  = array_map(fn (int $i) => $start + 3600 * $i, range(0, 7 * 24 - 1));
    Http::fake(['api.open-meteo.com/*' => Http::response(['hourly' => [
        'time'           => $time, 'cloud_cover' => array_fill(0, count($time), 0), 'relative_humidity_2m' => array_fill(0, count($time), 50),
        'wind_speed_10m' => array_fill(0, count($time), 5),
    ]])]);

    $night = NightForecast::forNights(50.85, 4.35, 50, 'Europe/Brussels', [SF_NIGHT])[SF_NIGHT];

    // the seeing does not change the verdict of the clouds
    expect($night['verdict'])->toBe('clear_dark')
        ->and($night['seeing']['class'])->toBe('poor')
        ->and($night['hours'][0]['seeing'])->toBe(7)
        ->and(NightForecast::summary($night))->toContain('Poor seeing (2–2.5″)');

    $this->view('components.night-forecast-nights', ['nights' => [$night], 'listLinks' => false])
        ->assertSee('Poor seeing')
        ->assertSee('7Timer!');
});

it('adds close double stars on a steady night, on top of the deep-sky objects', function () {
    Queue::fake();
    $user    = sfObserver();
    $options = ['night' => SF_NIGHT, 'count' => 5, 'seen' => 'any'];

    $without = TonightsBest::for($user, $options);
    expect($without['seeing'])->toBeNull()
        ->and(collect($without['objects'])->where('type_code', 'DS'))->toBeEmpty();

    fakeSeeing(fn () => 3);
    SeeingForecast::points(50.85, 4.35);
    $steady  = TonightsBest::for($user, $options);
    $doubles = collect($steady['objects'])->where('type_code', 'DS');

    // one per five deep-sky objects: the closest pairs the telescope splits, not the wide one
    expect($steady['seeing']['class'])->toBe('steady')
        ->and(sfDeepsky($steady))->toBe(sfDeepsky($without))->not->toBeEmpty()
        ->and($doubles)->toHaveCount(1)
        ->and($steady['steady_doubles'])->toBe(1)
        ->and($doubles->first()['name'])->toStartWith('Close Double')
        ->and($doubles->first()['seeing'])->toBe('steady')
        ->and(collect($steady['objects'])->where('kind', 'planet')->pluck('seeing')->unique()->all())->toBe(['steady'])
        ->and(TonightsBest::listDescription($steady))->toContain('Steady seeing');

    // more for a longer list, never the pair below the telescope's limit
    $long = TonightsBest::for($user, [...$options, 'count' => 20]);
    expect(collect($long['objects'])->where('type_code', 'DS')->pluck('name')->sort()->values()->all())
        ->toBe(['Close Double 0', 'Close Double 1', 'Close Double 2', 'Close Double 3']);

    // not when the kinds were chosen
    $galaxies = TonightsBest::for($user, [...$options, 'types' => ['GALXY']]);
    expect(collect($galaxies['objects'])->where('type_code', 'DS'))->toBeEmpty();

    $this->actingAs($user)->get('/')->assertOk();
});

it('leaves out the close double stars chosen on a poor night', function () {
    Queue::fake();
    $user    = sfObserver();
    $options = ['night' => SF_NIGHT, 'count' => 10, 'seen' => 'any', 'types' => ['DS'], 'planets' => true];

    $average = collect(TonightsBest::for($user, $options)['objects']);
    expect($average->where('type_code', 'DS')->pluck('name'))->toContain('Wide Double', 'Close Double 0');

    fakeSeeing(fn () => 8);
    SeeingForecast::points(50.85, 4.35);
    $poor = collect(TonightsBest::for($user, $options)['objects']);
    expect($poor->where('type_code', 'DS')->pluck('name')->all())->toBe(['Wide Double'])
        ->and($poor->where('kind', 'planet')->pluck('seeing')->unique()->all())->toBe(['poor']);
});
