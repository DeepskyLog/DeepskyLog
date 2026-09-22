<?php

use App\Livewire\AdminUserTable;
use App\Models\User;
use Livewire\Livewire;

function adminUserTableTeam(): object
{
    $owner = User::factory()->withPersonalTeam()->create(['name' => 'Alice Andromeda']);
    $team = $owner->currentTeam;

    $team->users()->attach($owner, ['role' => 'admin']);
    $team->users()->attach(User::factory()->create(['name' => 'Bob Bootes']), ['role' => 'admin']);

    test()->actingAs($owner);

    return $team;
}

test('the team member table can be searched by name', function () {
    $team = adminUserTableTeam();

    Livewire::test(AdminUserTable::class, ['team' => $team->id])
        ->set('search', 'Bootes')
        ->assertOk()
        ->assertSee('Bob Bootes')
        ->assertDontSee('Alice Andromeda');
});

test('the team member table can be sorted by name', function () {
    $team = adminUserTableTeam();

    Livewire::test(AdminUserTable::class, ['team' => $team->id])
        ->call('sortBy', 'name')
        ->assertOk()
        ->assertSeeInOrder(['Alice Andromeda', 'Bob Bootes']);
});
