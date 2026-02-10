<?php

use App\Models\DeepskyObject;

it('formats ra in hours correctly (static)', function () {
    expect(DeepskyObject::formatRa(0.7122777778))->toBe('00h42m44.2s');
});

it('formats dec correctly (static)', function () {
    expect(DeepskyObject::formatDec(41.2684722222))->toBe('41°16\'06.5"');
    expect(DeepskyObject::formatDec(-5.2009722222))->toBe('-5°12\'03.5"');
});

it('formats ra/dec from instance methods', function () {
    $obj = new DeepskyObject();
    $obj->ra = 0.7122777778;
    $obj->decl = 41.2684722222;
    expect($obj->formattedRa())->toBe('00h42m44.2s');
    expect($obj->formattedDec())->toBe('41°16\'06.5"');
});
