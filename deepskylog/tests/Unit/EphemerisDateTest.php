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

    EphemerisDate::choose('2026-12-24', 'America/New_York');

    expect(EphemerisDate::current('America/New_York'))->toBe('2026-12-24');
});

it('ignores a session date without an expiry, and forgets it', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 29, 2, 0, 0, 'UTC'));
    session()->put(EphemerisDate::SESSION_KEY, '2026-09-20');

    expect(EphemerisDate::current('UTC'))->toBe('2026-09-28')
        ->and(session()->has(EphemerisDate::SESSION_KEY))->toBeFalse();
});

it('does not keep today as a chosen date', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 29, 20, 0, 0, 'UTC'));
    EphemerisDate::choose('2026-09-29', 'UTC');

    expect(session()->has(EphemerisDate::SESSION_KEY))->toBeFalse();

    Carbon::setTestNow(Carbon::create(2026, 9, 30, 12, 0, 0, 'UTC'));
    expect(EphemerisDate::current('UTC'))->toBe('2026-09-30');
});

it('changes the date of the observing night at noon', function () {
    Carbon::setTestNow(Carbon::create(2026, 9, 30, 1, 0, 0, 'Europe/Brussels'));
    expect(EphemerisDate::today('Europe/Brussels'))->toBe('2026-09-29');

    Carbon::setTestNow(Carbon::create(2026, 9, 30, 11, 59, 0, 'Europe/Brussels'));
    expect(EphemerisDate::today('Europe/Brussels'))->toBe('2026-09-29');

    Carbon::setTestNow(Carbon::create(2026, 9, 30, 12, 0, 0, 'Europe/Brussels'));
    expect(EphemerisDate::today('Europe/Brussels'))->toBe('2026-09-30');
});

it('falls back to the timezone of the application for an unknown timezone', function () {
    $location = new \App\Models\Location(['timezone' => 'Not/AZone']);

    expect(EphemerisDate::timezone($location))->toBe(config('app.timezone'));
});
