<?php

use App\Livewire\Profile\UpdateUserObservingInformation;
use App\Models\User;
use Livewire\Livewire;

it('saves the observing settings when no instrument set is selected', function () {
    $this->actingAs($user = User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('stdinstrumentset', null)
        ->call('updateObservingInformation')
        ->assertHasNoErrors()
        ->assertDispatched('saved');

    expect($user->fresh()->stdinstrumentset)->toBeNull();
});

it('saves the observing settings when the selects are cleared to an empty string', function () {
    $this->actingAs($user = User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('stdinstrumentset', '')
        ->set('stdlocation', '')
        ->set('stdtelescope', '')
        ->set('stdeyepiece', '')
        ->call('updateObservingInformation')
        ->assertHasNoErrors()
        ->assertDispatched('saved');

    $user = $user->fresh();

    expect($user->stdinstrumentset)->toBeNull();
    expect($user->stdlocation)->toBeNull();
    expect($user->stdtelescope)->toBeNull();
});

it('saves the observing settings when the "No standard ..." options with id 0 are chosen', function () {
    $this->actingAs($user = User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('stdinstrumentset', 0)
        ->set('stdlocation', '0')
        ->set('stdtelescope', 0)
        ->call('updateObservingInformation')
        ->assertHasNoErrors()
        ->assertDispatched('saved');

    $user = $user->fresh();

    expect($user->stdinstrumentset)->toBeNull();
    expect($user->stdlocation)->toBeNull();
    expect($user->stdtelescope)->toBeNull();
});

it('falls back to the default atlas instead of writing a null atlas code', function () {
    $this->actingAs($user = User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('standardAtlasCode', '')
        ->call('updateObservingInformation')
        ->assertHasNoErrors()
        ->assertDispatched('saved');

    expect($user->fresh()->standardAtlasCode)->toEqual('urano');
});

it('rejects an atlas code that does not exist', function () {
    $this->actingAs(User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('standardAtlasCode', 'no-such-atlas')
        ->call('updateObservingInformation')
        ->assertHasErrors(['standardAtlasCode' => 'exists']);
});

it('keeps a valid atlas code', function () {
    $this->actingAs($user = User::factory()->create()->fresh());

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('standardAtlasCode', 'psa')
        ->call('updateObservingInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->standardAtlasCode)->toEqual('psa');
});
