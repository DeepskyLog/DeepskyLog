<?php

use App\Support\EphemerisDate;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('reads a date as noon of that date at the observer', function () {
    $date = EphemerisDate::parse('2026-09-29', 'America/Los_Angeles');

    expect($date->toDateString())->toBe('2026-09-29')
        ->and($date->format('H:i'))->toBe('12:00')
        ->and($date->timezoneName)->toBe('America/Los_Angeles');
});

it('keeps the moment of a date with a time and an offset', function () {
    $date = EphemerisDate::parse('2026-09-29T02:00:00+00:00', 'America/New_York');

    expect($date->toDateString())->toBe('2026-09-28')
        ->and($date->format('H:i'))->toBe('22:00');
});

it('uses the current moment without a valid date', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 29, 2, 0, 0, 'UTC'));

    expect(EphemerisDate::parse(null, 'America/New_York')->toDateString())->toBe('2026-09-28')
        ->and(EphemerisDate::parse('not a date', 'UTC')->toDateString())->toBe('2026-09-29')
        ->and(EphemerisDate::today('America/New_York'))->toBe('2026-09-28');
});

it('takes the date of the session', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 29, 2, 0, 0, 'UTC'));

    expect(EphemerisDate::current('America/New_York'))->toBe('2026-09-28');

    session()->put(EphemerisDate::SESSION_KEY, '2026-12-24');

    expect(EphemerisDate::current('America/New_York'))->toBe('2026-12-24');
});

it('falls back to the timezone of the application for an unknown timezone', function () {
    $location = new \App\Models\Location(['timezone' => 'Not/AZone']);

    expect(EphemerisDate::timezone($location))->toBe(config('app.timezone'));
});
