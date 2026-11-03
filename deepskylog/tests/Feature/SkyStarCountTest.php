<?php

use App\Livewire\SkyStarCount;
use App\Models\SkyMeasurement;
use App\Models\User;
use App\Services\AtmosphericExtinction;
use App\Services\LocationSkyHistory;
use App\Services\News\SkyPositions;
use App\Services\StarCountAreas;
use Database\Factories\LocationFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// Measure your sky: the naked-eye limiting magnitude from a count of the
// stars inside a figure of bright stars (StarCountAreas, Livewire SkyStarCount).

function sscStar(float $ra, float $dec, float $vmag, ?string $bayer = null, ?string $constellation = null): void
{
    static $n = 0;
    $n++;
    DB::table('stars')->insert([
        'source_key'         => sha1('ssc' . $n), 'source_table' => 'stars8', 'bayer' => $bayer, 'constellation_code' => $constellation,
        'ra_degrees'         => $ra, 'ra_hours' => $ra / 15, 'decl_degrees' => $dec, 'vmag' => $vmag, 'vmag_int' => (int) round($vmag * 100),
    ]);
}

/**
 * The body of Orion: its four corner stars, five stars inside (one of them
 * twice, as the table holds a TYC row next to a named star), one outside.
 */
function sscOrion(): void
{
    sscStar(88.79, 7.41, 0.45, 'Alp', 'Ori');
    sscStar(81.28, 6.35, 1.64, 'Gam', 'Ori');
    sscStar(86.94, -9.67, 2.06, 'Kap', 'Ori');
    sscStar(78.63, -8.20, 0.13, 'Bet', 'Ori');

    sscStar(84.05, -1.20, 1.69);
    sscStar(83.82, -5.39, 4.0);
    sscStar(83.82 + 0.005, -5.39, 4.1); // the same star in another catalogue
    sscStar(84.5, 2.0, 5.5);
    sscStar(83.0, -3.0, 6.1);
    sscStar(85.0, 0.0, 6.7);
    sscStar(95.0, 0.0, 3.0); // outside
}

/** The moment Orion stands highest at the longitude. */
function sscTransit(float $latitude, float $longitude): int
{
    $start = Carbon::parse('2026-01-15 00:00', 'UTC')->getTimestamp();
    $best = $start;
    for ($t = $start; $t < $start + 86400; $t += 300) {
        if (SkyPositions::altitude(84.0, 0.0, $latitude, $longitude, $t) > SkyPositions::altitude(84.0, 0.0, $latitude, $longitude, $best)) {
            $best = $t;
        }
    }

    return $best;
}

function sscLocation(User $user, array $attributes = [])
{
    return LocationFactory::new()->create($attributes + [
        'user_id'  => $user->id, 'observer' => $user->username, 'name' => 'Dark site', 'latitude' => 10.0, 'longitude' => 0.0,
        'timezone' => 'UTC', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'active' => true,
    ]);
}

it('reads the stars inside a figure, without its corners, duplicates and stars outside', function () {
    sscOrion();

    $area = StarCountAreas::area('orion');

    expect($area['magnitudes'])->toBe([1.69, 4.0, 5.5, 6.1, 6.7])
        ->and(array_column($area['corners'], 'label'))->toBe(['α Ori', 'γ Ori', 'κ Ori', 'β Ori'])
        ->and(StarCountAreas::area('pegasus'))->toBeNull();
});

it('puts the limit between the faintest star seen and the next one', function () {
    sscOrion();
    $area = StarCountAreas::area('orion');

    expect(StarCountAreas::limitingMagnitude($area, 0))->toBeNull()
        ->and(StarCountAreas::limitingMagnitude($area, 3))->toBe(5.8)
        ->and(StarCountAreas::limitingMagnitude($area, 5))->toBe(6.8)
        ->and(StarCountAreas::limitingMagnitude($area, 50))->toBe(6.8);
});

it('adds the extinction at the altitude of the figure to give the limit at the zenith', function () {
    sscOrion();
    $user = User::factory()->create();
    $location = sscLocation($user, ['latitude' => 35.0]);
    $timestamp = sscTransit(35.0, 0.0);

    $result = StarCountAreas::measure(StarCountAreas::area('orion'), 4, $location, $timestamp);

    expect($result['altitude'])->toEqualWithDelta(55.0, 1.0)
        ->and($result['area_magnitude'])->toBe(6.4)
        ->and($result['extinction'])->toBe(round(AtmosphericExtinction::magnitudes($result['altitude']), 2))
        ->and($result['nelm'])->toBe(round(6.4 + AtmosphericExtinction::magnitudes($result['altitude']), 1))
        ->and($result['reliable'])->toBeTrue();
});

