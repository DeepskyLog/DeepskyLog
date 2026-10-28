<?php

use App\Models\InstrumentSet;
use App\Models\Observation;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;

it('keeps the entries of the observation form when the instrument set changes', function () {
    $user = User::factory()->create();
    $inSet = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $eyepiece = EyepieceFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);
    $set = InstrumentSet::factory()->create(['user_id' => $user->id]);
    $set->instruments()->attach($inSet);

    $response = $this->actingAs($user)->post(route('observation.store'), [
        'switch_instrument_set' => $set->id,
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'edit_unified_id' => '12',
        'observed_on' => '2026-03-01',
        'description' => 'A faint glow',
        'seeing' => '2',
        'instrument_id' => $inSet->id,
        'eyepiece_id' => $eyepiece->id,
        'magnification' => '120',
    ]);

    $response->assertRedirect(route('observation.create', ['slug' => 'm-65', 'instrument_set' => $set->id]));
    expect(Observation::count())->toBe(0)
        ->and(session()->getOldInput())->toMatchArray([
            'edit_unified_id' => '12',
            'observed_on' => '2026-03-01',
            'description' => 'A faint glow',
            'seeing' => '2',
            'instrument_id' => $inSet->id,
        ])
        // The eyepiece is not in the set: it falls back to the default and the
        // magnification is computed again.
        ->and(session()->getOldInput())->not->toHaveKeys(['eyepiece_id', 'magnification', 'switch_instrument_set', '_token']);
});

it('keeps all equipment when switching to all gear', function () {
    $user = User::factory()->create();
    $instrument = InstrumentFactory::new()->create(['user_id' => $user->id, 'observer' => $user->username]);

    $this->actingAs($user)->post(route('observation.store'), [
        'switch_instrument_set' => '',
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'instrument_id' => $instrument->id,
        'magnification' => '120',
    ])->assertRedirect(route('observation.create', ['slug' => 'm-65']));

    expect(session()->getOldInput())->toMatchArray([
        'instrument_id' => $instrument->id,
        'magnification' => '120',
    ]);
});

it('ignores an instrument set of another observer', function () {
    $user = User::factory()->create();
    $other = InstrumentSet::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), [
        'switch_instrument_set' => $other->id,
        'target_table' => 'objects',
        'target_slug' => 'm-65',
    ])->assertRedirect(route('observation.create', ['slug' => 'm-65']));
});
