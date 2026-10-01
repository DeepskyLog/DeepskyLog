<?php

use App\Livewire\CreateInstrumentSet;
use App\Livewire\Profile\UpdateUserObservingInformation;
use App\Models\InstrumentSet;
use App\Models\Team;
use App\Models\User;
use Database\Factories\EyepieceFactory;
use Database\Factories\InstrumentFactory;
use Database\Factories\LensFactory;
use Database\Factories\LocationFactory;
use Database\Seeders\GroupSeeder;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Livewire\Livewire;

// The equipment factories also need the username of the owner.
function ownedEquipment($factory, User $user)
{
    return $factory->create(['user_id' => $user->id, 'observer' => $user->username]);
}

it('keeps the system teams when their owner is deleted', function () {
    $this->seed(GroupSeeder::class);
    $owner = User::factory()->create();
    Team::whereIn('id', [Team::OBSERVERS, Team::ADMINISTRATORS, Team::DATABASE_EXPERTS])->update(['user_id' => $owner->id]);
    $ownTeam = Team::forceCreate(['user_id' => $owner->id, 'name' => 'Own team', 'personal_team' => 0]);

    app(DeletesUsers::class)->delete($owner);

    expect(Team::whereIn('id', [Team::OBSERVERS, Team::ADMINISTRATORS, Team::DATABASE_EXPERTS])->count())->toBe(3)
        ->and(Team::find($ownTeam->id))->toBeNull();
});

it('does not add the equipment of another user to an instrument set', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $foreignInstrument = ownedEquipment(InstrumentFactory::new(), $other);
    $foreignLocation = ownedEquipment(LocationFactory::new(), $other);

    $this->actingAs($user);

    Livewire::test(CreateInstrumentSet::class)
        ->set('name', 'My set')
        ->set('instruments', [$foreignInstrument->id])
        ->set('locations', [$foreignLocation->id])
        ->call('save')
        ->assertHasErrors(['instruments.0' => 'exists', 'locations.0' => 'exists']);

    expect(InstrumentSet::where('user_id', $user->id)->count())->toBe(0);
});

it('adds the own equipment to an instrument set', function () {
    $user = User::factory()->create();
    $instrument = ownedEquipment(InstrumentFactory::new(), $user);
    $location = ownedEquipment(LocationFactory::new(), $user);

    $this->actingAs($user);

    Livewire::test(CreateInstrumentSet::class)
        ->set('name', 'My set')
        ->set('instruments', [$instrument->id])
        ->set('locations', [$location->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(InstrumentSet::where('user_id', $user->id)->count())->toBe(1);
});

it('lets an administrator edit a set with the equipment of its owner', function () {
    $owner = User::factory()->create();
    $instrument = ownedEquipment(InstrumentFactory::new(), $owner);
    $set = InstrumentSet::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($this->createUserAndAssignToTeam('Administrators'));

    Livewire::test(CreateInstrumentSet::class, ['set' => $set])
        ->set('instruments', [$instrument->id])
        ->call('save')
        ->assertHasNoErrors();
});

it('does not accept the equipment of another user as default', function () {
    $user = User::factory()->create()->fresh();
    $other = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('stdlocation', ownedEquipment(LocationFactory::new(), $other)->id)
        ->set('stdtelescope', ownedEquipment(InstrumentFactory::new(), $other)->id)
        ->set('stdinstrumentset', InstrumentSet::factory()->create(['user_id' => $other->id])->id)
        ->set('stdeyepiece', ownedEquipment(EyepieceFactory::new(), $other)->id)
        ->set('stdlens', ownedEquipment(LensFactory::new(), $other)->id)
        ->call('updateObservingInformation')
        ->assertHasErrors([
            'stdlocation' => 'exists',
            'stdtelescope' => 'exists',
            'stdinstrumentset' => 'exists',
            'stdeyepiece' => 'exists',
            'stdlens' => 'exists',
        ]);

    expect($user->fresh()->stdtelescope)->toBeNull();
});

it('accepts the own equipment as default', function () {
    $user = User::factory()->create()->fresh();
    $instrument = ownedEquipment(InstrumentFactory::new(), $user);

    $this->actingAs($user);

    Livewire::test(UpdateUserObservingInformation::class)
        ->set('stdtelescope', $instrument->id)
        ->call('updateObservingInformation')
        ->assertHasNoErrors();

    expect((int) $user->fresh()->stdtelescope)->toBe($instrument->id);
});

it('does not store an observation with the equipment of another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'observed_on' => '2026-03-01',
        'instrument_id' => ownedEquipment(InstrumentFactory::new(), $other)->id,
        'eyepiece_id' => ownedEquipment(EyepieceFactory::new(), $other)->id,
        'location_id' => ownedEquipment(LocationFactory::new(), $other)->id,
    ])->assertSessionHasErrors(['instrument_id', 'eyepiece_id', 'location_id']);

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'm-65',
        'observed_on' => '2026-03-01',
        'instrument_id' => ownedEquipment(InstrumentFactory::new(), $user)->id,
    ])->assertSessionDoesntHaveErrors('instrument_id');
});
