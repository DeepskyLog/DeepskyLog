<?php

use App\Charts\SkyQualityHistoryChart;
use App\Models\Location;
use App\Models\User;
use App\Services\SkyBrightnessMap;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

function skyMapLocation(float $latitude, float $longitude, array $attributes = []): Location
{
    return LocationFactory::new()->create(['observer' => '', 'latitude' => $latitude, 'longitude' => $longitude, 'hidden' => false, ...$attributes]);
}

function skyMapObservation(Location $location, string $date, ?float $sqm, ?float $nelm = null, ?string $observer = null): void
{
    DB::table('observations')->insert(['observer_username' => $observer ?? $location->user->username, 'observed_on' => $date, 'location_id' => $location->id,
        'sqm' => $sqm, 'limiting_magnitude' => $nelm,
        'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 31', 'target_name' => 'M 31',
        'has_drawing' => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
}

it('gives a cell the median of its observers, each weighing the same', function () {
    // Three observers in the cell 50.75–51.00° N, 4.50–4.75° E
    $first = skyMapLocation(50.80, 4.55);
    foreach (['2020-01-01', '2020-01-02', '2020-01-03', '2020-01-04'] as $date) {
        skyMapObservation($first, $date, 18.0);
    }
    skyMapObservation(skyMapLocation(50.95, 4.70), '2020-02-01', 20.0);
    $third = skyMapLocation(50.76, 4.74);
    skyMapObservation($third, '2021-02-01', 21.0, 6.0);
    // Two observations of one night count once
    skyMapObservation($third, '2021-02-01', 21.4, null);

    $cells = SkyBrightnessMap::compute();

    expect($cells)->toHaveCount(1)
        ->and($cells[0])->toMatchArray([
            'south' => 50.75, 'west' => 4.5, 'north' => 51.0, 'east' => 4.75,
            // The median of 18.0, 20.0 and 21.2, not of the six nights
            'sqm' => 20.0,
            'nelm' => 6.0,
            'observers' => 3,
            'nights' => 6,
        ])
        ->and($cells[0]['years'])->toBe([
            ['year' => 2020, 'sqm' => 19.0, 'nelm' => null, 'sky' => 19.0, 'sky_estimated' => false],
            ['year' => 2021, 'sqm' => 21.2, 'nelm' => 6.0, 'sky' => 21.2, 'sky_estimated' => false],
        ]);
});

it('combines the SQM and the NELM converted with the fstOffset of the observer', function () {
    // An SQM with a NELM the same night: the SQM counts
    $measured = skyMapLocation(20.1, 20.1);
    skyMapObservation($measured, '2020-01-01', 20.0, 5.0);
    // Only NELMs, of an observer with an fstOffset
    $owner = User::factory()->create(['fstOffset' => 0.3]);
    $visual = skyMapLocation(20.2, 20.2, ['user_id' => $owner->id]);
    skyMapObservation($visual, '2020-01-02', null, 6.0);
    skyMapObservation($visual, '2021-01-02', null, 6.2);
    skyMapObservation(skyMapLocation(20.15, 20.15), '2021-01-03', null, 6.8);

    $cell = SkyBrightnessMap::compute()[0];
    // The median of the three observers: 20.0, the mean of the visual observer's two nights, the third's NELM
    $visualSky = round((round(SkyQualityHistoryChart::sqmOfNelm(6.0, 0.3), 2) + round(SkyQualityHistoryChart::sqmOfNelm(6.2, 0.3), 2)) / 2, 2);
    $observerSkies = [20.0, $visualSky, round(SkyQualityHistoryChart::sqmOfNelm(6.8), 2)];
    sort($observerSkies);

    expect($cell['sqm'])->toBe(20.0)
        ->and($visualSky)->toBeGreaterThan(21.0)
        // The visual observer is the middle one, with their fstOffset
        ->and($cell['sky'])->toBe($visualSky)
        ->and($observerSkies[1])->toBe($visualSky)
        ->and($cell['sky_nelm_nights'])->toBe(3)
        ->and($cell['sky_estimated'])->toBeTrue()
        ->and($cell['years'][0])->toMatchArray(['year' => 2020, 'sky_estimated' => false])
        ->and($cell['years'][1])->toMatchArray(['year' => 2021, 'sky_estimated' => true]);
});

it('converts a NELM to at most the sky brightness of a natural sky', function () {
    $location = skyMapLocation(30.1, 30.1);
    skyMapObservation($location, '2020-01-01', null, 7.2);
    // A measured SQM is not capped
    skyMapObservation($location, '2021-01-01', 22.3, 7.2);

    expect(SkyQualityHistoryChart::sqmOfNelm(7.2))->toBeGreaterThan(SkyBrightnessMap::MAX_CONVERTED_SQM)
        ->and(array_column(SkyBrightnessMap::compute()[0]['years'], 'sky'))->toBe([22.0, 22.3]);
});

it('shows the cell of one observer, without hidden locations or the values of others at a location', function () {
    $location = skyMapLocation(10.1, 10.1);
    skyMapObservation($location, '2020-01-01', 20.0);
    // Observations of others at someone's location are errors of old data
    $other = User::factory()->create();
    skyMapObservation($location, '2020-01-02', 18.0, null, $other->username);
    skyMapObservation(skyMapLocation(10.15, 10.15, ['hidden' => true]), '2020-01-01', 18.0);
    // Placeholders
    skyMapObservation(skyMapLocation(10.12, 10.12), '2020-01-01', 0.0, 0.0);

    $cells = SkyBrightnessMap::compute();

    expect($cells)->toHaveCount(1)
        ->and($cells[0])->toMatchArray(['south' => 10.0, 'west' => 10.0, 'sqm' => 20.0, 'nelm' => null, 'observers' => 1, 'nights' => 1]);
});

it('limits the map to the recent years', function () {
    foreach ([[1.1, '2005-01-01'], [1.2, '2006-01-01'], [1.15, now()->toDateString()]] as [$latitude, $date]) {
        skyMapObservation(skyMapLocation($latitude, 1.1), $date, 21.0);
    }

    expect(SkyBrightnessMap::compute())->toHaveCount(1)
        ->and(SkyBrightnessMap::compute()[0]['observers'])->toBe(3)
        ->and(SkyBrightnessMap::compute(SkyBrightnessMap::sinceYear('recent'))[0]['observers'])->toBe(1);
});

it('shows the cells on the map, never the locations', function () {
    $locations = [skyMapLocation(50.8123, 4.5511), skyMapLocation(50.9, 4.6), skyMapLocation(50.85, 4.7)];
    foreach ($locations as $location) {
        skyMapObservation($location, '2020-01-01', 20.5);
    }

    $this->get(route('sky-brightness-map', ['quantity' => 'sqm']))
        ->assertOk()
        ->assertSee('Sky brightness map')
        ->assertSee('"south":50.75', false)
        ->assertDontSee('50.8123')
        ->assertDontSee($locations[0]->name);
});
