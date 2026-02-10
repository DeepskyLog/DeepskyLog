<?php

use App\Helpers\CoordinateParser;

it('parses RA in HMS to degrees', function () {
    $parsed = CoordinateParser::parse('12 34 56.7', true);
    $expected = (12 + 34/60 + 56.7/3600) * 15;
    expect($parsed['deg'])->toBeCloseTo($expected, 6);
});

it('parses Dec in DMS to degrees', function () {
    $parsed = CoordinateParser::parse('+12 34 56.7', false);
    $expected = 12 + 34/60 + 56.7/3600;
    expect($parsed['deg'])->toBeCloseTo($expected, 6);
});

it('parses RA decimal hours correctly', function () {
    $parsed = CoordinateParser::parse('12.5', true);
    expect($parsed['deg'])->toBeCloseTo(12.5 * 15.0, 6);
});

it('parses RA decimal degrees correctly', function () {
    $parsed = CoordinateParser::parse('180.0', true);
    expect($parsed['deg'])->toBeCloseTo(180.0, 6);
});

it('formats to HMS', function () {
    $parsed = CoordinateParser::parse('1 2 3.5', true);
    $hms = CoordinateParser::toHMS($parsed);
    expect($hms)->toMatch('/^\\d{2} \\d{2} \\d{2}\\.\\d{2}$/');
});

it('formats negative Dec to DMS with sign', function () {
    $parsed = CoordinateParser::parse('-12 0 0', false);
    $dms = CoordinateParser::toDMS($parsed);
    expect($dms[0])->toBe('-');
});
