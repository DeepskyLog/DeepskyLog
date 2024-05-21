<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\GroupSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user and assign it to the specified team.
     *
     * @param  string  $teamName  The name of the team to assign the user to.
     * @return User The created user.
     */
    public function createUserAndAssignToTeam(string $teamName): User
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
     * Return the team from the team name.
     *
     * @param  string  $teamName  The name of the team.
     * @return Team The team.
     */
    public function getTeamFromString(string $teamName): Team
    {
        // Check if the team exists
        $team = Team::where('name', $teamName)->first();

        // If the team does not exist, seed the groups and try again
        if (! $team) {
            $this->seed(GroupSeeder::class);
            $team = Team::where('name', $teamName)->firstOrFail();
        }

        return $team;
    }

    /**
     * Add a user to a specified team.
     *
     * @param  mixed  $user  The user.
     * @param  string  $teamName  The name of the team to assign the user to.
     */
    public function addUserToTeam(mixed $user, string $teamName): void
    {
        // Get the team from the name
        $team = $this->getTeamFromString($teamName);

        // Attach the user to the team
        $user->teams()->attach($team);
    }

    /**
     * Add a user to a specified team.
     *
     * @param  mixed  $user  The user.
     * @param  string  $teamName  The name of the team to assign the user to.
     */
    public function switchUserToTeam(mixed $user, string $teamName): void
    {
        // Get the team from the name
        $team = $this->getTeamFromString($teamName);

        // Switch the user to the specified team
        $user->switchTeam($team);
    }
}
