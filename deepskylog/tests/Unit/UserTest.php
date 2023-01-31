<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\GroupSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IsAdministratorTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test that the isAdministrator method returns true when the user belongs to the Administrators team.
     */
    public function testIsAdministratorReturnsTrueWhenUserBelongsToAdministratorsTeam()
    {
        // Create a user and assign it to the Administrators team
        $user = $this->createUserAndAssignToTeam('Administrators');

        // Assert that the isAdministrator method returns true
        $this->assertTrue($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertFalse($user->isObserver());
    }

    /**
     * Test that the isAdministrator method returns true when the user belongs to the different teams and
     * is now using the Administrators team
     */
    public function testIsAdministratorReturnsTrueWhenUserBelongsToDifferentTeamsAndAdministratorsTeamIsActive()
    {
        // Create a user and assign it to the Administrators team
        $user = $this->createUserAndAssignToTeam('Administrators');
        $this->addUserToTeam($user, "Observers");

        // Assert that the isAdministrator method returns true
        $this->assertTrue($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertFalse($user->isObserver());

        // Switch to Observers team
        $this->switchUserToTeam($user, "Observers");

        $this->assertFalse($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertTrue($user->isObserver());

        // Switch to Admin team
        $this->switchUserToTeam($user, "Administrators");
        // Assert that the isAdministrator method returns true
        $this->assertTrue($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertFalse($user->isObserver());
    }

    /**
     * Test that the isAdministrator method returns false when the user does not belong to the Administrators team.
     */
    public function testIsAdministratorReturnsFalseWhenUserDoesNotBelongToAdministratorsTeam()
    {
        // Create a user and assign it to the Observers team
        $user = $this->createUserAndAssignToTeam('Observers');

        // Assert that the isAdministrator method returns false
        $this->assertFalse($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertTrue($user->isObserver());

        $this->addUserToTeam($user, "Database Experts");
        $this->assertFalse($user->isAdministrator());
        $this->assertFalse($user->isDatabaseExpert());
        $this->assertTrue($user->isObserver());

        $this->switchUserToTeam($user, "Database Experts");
        $this->assertFalse($user->isAdministrator());
        $this->assertTrue($user->isDatabaseExpert());
        $this->assertFalse($user->isObserver());
    }

    /**
     * Create a user and assign it to the specified team.
     *
     * @param string $teamName The name of the team to assign the user to.
     * @return User The created user.
     */
    private function createUserAndAssignToTeam(string $teamName): User
    {
        // Create a user
        $user = User::factory(['id' => 2])->create();

        // Get the team from the name
        $team = $this->getTeamFromString($teamName);

        // Attach the user to the team
        $user->teams()->attach($team);

        // Switch the user to the specified team
        $user->switchTeam($team);

        return $user;
    }

    /**
     * Add a user to a specified team.
     *
     * @param mixed $user The user.
     * @param string $teamName The name of the team to assign the user to.
     */
    private function addUserToTeam(mixed $user, string $teamName)
    {
        // Get the team from the name
        $team = $this->getTeamFromString($teamName);

        // Attach the user to the team
        $user->teams()->attach($team);
    }

    /**
     * Add a user to a specified team.
     *
     * @param mixed $user The user.
     * @param string $teamName The name of the team to assign the user to.
     */
    private function switchUserToTeam(mixed $user, string $teamName)
    {
        // Get the team from the name
        $team = $this->getTeamFromString($teamName);

        // Switch the user to the specified team
        $user->switchTeam($team);
    }

    /**
     * Return the team from the team name.
     *
     * @param string $teamName The name of the team.
     * @return Team The team.
     */
    private function getTeamFromString(string $teamName): Team
    {
        // Check if the team exists
        $team = Team::where('name', $teamName)->first();

        // If the team does not exist, seed the groups and try again
        if (!$team) {
            $this->seed(GroupSeeder::class);
            $team = Team::where('name', $teamName)->firstOrFail();
        }

        return $team;
    }
}
