<?php

use App\Services\TelescopicLimitingMagnitude;

it('reaches about magnitude 14.5 with a 200 mm reflector at high power under a sky of 6.5', function () {
    expect(TelescopicLimitingMagnitude::calculate(6.5, 200, 200, 25, true))->toBe(14.5);
});

it('reaches fainter stars at higher magnifications', function () {
    $low  = TelescopicLimitingMagnitude::calculate(6.5, 200, 40, 25, true);
    $high = TelescopicLimitingMagnitude::calculate(6.5, 200, 200, 25, true);

    expect($high)->toBeGreaterThan($low);
});

it('gains nothing beyond an exit pupil of half a millimetre', function () {
    expect(TelescopicLimitingMagnitude::calculate(6.5, 200, 400))
        ->toBe(TelescopicLimitingMagnitude::calculate(6.5, 200, 800));
});

it('only counts the part of the aperture the eye takes in when the exit pupil is too large', function () {
    // 50 mm at 4x: an exit pupil of 12.5 mm, of which the eye takes in 7 mm (28 mm of aperture).
    expect(TelescopicLimitingMagnitude::calculate(6.0, 50, 4))
        ->toBe(TelescopicLimitingMagnitude::calculate(6.0, 28, 4));
});

it('follows the limiting magnitude of the sky', function () {
    $dark   = TelescopicLimitingMagnitude::calculate(6.5, 50, 10);
    $bright = TelescopicLimitingMagnitude::calculate(5.0, 50, 10);

    expect(round($dark - $bright, 1))->toBe(1.5);
});

it('never goes beyond the star catalogue', function () {
    expect(TelescopicLimitingMagnitude::calculate(7.5, 2000, 500, 0, true))->toBe(TelescopicLimitingMagnitude::MAX_MAGNITUDE);
});
