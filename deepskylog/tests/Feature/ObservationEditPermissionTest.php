<?php

use App\Models\Observation;
use App\Models\User;

/**
 * Another observer's observation can only be changed or deleted in the role
 * of administrator or database expert, not by an administrator who is
 * working in the role of observer.
 */
function editPermissionObservation(User $owner): Observation
{
    return Observation::create([
        'observer_username' => $owner->username,
        'observer_user_id' => $owner->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
        'description' => 'Seen',
    ]);
}

function editPermissionParams(User $owner): array
{
    return ['observer' => $owner->slug, 'date' => '2026-03-01', 'object' => 'm-65'];
}

// An administrator in the given role, or a database expert who is not an administrator
function editPermissionViewer(object $test, string $role): User
{
    $viewer = User::factory()->create();
    if ($role !== 'Database Experts') {
        $test->addUserToTeam($viewer, 'Administrators');
    }
    if ($role !== 'Administrators') {
        $test->addUserToTeam($viewer, $role);
    }
    $test->switchUserToTeam($viewer, $role);

    return $viewer->fresh();
}

it('hides the update and delete buttons from an administrator in the role of observer', function () {
    $owner = User::factory()->create();
    editPermissionObservation($owner);
    $viewer = editPermissionViewer($this, 'Observers');
    $editUrl = route('observations.edit', editPermissionParams($owner) + ['index' => 1]);

    $this->actingAs($viewer)->get(route('observations.detail', editPermissionParams($owner)))
        ->assertOk()
        ->assertDontSee($editUrl, false);

    $this->actingAs($viewer)->get($editUrl)->assertForbidden();
    $this->actingAs($viewer)->delete(route('observations.destroy', editPermissionParams($owner) + ['index' => 1]))->assertForbidden();
    expect(Observation::count())->toBe(1);
});

it('shows the update button in the role of administrator or database expert', function (string $role) {
    $owner = User::factory()->create();
    editPermissionObservation($owner);
    $viewer = editPermissionViewer($this, $role);
    $editUrl = route('observations.edit', editPermissionParams($owner) + ['index' => 1]);

    $this->actingAs($viewer)->get(route('observations.detail', editPermissionParams($owner)))
        ->assertOk()
        ->assertSee($editUrl, false);

    expect($this->actingAs($viewer)->get($editUrl)->status())->not->toBe(403);
})->with(['Administrators', 'Database Experts']);

it('lets the observer change their own observation', function () {
    $owner = User::factory()->create();
    editPermissionObservation($owner);
    $this->switchUserToTeam($owner, 'Observers');
    $editUrl = route('observations.edit', editPermissionParams($owner) + ['index' => 1]);

    $this->actingAs($owner)->get(route('observations.detail', editPermissionParams($owner)))
        ->assertOk()
        ->assertSee($editUrl, false);
});
