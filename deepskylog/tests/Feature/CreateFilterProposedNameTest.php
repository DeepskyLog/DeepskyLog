<?php

use App\Livewire\CreateFilter;
use App\Models\FilterMake;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->baader = FilterMake::firstOrCreate(['name' => 'Baader']);
    $this->actingAs(User::factory()->create());
});

it('proposes the make and type as the name of a new filter', function () {
    Livewire::test(CreateFilter::class)
        ->set('filter_make', $this->baader->id)
        ->set('type_id', 4)
        ->assertSet('proposed_name', 'Baader O-III')
        ->assertSet('name', 'Baader O-III');
});

it('proposes a new make, the color and the Wratten number for a color filter', function () {
    Livewire::test(CreateFilter::class)
        ->set('filter_new_make', 'Celestron')
        ->set('type_id', 7)
        ->set('color_id', 3)
        ->set('wratten', '25')
        ->assertSet('name', 'Celestron Red Wratten 25');
});
