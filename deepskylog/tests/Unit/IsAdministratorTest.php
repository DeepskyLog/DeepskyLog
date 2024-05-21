<?php

/**
 * Test that the isAdministrator method returns true when the user belongs to the Administrators team.
 */
it('returns true when the user belongs to the Administrators team', function () {
    // Create a user and assign it to the Administrators team
    $user = $this->createUserAndAssignToTeam('Administrators');

    // Assert that the isAdministrator method returns true
    expect($user->isAdministrator())->toBeTrue()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeFalse();
});

test('is administrator returns true when user belongs to different teams and administrators team is active', function () {
    // Create a user and assign it to the Administrators team
    $user = $this->createUserAndAssignToTeam('Administrators');
    $this->addUserToTeam($user, 'Observers');

    // Assert that the isAdministrator method returns true
    expect($user->isAdministrator())->toBeTrue()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeFalse();

    // Switch to Observers team
    $this->switchUserToTeam($user, 'Observers');

    expect($user->isAdministrator())->toBeFalse()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeTrue();

    // Switch to Admin team
    $this->switchUserToTeam($user, 'Administrators');

    // Assert that the isAdministrator method returns true
    expect($user->isAdministrator())->toBeTrue()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeFalse();
});

test('is administrator returns false when user does not belong to administrators team', function () {
    // Create a user and assign it to the Observers team
    $user = $this->createUserAndAssignToTeam('Observers');

    // Assert that the isAdministrator method returns false
    expect($user->isAdministrator())->toBeFalse()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeTrue();

    $this->addUserToTeam($user, 'Database Experts');
    expect($user->isAdministrator())->toBeFalse()
        ->and($user->isDatabaseExpert())->toBeFalse()
        ->and($user->isObserver())->toBeTrue();

    $this->switchUserToTeam($user, 'Database Experts');
    expect($user->isAdministrator())->toBeFalse()
        ->and($user->isDatabaseExpert())->toBeTrue()
        ->and($user->isObserver())->toBeFalse();
});
