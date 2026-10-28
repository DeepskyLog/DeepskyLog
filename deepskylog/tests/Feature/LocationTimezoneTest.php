<?php

use App\Support\Timezones;
use Database\Factories\LocationFactory;

it('tells time zone names from offsets', function (string $timezone, bool $expected) {
    expect(Timezones::isIdentifier($timezone))->toBe($expected);
})->with([
    ['Europe/Brussels', true],
    ['UTC', true],
    ['CET', true],
    ['60', false],
    ['-300', false],
    ['+01:00', false],
    ['Foo/Bar', false],
    ['', false],
]);

it('finds the time zone of a site from its offset and position', function (int $minutes, ?float $latitude, ?float $longitude, string $expected) {
    expect(Timezones::fromOffset($minutes, $latitude, $longitude))->toBe($expected);
})->with([
    'Leuven' => [60, 50.88, 4.70, 'Europe/Brussels'],
    'Swiss Alps' => [60, 46.0, 7.5, 'Europe/Zurich'],
    'New York' => [-300, 40.7, -74.0, 'America/New_York'],
    'London' => [0, 51.5, -0.1, 'Europe/London'],
    'no position, whole hours' => [-480, null, null, 'Etc/GMT+8'],
    'no position, UT' => [0, null, null, 'UTC'],
    'no position, half an hour' => [330, null, null, 'UTC'],
]);

it('repairs the locations that got an offset as their time zone', function () {
    $offset = LocationFactory::new()->create(['observer' => '', 'timezone' => '60', 'latitude' => 50.88, 'longitude' => 4.70]);
    $named = LocationFactory::new()->create(['observer' => '', 'timezone' => 'Europe/Paris', 'latitude' => 50.88, 'longitude' => 4.70]);

    (require database_path('migrations/2026_10_20_000000_repair_numeric_location_timezones.php'))->up();

    expect($offset->fresh()->timezone)->toBe('Europe/Brussels')
        ->and($named->fresh()->timezone)->toBe('Europe/Paris');
});
