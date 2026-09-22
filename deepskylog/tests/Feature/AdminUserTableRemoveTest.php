<?php

use App\Livewire\AdminUserTable;
use App\Models\User;
use Livewire\Livewire;

function adminUserTableWithTwoMembers(): array
{
    $owner = User::factory()->withPersonalTeam()->create(['name' => 'Alice Andromeda']);
    $team = $owner->currentTeam;

    $team->users()->attach($owner, ['role' => 'admin']);
    $team->users()->attach($other = User::factory()->create(['name' => 'Bob Bootes']), ['role' => 'admin']);

    return [$owner, $team, $other];
}

test('the remove button is handed to the front end for every row', function () {
    [$owner, $team] = adminUserTableWithTwoMembers();
    test()->actingAs($owner);

    $component = Livewire::test(AdminUserTable::class, ['team' => $team->id]);
    $effects = (fn () => $this->lastState->getEffects())->call($component);

    $actions = collect($effects['xjs'] ?? [])->firstWhere('expression', 'pgActions');
    $payload = json_decode(data_get($actions, 'params.0', '[]'), true);

    expect($payload)->toHaveKeys(array_map('strval', $team->users->pluck('id')->all()));
    expect(data_get($payload, $team->users->last()->id.'.0.attributes.wire:click'))
        ->toContain("\$dispatch('remove'");
});

test('a team member is removed when the remove button is clicked', function () {
    [$owner, $team, $other] = adminUserTableWithTwoMembers();
    test()->actingAs($owner);

    Livewire::test(AdminUserTable::class, ['team' => $team->id])
        ->dispatch('remove', $other->id)
        ->assertOk();

    expect($team->fresh()->users->pluck('id')->all())->toEqual([$owner->id]);
});

test('somebody who does not own the team cannot remove its members', function () {
    [$owner, $team, $other] = adminUserTableWithTwoMembers();
    test()->actingAs($other);

    Livewire::test(AdminUserTable::class, ['team' => $team->id])
        ->dispatch('remove', $owner->id)
        ->assertForbidden();

    expect($team->fresh()->users)->toHaveCount(2);
});
