<?php

use App\Charts\SkyQualityHistoryChart;
use App\Models\Location;
use App\Services\LocationSkyHistory;
use Database\Factories\LocationFactory;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Support\Facades\DB;

function skyHistoryObservation(Location $location, string $date, ?float $sqm, ?float $nelm, ?string $observer = null): void
{
    DB::table('observations')->insert(['observer_username' => $observer ?? $location->user->username, 'observed_on' => $date, 'location_id' => $location->id,
        'sqm'                                              => $sqm, 'limiting_magnitude' => $nelm,
        'target_type'                                      => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 31', 'target_name' => 'M 31',
        'has_drawing'                                      => false, 'language' => 'en', 'legacy_source' => 'deepsky', 'created_at' => now(), 'updated_at' => now()]);
}

it('takes the median of each night and of the nights of each year', function () {
    $location = LocationFactory::new()->create(['observer' => '', 'latitude' => 50.88, 'longitude' => 4.70]);
    // Three observations in one night count once
    skyHistoryObservation($location, '2020-03-01', 20.0, 5.0);
    skyHistoryObservation($location, '2020-03-01', 20.4, null);
    skyHistoryObservation($location, '2020-03-01', 21.0, 5.6);
    skyHistoryObservation($location, '2020-04-01', 19.0, null);
    skyHistoryObservation($location, '2020-05-01', 18.0, null);
    skyHistoryObservation($location, '2022-01-10', null, 6.1);

    $history = LocationSkyHistory::forLocation($location);

    expect($history->nights)->toBe([
        ['date' => '2020-03-01', 'sqm' => 20.4, 'nelm' => 5.3],
        ['date' => '2020-04-01', 'sqm' => 19.0, 'nelm' => null],
        ['date' => '2020-05-01', 'sqm' => 18.0, 'nelm' => null],
        ['date' => '2022-01-10', 'sqm' => null, 'nelm' => 6.1],
    ])->and($history->years)->toBe([
        2020 => ['sqm' => 19.0, 'nelm' => 5.3, 'nights' => 3],
        2022 => ['sqm' => null, 'nelm' => 6.1, 'nights' => 1],
    ]);
});

it('leaves out the observations of other observers at the location', function () {
    $location = LocationFactory::new()->create(['observer' => '']);
    skyHistoryObservation($location, '2020-03-01', 20.4, null);
    skyHistoryObservation($location, '2020-03-02', 18.0, null, 'someone else');

    expect(array_column(LocationSkyHistory::forLocation($location)->nights, 'date'))->toBe(['2020-03-01']);
});

it('leaves out the placeholders and implausible values', function () {
    $location = LocationFactory::new()->create(['observer' => '']);
    skyHistoryObservation($location, '2020-03-01', 0.0, 0.0);
    skyHistoryObservation($location, '2020-03-02', 24.5, 9.0);
    skyHistoryObservation($location, '2020-03-03', null, null);

    expect(LocationSkyHistory::forLocation($location)->isEmpty())->toBeTrue();

    skyHistoryObservation($location, '2020-03-04', 0.0, 5.5);

    $history = LocationSkyHistory::forLocation($location);
    expect($history->nights)->toBe([['date' => '2020-03-04', 'sqm' => null, 'nelm' => 5.5]])
        ->and($history->has('sqm'))->toBeFalse()
        ->and($history->has('nelm'))->toBeTrue();
});

it('draws the nights and the yearly medians on one scale of sky brightness', function () {
    $location = LocationFactory::new()->create(['observer' => '']);
    skyHistoryObservation($location, '2019-08-01', 20.5, null);
    skyHistoryObservation($location, '2020-08-01', 20.3, null);
    skyHistoryObservation($location, '2020-08-02', null, 5.8);

    $options = (new SkyQualityHistoryChart)->build('Home', LocationSkyHistory::forLocation($location))->getAdditionalOptions();

    expect(array_column($options['series'], 'name'))->toBe(['SQM', 'SQM, median per year', 'NELM'])
        ->and($options['series'][0]['data'][0])->toBe(['x' => strtotime('2019-08-01 UTC') * 1000, 'y' => 20.5, 'value' => 20.5])
        ->and($options['series'][1]['data'][1])->toBe(['x' => strtotime('2020-07-01 UTC') * 1000, 'y' => 20.3, 'value' => 20.3])
        // The NELM at the SQM of the same sky, with its own value for the tooltip
        ->and($options['series'][2]['data'][0]['y'])->toEqualWithDelta(Magnitude::nelmToSqm(5.8), 0.001)
        ->and($options['series'][2]['data'][0]['value'])->toBe(5.8)
        ->and(array_column($options['yaxis'], 'seriesName'))->toBe(['SQM', 'SQM', 'NELM'])
        ->and(array_column($options['yaxis'], 'nelmLabels'))->toBe([false, false, true])
        ->and($options['yaxis'][2]['opposite'])->toBeTrue()
        ->and($options['xaxis']['type'])->toBe('datetime');

    // Every axis has the same bounds, so a height is the same sky on both sides
    $bounds = array_map(fn ($axis) => [$axis['min'], $axis['max'], $axis['tickAmount']], $options['yaxis']);
    expect(array_unique($bounds, SORT_REGULAR))->toHaveCount(1)
        ->and($bounds[0])->toBe([20.0, 20.5, 1]);
});

it('places a NELM at the SQM of the same sky, also beyond the library\'s cap', function () {
    foreach ([3.0, 4.5, 5.5, 6.2] as $nelm) {
        expect(SkyQualityHistoryChart::sqmOfNelm($nelm))->toEqualWithDelta(Magnitude::nelmToSqm($nelm), 0.001)
            ->and(Magnitude::sqmToNelm(SkyQualityHistoryChart::sqmOfNelm($nelm)))->toEqualWithDelta($nelm, 0.001);
    }
    // An observer who sees 0.3 magnitudes fainter than average reaches the same NELM in a brighter sky
    expect(SkyQualityHistoryChart::sqmOfNelm(5.5, -0.3))->toBeLessThan(SkyQualityHistoryChart::sqmOfNelm(5.5))
        ->and(SkyQualityHistoryChart::sqmOfNelm(5.5, -0.3))->toEqualWithDelta(Magnitude::nelmToSqm(5.5, -0.3), 0.001)
        ->and(Magnitude::sqmToNelm(SkyQualityHistoryChart::sqmOfNelm(5.5, -0.3), -0.3))->toEqualWithDelta(5.5, 0.001);
    expect(SkyQualityHistoryChart::sqmOfNelm(6.7))->toBeGreaterThan(22.0)
        ->and(SkyQualityHistoryChart::sqmOfNelm(7.5))->toBe(LocationSkyHistory::SQM_RANGE[1]);
});

it('shows the sky quality history on the location page', function () {
    $location = LocationFactory::new()->create(['observer' => '', 'latitude' => 50.88, 'longitude' => 4.70, 'slug' => 'home']);
    skyHistoryObservation($location, '2020-03-01', 20.4, 5.3);

    $this->get('/location/'.$location->user->slug.'/home')
        ->assertOk()
        ->assertSee('Sky quality history')
        ->assertSee('1 night with a value');
});

it('shows no sky quality history without values', function () {
    $location = LocationFactory::new()->create(['observer' => '', 'latitude' => 50.88, 'longitude' => 4.70, 'slug' => 'home']);
    skyHistoryObservation($location, '2020-03-01', null, null);

    $this->get('/location/'.$location->user->slug.'/home')
        ->assertOk()
        ->assertDontSee('Sky quality history');
});
