<?php

use App\Livewire\AdvancedObjectSearch;
use App\Livewire\AdvancedObservationSearch;
use Livewire\Livewire;

it('offers the sketch filter in the object search and passes it on', function () {
    Livewire::test(AdvancedObjectSearch::class)
        ->call('addFilter', 'sketches')
        ->assertSee('Objects without a sketch of the week or month')
        ->set('sketches', 'none')
        ->call('search')
        ->assertRedirectContains('sketches=none');
});

it('offers the sketches of the week and month in the drawing filter of the observation search', function () {
    Livewire::test(AdvancedObservationSearch::class)
        ->call('addFilter', 'drawing')
        ->assertSee('Only sketches of the month')
        ->set('drawingState', 'sketch_of_the_month')
        ->call('search')
        ->assertRedirectContains('drawing_state=sketch_of_the_month');
});
