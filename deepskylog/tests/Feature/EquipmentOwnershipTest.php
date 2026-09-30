<?php

use App\Livewire\CreateEyepiece;
use App\Livewire\CreateFilter;
use App\Livewire\CreateInstrument;
use App\Livewire\CreateInstrumentSet;
use App\Livewire\CreateLens;
use App\Livewire\CreateLocation;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\FilterFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\InstrumentSetFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

// Equipment, locations and instrument sets can only be changed by their owner
// or an administrator: the edit page, the form and its save() all check it.
dataset('owned records', [
    'instrument' => [InstrumentFactory::class, 'instrument', CreateInstrument::class, 'instrument'],
    'eyepiece' => [EyepieceFactory::class, 'eyepiece', CreateEyepiece::class, 'eyepiece'],
    'lens' => [LensFactory::class, 'lens', CreateLens::class, 'lens'],
    'filter' => [FilterFactory::class, 'filter', CreateFilter::class, 'filter'],
    'location' => [LocationFactory::class, 'location', CreateLocation::class, 'location'],
    'instrument set' => [InstrumentSetFactory::class, 'instrumentset', CreateInstrumentSet::class, 'set'],
]);

function createOwnedBy(string $factory, User $owner)
{
    $factory = $factory::new();
    $attributes = ['user_id' => $owner->id];
    // The legacy observer column is still required in some tables.
    if (Schema::hasColumn($factory->newModel()->getTable(), 'observer')) {
        $attributes['observer'] = $owner->username;
    }

    return $factory->create($attributes);
}

it('forbids another user to open the edit page', function (string $factory, string $route) {
    $owner = User::factory()->create();
    $record = createOwnedBy($factory, $owner);

    $this->actingAs(User::factory()->create())
        ->get("/{$route}/{$owner->slug}/{$record->slug}/edit")
        ->assertForbidden();
})->with('owned records');

it('lets the owner open the edit page', function (string $factory, string $route) {
    $owner = User::factory()->create();
    $record = createOwnedBy($factory, $owner);

    $this->actingAs($owner)
        ->get("/{$route}/{$owner->slug}/{$record->slug}/edit")
        ->assertOk();
})->with('owned records');

it('forbids another user to mount the form with the record', function (string $factory, string $route, string $component, string $property) {
    $owner = User::factory()->create();
    $record = createOwnedBy($factory, $owner);

    Livewire::actingAs(User::factory()->create())
        ->test($component, [$property => $record])
        ->assertForbidden();
})->with('owned records');

it('forbids another user to save the record', function (string $factory, string $route, string $component, string $property) {
    $owner = User::factory()->create();
    $record = createOwnedBy($factory, $owner);
    $name = $record->name;

    // Mounted by the owner, then saved in the session of someone else.
    $form = Livewire::actingAs($owner)->test($component, [$property => $record]);
    $this->actingAs(User::factory()->create());

    $form->set('name', 'Taken over')->call('save')->assertForbidden();

    expect($record->fresh()->name)->toBe($name);
})->with('owned records');

it('lets an administrator edit the record of another user', function () {
    $owner = User::factory()->create();
    $instrument = createOwnedBy(InstrumentFactory::class, $owner);

    $this->actingAs($this->createUserAndAssignToTeam('Administrators'))
        ->get("/instrument/{$owner->slug}/{$instrument->slug}/edit")
        ->assertOk();

    expect($instrument->canBeEditedBy(auth()->user()))->toBeTrue()
        ->and($instrument->canBeEditedBy(null))->toBeFalse();
});
