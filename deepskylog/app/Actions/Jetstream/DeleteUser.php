<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * The team deleter implementation.
     *
     * @var \Laravel\Jetstream\Contracts\DeletesTeams
     */
    protected $deletesTeams;

    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct(DeletesTeams $deletesTeams)
    {
        $this->deletesTeams = $deletesTeams;
    }

    /**
     * Delete the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        DB::transaction(function () use ($user) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->connectedAccounts->each->delete();
            $user->tokens->each->delete();
            // Rows with a foreign key on the user without a cascade
            $user->instrumentSets->each->delete();
            foreach (['experience_audits', 'experiences', 'achievement_user', 'challenge_user'] as $table) {
                DB::table($table)->where('user_id', $user->id)->delete();
            }
            $user->delete();
        });
    }

    /**
     * Delete the teams and team associations attached to the user.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function deleteTeams($user)
    {
        $user->teams()->detach();

        // The system teams (owned by user 1 in GroupSeeder) hold every
        // user's role and must survive the deletion of their owner.
        $user->ownedTeams
            ->reject(fn ($team) => in_array((int) $team->id, [Team::OBSERVERS, Team::ADMINISTRATORS, Team::DATABASE_EXPERTS], true))
            ->each(function ($team) {
                $this->deletesTeams->delete($team);
            });
    }
}
