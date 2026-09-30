<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

test('a user cannot switch to a team they are not a member of', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $administrators = $this->getTeamFromString('Administrators');

    expect($user->switchTeam($administrators))->toBeFalse();
    expect($user->fresh()->isAdministrator())->toBeFalse();
});

test('the switch-team route refuses a team the user is not a member of', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $administrators = $this->getTeamFromString('Administrators');

    $this->actingAs($user)
        ->post(route('current-team.switch', $administrators->id))
        ->assertForbidden();

    expect($user->fresh()->current_team_id)->not->toBe($administrators->id);
});

test('the current-team route refuses a team the user is not a member of', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $administrators = $this->getTeamFromString('Administrators');

    $this->actingAs($user)
        ->put(route('current-team.update'), ['team_id' => $administrators->id])
        ->assertForbidden();

    expect($user->fresh()->isAdministrator())->toBeFalse();
});

test('a member of a team can switch to it', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $this->addUserToTeam($user, 'Administrators');
    $administrators = $this->getTeamFromString('Administrators');

    $this->actingAs($user)
        ->post(route('current-team.switch', $administrators->id))
        ->assertRedirect();

    expect($user->fresh()->isAdministrator())->toBeTrue();
});

test('the team cannot be switched with a GET request', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $this->addUserToTeam($user, 'Administrators');
    $administrators = $this->getTeamFromString('Administrators');

    $this->actingAs($user)
        ->get('/switch-team/' . $administrators->id)
        ->assertMethodNotAllowed();

    expect($user->fresh()->isAdministrator())->toBeFalse();
});

test('the team cannot be switched without a CSRF token', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $this->addUserToTeam($user, 'Administrators');
    $administrators = $this->getTeamFromString('Administrators');

    // The test kernel skips the CSRF check unless it is asked for.
    $this->withMiddleware(ValidateCsrfToken::class);
    app()->detectEnvironment(fn () => 'production');

    $this->actingAs($user)
        ->post(route('current-team.switch', $administrators->id))
        ->assertStatus(419);

    expect($user->fresh()->isAdministrator())->toBeFalse();
});

test('nobody can create a team', function () {
    $user = $this->createUserAndAssignToTeam('Observers');
    $this->addUserToTeam($user, 'Administrators');
    $this->actingAs($user)->post(route('current-team.switch', $this->getTeamFromString('Administrators')->id));

    $this->actingAs($user->fresh())
        ->get(route('teams.create'))
        ->assertForbidden();

    expect(fn () => app(\App\Actions\Jetstream\CreateTeam::class)->create($user->fresh(), ['name' => 'New team']))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    expect(Team::count())->toBe(3);
});

test('a user created from a social provider joins the Observers team without a team of their own', function () {
    $this->getTeamFromString('Observers');

    $providerUser = new \Laravel\Socialite\Two\User();
    $providerUser->map(['id' => '123', 'name' => 'Jane Doe', 'email' => 'jane@example.com'])->setToken('token');

    $user = app(\JoelButcher\Socialstream\Actions\CreateUserWithTeamsFromProvider::class)
        ->create('github', $providerUser);

    expect($user->fresh()->currentTeam->name)->toBe('Observers');
    expect(Team::count())->toBe(3);
});
