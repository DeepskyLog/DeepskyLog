<?php

use App\Models\EyepieceMake;
use App\Models\FilterMake;
use App\Models\InstrumentMake;
use App\Models\LensMake;
use App\Models\User;

it('lists the makes alphabetically, also when added later', function (string $model, string $route) {
    $model::firstOrCreate(['name' => 'Zeta']);
    $model::firstOrCreate(['name' => 'Alpha']);
    $this->actingAs(User::factory()->create());

    $names = collect($this->getJson(route($route))->assertOk()->json())->pluck('name');

    expect($names->search('Alpha'))->toBeLessThan($names->search('Zeta'));
    expect($names->all())->toBe($names->sortBy(fn ($name) => mb_strtolower($name))->values()->all());
})->with([
    [InstrumentMake::class, 'instrument_makes.api'],
    [EyepieceMake::class, 'eyepiece_makes.api'],
    [LensMake::class, 'lens_makes.api'],
    [FilterMake::class, 'filter_makes.api'],
]);
