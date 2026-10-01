<?php

use App\Models\UserObjectMetric;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

function skyObservation(?float $sqm, ?float $nelm): int
{
    return (int) DB::table('observations')->insertGetId([
        'observer_username' => 'testobserver',
        'target_name' => 'M 31',
        'observed_on' => '2009-05-11',
        'sqm' => $sqm,
        'limiting_magnitude' => $nelm,
        'created_at' => '2009-05-11 00:00:00',
        'updated_at' => '2009-05-11 00:00:00',
    ]);
}

function skyValues(int $id): array
{
    $row = DB::table('observations')->find($id);

    return [
        $row->sqm === null ? null : (float) $row->sqm,
        $row->limiting_magnitude === null ? null : (float) $row->limiting_magnitude,
    ];
}

it('changes nothing without --apply', function () {
    $id = skyObservation(4.9, null);

    $this->artisan('observations:repair-sky-values')
        ->expectsOutputToContain('Observations with an SQM moved to the limiting magnitude: 1')
        ->assertSuccessful();

    expect(skyValues($id))->toBe([4.9, null]);
});

it('moves a NELM stored as SQM to the limiting magnitude', function () {
    $empty = skyObservation(4.9, null);
    $zero = skyObservation(5.1, 0.0);

    $this->artisan('observations:repair-sky-values', ['--apply' => true])->assertSuccessful();

    expect(skyValues($empty))->toBe([null, 4.9])
        ->and(skyValues($zero))->toBe([null, 5.1])
        ->and(DB::table('observations')->find($empty)->updated_at)->not->toBe('2009-05-11 00:00:00');
});

it('drops an SQM below 10 that is no NELM, or when the NELM is set', function () {
    $placeholder = skyObservation(1.0, null);
    $withNelm = skyObservation(4.9, 6.2);

    $this->artisan('observations:repair-sky-values', ['--apply' => true])->assertSuccessful();

    expect(skyValues($placeholder))->toBe([null, null])
        ->and(skyValues($withNelm))->toBe([null, 6.2]);
});

it('drops a NELM above 8 and keeps the SQM', function () {
    $id = skyObservation(19.0, 9.0);
    $unset = skyObservation(-1.0, 8.5);

    $this->artisan('observations:repair-sky-values', ['--apply' => true])->assertSuccessful();

    expect(skyValues($id))->toBe([19.0, null])
        ->and(skyValues($unset))->toBe([null, null]);
});

it('leaves valid values alone', function () {
    $ids = [skyObservation(21.5, 6.5), skyObservation(23.3, null), skyObservation(null, 8.0)];

    $this->artisan('observations:repair-sky-values', ['--apply' => true])
        ->expectsOutputToContain('Observations with a value dropped: 0')
        ->assertSuccessful();

    expect(array_map('skyValues', $ids))->toBe([[21.5, 6.5], [23.3, null], [null, 8.0]]);
});

it('stores the placeholders of the old site as empty, without changing updated_at', function () {
    $ids = [skyObservation(-1.0, 0.0), skyObservation(0.0, 6.2), skyObservation(21.5, 0.0)];

    $this->artisan('observations:repair-sky-values')
        ->expectsOutputToContain('Observations with a placeholder SQM (-1 or 0) set to empty: 2')
        ->expectsOutputToContain('Observations with a placeholder limiting magnitude (0) set to empty: 2')
        ->assertSuccessful();
    expect(skyValues($ids[0]))->toBe([-1.0, 0.0]);

    $this->artisan('observations:repair-sky-values', ['--apply' => true])
        ->expectsOutputToContain('Observations with a placeholder SQM (-1 or 0) set to empty: 2')
        ->assertSuccessful();

    expect(array_map('skyValues', $ids))->toBe([[null, null], [null, 6.2], [21.5, null]])
        ->and(DB::table('observations')->whereIn('id', $ids)->pluck('updated_at')->unique()->all())->toBe(['2009-05-11 00:00:00']);
});

it('repairs locations and deletes their cached metrics', function () {
    $telescopic = LocationFactory::new()->create(['observer' => 'testobserver', 'limitingMagnitude' => 9.5, 'skyBackground' => -999]);
    $swapped = LocationFactory::new()->create(['observer' => 'testobserver', 'limitingMagnitude' => -999, 'skyBackground' => 5.4]);
    $valid = LocationFactory::new()->create(['observer' => 'testobserver', 'limitingMagnitude' => 6.8, 'skyBackground' => 21.2]);
    $metric = UserObjectMetric::forceCreate([
        'user_id' => $telescopic->user_id,
        'object_name' => 'M 31',
        'location_id' => $telescopic->id,
    ]);

    $this->artisan('observations:repair-sky-values', ['--apply' => true])
        ->expectsOutputToContain('Locations repaired: 2')
        ->assertSuccessful();

    expect($telescopic->fresh()->only('limitingMagnitude', 'skyBackground'))->toEqual(['limitingMagnitude' => -999, 'skyBackground' => -999])
        ->and($swapped->fresh()->only('limitingMagnitude', 'skyBackground'))->toEqual(['limitingMagnitude' => 5.4, 'skyBackground' => -999])
        ->and($valid->fresh()->only('limitingMagnitude', 'skyBackground'))->toEqual(['limitingMagnitude' => 6.8, 'skyBackground' => 21.2])
        ->and(UserObjectMetric::find($metric->id))->toBeNull();
});
