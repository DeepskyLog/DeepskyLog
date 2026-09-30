<?php

use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\UpdateTeamName;
use App\Livewire\ObservingListNotes;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function notesList(User $owner, bool $public): ObservingList
{
    $list = ObservingList::create([
        'owner_user_id' => $owner->id,
        'name' => 'Notes list ' . ($public ? 'public' : 'private'),
        'public' => $public,
    ]);
    ObservingListItem::create([
        'observing_list_id' => $list->id,
        'object_name' => 'M 31',
        'item_description' => 'Secret note',
        'added_by_user_id' => $owner->id,
    ]);

    return $list;
}

it('grants roles by team id, not by team name', function () {
    $this->getTeamFromString('Observers');
    $user = User::factory()->create();
    $fake = Team::forceCreate(['user_id' => $user->id, 'name' => 'Administrators', 'personal_team' => false]);
    $user->teams()->attach($fake);
    $user->switchTeam($fake);
    $user = $user->fresh();

    expect($user->isAdministrator())->toBeFalse()
        ->and($user->hasAdministratorPrivileges())->toBeFalse();

    $user->teams()->attach(Team::ADMINISTRATORS);
    $user->switchTeam(Team::find(Team::ADMINISTRATORS));
    $user = $user->fresh();

    expect($user->isAdministrator())->toBeTrue()
        ->and($user->hasAdministratorPrivileges())->toBeTrue();
});

it('does not let anybody create a team', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');

    expect(fn () => app(CreateTeam::class)->create($admin, ['name' => 'Some team']))
        ->toThrow(AuthorizationException::class);
});

it('rejects the reserved group names when renaming a team', function () {
    $this->getTeamFromString('Observers');
    $user = User::factory()->create();
    $team = Team::forceCreate(['user_id' => $user->id, 'name' => 'My team', 'personal_team' => true]);

    expect(fn () => app(UpdateTeamName::class)->update($user, $team, ['name' => ' administrators ']))
        ->toThrow(ValidationException::class);
    expect($team->fresh()->name)->toBe('My team');

    app(UpdateTeamName::class)->update($user, $team, ['name' => 'Other team']);
    expect($team->fresh()->name)->toBe('Other team');
});

it('does not show the notes of a private list to other users or guests', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = notesList($owner, false);

    Livewire::actingAs($other)->test(ObservingListNotes::class, ['listId' => $list->id])->assertForbidden();
    Livewire::test(ObservingListNotes::class, ['listId' => $list->id])->assertForbidden();

    Livewire::actingAs($owner)->test(ObservingListNotes::class, ['listId' => $list->id])
        ->assertSet('isOwner', true)
        ->dispatch('observing-list-table-visible-objects-updated', listId: $list->id, objectNames: ['M 31'])
        ->assertSee('Secret note');
});

it('computes isOwner on the server and locks the list properties', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $public = notesList($owner, true);
    $private = notesList($owner, false);

    // An isOwner passed by the caller is ignored.
    $component = Livewire::actingAs($other)
        ->test(ObservingListNotes::class, ['listId' => $public->id, 'isOwner' => true])
        ->assertSet('isOwner', false)
        ->dispatch('observing-list-table-visible-objects-updated', listId: $public->id, objectNames: ['M 31'])
        ->assertSee('Secret note');

    expect(fn () => $component->set('listId', $private->id))->toThrow(Exception::class);
    expect(fn () => $component->set('isOwner', true))->toThrow(Exception::class);
});
