<?php

use App\Livewire\CreateLens;
use App\Models\LensMake;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->televue = LensMake::firstOrCreate(['name' => 'Tele Vue']);
    $this->actingAs(User::factory()->create());
});

it('proposes the make, factor and kind as the name of a new lens', function (float $factor, string $name) {
    Livewire::test(CreateLens::class)
        ->set('lens_make', $this->televue->id)
        ->set('factor', $factor)
        ->assertSet('proposed_name', $name)
        ->assertSet('name', $name);
})->with([
    [2, 'Tele Vue 2x Barlow'],
    [2.5, 'Tele Vue 2.5x Barlow'],
    [0.5, 'Tele Vue 0.5x Focal reducer'],
    [1.15, 'Tele Vue 1.15x Coma corrector'],
    [1, 'Tele Vue 1x Coma corrector'],
]);

it('proposes a new make', function () {
    Livewire::test(CreateLens::class)
        ->set('lens_new_make', 'Baader')
        ->set('factor', 2.25)
        ->assertSet('name', 'Baader 2.25x Barlow');
});
