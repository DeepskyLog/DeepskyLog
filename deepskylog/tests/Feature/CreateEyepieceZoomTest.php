<?php

use App\Livewire\CreateEyepiece;
use App\Models\Eyepiece;
use Livewire\Livewire;

it('saves the maximum focal length of a zoom eyepiece', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $this->actingAs($user);

    Livewire::test(CreateEyepiece::class)
        ->set('eyepiece_new_make', 'Baader')
        ->set('eyepiece_new_type', 'Hyperion Zoom')
        ->set('name', 'Baader Hyperion Zoom 8-24mm')
        ->set('focal_length_mm', 8)
        ->set('max_focal_length_mm', 24)
        ->set('apparentFOV', 68)
        ->call('save')
        ->assertHasNoErrors();

    expect((float) Eyepiece::where('user_id', $user->id)->firstOrFail()->max_focal_length_mm)->toBe(24.0);
});

it('rejects a maximum focal length that is not larger than the focal length', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Observers'));

    Livewire::test(CreateEyepiece::class)
        ->set('eyepiece_new_make', 'Baader')
        ->set('eyepiece_new_type', 'Hyperion Zoom')
        ->set('name', 'Baader Hyperion Zoom')
        ->set('focal_length_mm', 24)
        ->set('max_focal_length_mm', 8)
        ->call('save')
        ->assertHasErrors('max_focal_length_mm');
});
