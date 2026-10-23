<?php

namespace Tests;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\GroupSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Pagination\Paginator;
use Tests\Support\CaseInsensitiveBlueprint;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the tables of the test database with case-insensitive string
     * columns, as on MariaDB, and start every test with Laravel's paginator
     * views.
     */
    public function createApplication()
    {
        $app = parent::createApplication();
        $app->bind(Blueprint::class, CaseInsensitiveBlueprint::class);

        // A paginated Livewire component switches the paginator views to its
        // own and switches them back when it is destroyed, which it is not
        // when its request aborts or throws. The views are static, so reset
        // them, or the plain paginators of later tests render Livewire links.
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        return $app;
    }

    /**
     * Give the SQLite test database the REGEXP_REPLACE() of MariaDB, which
     * the natural sort on object names uses.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $connection = $this->app['db']->connection();
        if ($connection->getDriverName() === 'sqlite') {
            $connection->getPdo()->sqliteCreateFunction(
                'REGEXP_REPLACE',
                fn ($value, $pattern, $replacement) => preg_replace('/'.$pattern.'/u', $replacement, (string) $value),
                3
            );
        }
    }

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
