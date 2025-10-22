<?php

use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Targets\Moon;

it('calculates moon illuminated fraction, phase ratio and new moon date', function () {
    $date = Carbon::create(2025, 10, 22, 12, 0, 0, 'UTC');

    $moon = new Moon();

    // Illuminated fraction should be numeric between 0 and 1
    $ill = $moon->illuminatedFraction($date);
    expect(is_numeric($ill))->toBeTrue();
    expect($ill)->toBeGreaterThanOrEqual(0);
    expect($ill)->toBeLessThanOrEqual(1);

    // Phase ratio should be numeric between 0 and 1
    $pr = $moon->getPhaseRatio($date);
    expect(is_numeric($pr))->toBeTrue();
    expect($pr)->toBeGreaterThanOrEqual(0);
    expect($pr)->toBeLessThanOrEqual(1);

    // newMoonDate should return a Carbon instance after the given date
    $next = $moon->newMoonDate($date);
    expect($next)->toBeInstanceOf(Carbon::class);
    expect($next->greaterThan($date))->toBeTrue();
});