it('offers only the figures high enough at the moment, the highest first', function () {
    sscOrion();
    $user = User::factory()->create();
    $location = sscLocation($user);

    expect(array_column(StarCountAreas::visible($location, sscTransit(10.0, 0.0)), 'key'))->toBe(['orion'])
        ->and(StarCountAreas::visible($location, sscTransit(10.0, 0.0) + 12 * 3600))->toBe([]);
});

it('draws the figure with its corner stars but not the stars inside', function () {
    sscOrion();
    $user = User::factory()->create();
    $svg = StarCountAreas::chart(StarCountAreas::area('orion'), sscLocation($user), sscTransit(10.0, 0.0));

    expect($svg)->toStartWith('<svg')
        ->toContain('α Ori')
        ->toContain('<polygon')
        ->and(substr_count($svg, '<circle'))->toBe(5); // four corners and the star outside
});

it('saves a count, also as the limiting magnitude of the location, and counts it in the sky history', function () {
    sscOrion();
    $user = User::factory()->create(['fstOffset' => 0.2]);
    $location = sscLocation($user);
    $moment = Carbon::createFromTimestampUTC(sscTransit(10.0, 0.0));

    Livewire::actingAs($user)->test(SkyStarCount::class, ['locationId' => $location->id, 'moment' => $moment->format('Y-m-d\TH:i')])
        ->assertSee('Body of Orion')
        ->set('count', 4)
        ->assertSee('NELM')
        ->set('applyToLocation', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Saved.');

    $measurement = SkyMeasurement::sole();
    expect($measurement->area)->toBe('orion')
        ->and($measurement->star_count)->toBe(4)
        ->and($measurement->area_magnitude)->toBe(6.4)
        ->and($measurement->nelm)->toBeGreaterThanOrEqual(6.4);

    // the location stores the NELM without the observer's fstOffset
    $location->refresh();
    expect((float) $location->limitingMagnitude)->toEqualWithDelta($measurement->nelm + 0.2, 0.001)
        ->and((float) $location->getNelm(0.2))->toEqualWithDelta($measurement->nelm, 0.001)
        ->and((float) $location->skyBackground)->toBeGreaterThan(20.0);

    // the count replaces the NELM the observations of that night give
    DB::table('observations')->insert(['observer_username' => $user->username, 'location_id' => $location->id, 'observed_on' => $moment->toDateString(),
        'limiting_magnitude' => 5.0, 'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 42', 'target_name' => 'M 42',
        'has_drawing' => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
    $night = LocationSkyHistory::forLocation($location)->nights[0];
    expect($night['nelm'])->toBe(round($measurement->nelm, 1))
        ->and($night['measured'])->toBeTrue();
});

it('refuses a count of nothing and the locations of others', function () {
    sscOrion();
    $user = User::factory()->create();
    $location = sscLocation($user);
    $moment = Carbon::createFromTimestampUTC(sscTransit(10.0, 0.0))->format('Y-m-d\TH:i');

    Livewire::actingAs($user)->test(SkyStarCount::class, ['locationId' => $location->id, 'moment' => $moment])
        ->set('count', 0)
        ->call('save')
        ->assertHasErrors('count');

    $other = User::factory()->create();
    $mine = sscLocation($other, ['name' => 'Mine']);
    Livewire::actingAs($other)->test(SkyStarCount::class, ['locationId' => $location->id, 'moment' => $moment])
        ->assertSet('locationId', $mine->id);

    expect(SkyMeasurement::count())->toBe(0);
});

it('lets the observer delete their own counts only', function () {
    sscOrion();
    $user = User::factory()->create();
    $location = sscLocation($user);
    $measurement = SkyMeasurement::create(['user_id' => $user->id, 'location_id' => $location->id, 'measured_at' => now(), 'area' => 'orion',
        'star_count' => 4, 'area_magnitude' => 6.4, 'altitude' => 60, 'nelm' => 6.5]);

    Livewire::actingAs(User::factory()->create())->test(SkyStarCount::class)->call('deleteMeasurement', $measurement->id);
    expect(SkyMeasurement::count())->toBe(1);

    Livewire::actingAs($user)->test(SkyStarCount::class, ['locationId' => $location->id])->call('deleteMeasurement', $measurement->id);
    expect(SkyMeasurement::count())->toBe(0);
});

it('opens the page', function () {
    $user = User::factory()->create();
    sscLocation($user);

    $this->actingAs($user)->get(route('sky-quality.star-count'))->assertOk()->assertSee('Measure your sky');
});
