<?php

use App\Models\Club;
use App\Models\ObjectFindingNote;
use App\Models\ObservationSession;
use App\Models\ObservingRequest;
use App\Models\StarParty;
use App\Models\SuspectObservation;
use App\Models\User;

/**
 * Another observer's data is only changed in the role that allows it: an
 * administrator working in the role of observer has the rights of an
 * observer, and database experts have the rights the comments promise them.
 */
function roleTestUser(object $test, string $role): User
{
    $user = User::factory()->create();
    // An administrator who switched to another role stays a member of the team
    if ($role !== 'Database Experts') {
        $test->addUserToTeam($user, 'Administrators');
    }
    if ($role !== 'Administrators') {
        $test->addUserToTeam($user, $role);
    }
    $test->switchUserToTeam($user, $role);

    return $user->fresh();
}

it('gives the moderator role to administrators and database experts only', function (string $role, bool $moderator) {
    expect(roleTestUser($this, $role)->hasModeratorRole())->toBe($moderator);
})->with([
    ['Administrators', true],
    ['Database Experts', true],
    ['Observers', false],
]);

it('lets only the moderator roles delete finding notes and close and dismiss for others', function (string $role, bool $allowed) {
    $user = roleTestUser($this, $role);
    $author = User::factory()->create();

    expect((new ObjectFindingNote(['user_id' => $author->id]))->canBeDeletedBy($user))->toBe($allowed)
        ->and((new ObservingRequest(['user_id' => $author->id]))->canBeClosedBy($user))->toBe($allowed)
        ->and((new SuspectObservation)->canBeManagedBy($user))->toBe($allowed);
})->with([
    ['Administrators', true],
    ['Database Experts', true],
    ['Observers', false],
]);

it('lets only the administrator role manage starparties, clubs and sessions of others', function (string $role, bool $allowed) {
    config(['sessions.allow_admin_override' => true]);
    $user = roleTestUser($this, $role);
    $organiser = User::factory()->create();

    expect((new StarParty(['user_id' => $organiser->id]))->canBeManagedBy($user))->toBe($allowed)
        ->and((new Club)->canBeManagedBy($user))->toBe($allowed)
        ->and((new ObservationSession(['observerid' => $organiser->username]))->canBeEditedBy($user))->toBe($allowed);
})->with([
    ['Administrators', true],
    ['Database Experts', false],
    ['Observers', false],
]);
