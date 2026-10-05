<?php

namespace App\Actions\Jetstream;

use App\Jobs\ComputeObserverStats;
use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\Team;
use App\Observers\ObservationObserver;
use App\Services\ObjectObservationStatistics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Contracts\DeletesTeams;
use Laravel\Jetstream\Contracts\DeletesUsers;

/**
 * Deletes an account with everything the observer recorded: observations
 * (with their sketches, likes and comments), sessions, equipment, locations,
 * observing lists, comments, likes and settings. Used when observers delete
 * their own account on the profile page, by the administrators' "remove
 * user" page and by users:prune-inactive. Messages the observer sent stay in
 * the inbox of their receivers.
 */
class DeleteUser implements DeletesUsers
{
    /**
     * The team deleter implementation.
     *
     * @var DeletesTeams
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
        $files = [];
        $objectNames = [];

        DB::transaction(function () use ($user, &$files, &$objectNames) {
            $this->deleteTeams($user);
            $user->deleteProfilePhoto();
            $user->connectedAccounts->each->delete();
            $user->tokens->each->delete();

            $objectNames = $this->deleteObservations($user, $files);
            $this->deleteSessions($user, $files);

            // Items the observer added to the lists of others stay in those
            // lists, as added by their owner.
            DB::table('observing_list_items')
                ->where('added_by_user_id', $user->id)
                ->update(['added_by_user_id' => DB::raw('(select owner_user_id from observing_lists where observing_lists.id = observing_list_items.observing_list_id)')]);
            // The lists themselves, with their items, comments, likes and
            // subscriptions (cascading foreign keys)
            DB::table('observing_lists')->where('owner_user_id', $user->id)->delete();

            // Equipment and locations, after the instrument sets that use them
            $user->instrumentSets->each->delete();
            foreach (['instruments', 'eyepieces', 'filters', 'lenses', 'locations'] as $table) {
                $files = array_merge($files, DB::table($table)->where('user_id', $user->id)->whereNotNull('picture')->pluck('picture')->all());
                DB::table($table)->where('user_id', $user->id)->delete();
            }

            // Rows with a foreign key on the user without a cascade
            foreach (['experience_audits', 'experiences', 'achievement_user', 'challenge_user', 'sketch_of_the_week', 'sketch_of_the_month'] as $table) {
                DB::table($table)->where('user_id', $user->id)->delete();
            }
            // Personal rows without a foreign key, or one that would only set
            // the user to null; the cascading ones (comments, likes, saved
            // searches, report layouts, streaks) go with the user row
            foreach (['user_object_metrics', 'user_table_settings', 'sessions', 'observation_comments', 'observation_likes'] as $table) {
                DB::table($table)->where('user_id', $user->id)->delete();
            }
            // Tables of the old DeepskyLog, only in imported databases
            foreach (['user_preferences' => ['user_id', $user->id], 'observerobjectlist' => ['observerid', $user->username]] as $table => [$column, $value]) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where($column, $value)->delete();
                }
            }
            DB::table(ObjectObservationStatistics::OBSERVER_TABLE)->where('observer_username', $user->username)->delete();

            // The inbox: messages to the user, and what they read or deleted
            // of the broadcasts. Sent messages stay in the inbox of others.
            DB::table('messages')->where('receiver', $user->username)->delete();
            foreach (['messages_read', 'messages_deleted'] as $table) {
                DB::table($table)->where('receiver', $user->username)->delete();
            }
            $user->delete();
        });

        $this->deleteFiles($files);

        // The observations went with a mass delete, without their model events
        Cache::forget('observer_stats:'.$user->id);
        Cache::forget(ComputeObserverStats::SITE_TOTALS_KEY);
        ObservationObserver::forgetHomepageCaches();

        // The totals of the objects the observer observed: one query per
        // object, so after the response.
        if ($objectNames !== []) {
            defer(fn () => ObjectObservationStatistics::refreshObjects($objectNames, []));
        }
    }

    /**
     * Delete the observations of the user, with their likes, comments,
     * sketch of the week/month entries and session links, and add their
     * drawings to $files.
     *
     * @return array<int, string> the deep-sky objects they were of
     */
    protected function deleteObservations($user, array &$files): array
    {
        $observations = fn () => DB::table('observations')
            ->where(fn ($q) => $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username));

        $objectNames = $observations()->where('target_type', Observation::TYPE_DEEPSKY)->distinct()->pluck('target_name')->filter()->values()->all();

        $observations()->select('id', 'drawing_filename')->orderBy('id')->chunkById(1000, function ($chunk) use (&$files) {
            $ids = $chunk->pluck('id')->all();
            $files = array_merge($files, $chunk->pluck('drawing_filename')->filter()->all());

            foreach (['observation_likes', 'observation_comments'] as $table) {
                DB::table($table)->where('observation_type', ObservationLike::TYPE_OBSERVATION)->whereIn('observation_id', $ids)->delete();
            }
            DB::table('sketch_of_the_week')->whereIn('observation_id', $ids)->delete();
            DB::table('sketch_of_the_month')->whereIn('observation_id', $ids)->delete();
            DB::table('observing_list_items')->whereIn('source_observation_id', $ids)->update(['source_observation_id' => null]);
            // The deep-sky and comet details and the session links cascade
            DB::table('observations')->whereIn('id', $ids)->delete();
        });

        return $objectNames;
    }

    /**
     * Delete the sessions of the user, and take the user out of the sessions
     * of others they observed in. Adds the session pictures to $files.
     */
    protected function deleteSessions($user, array &$files): void
    {
        $sessions = DB::table('observation_sessions')->where('observerid', $user->username)->get(['id', 'picture']);
        $ids = $sessions->pluck('id')->all();

        foreach (array_chunk($ids, 1000) as $chunk) {
            DB::table('sessionObservations')->whereIn('sessionid', $chunk)->delete();
            DB::table('sessionObservers')->whereIn('sessionid', $chunk)->delete();
            DB::table('observation_likes')->where('observation_type', ObservationLike::TYPE_SESSION)->whereIn('observation_id', $chunk)->delete();
            DB::table('observation_sessions')->whereIn('id', $chunk)->delete();
        }
        $files = array_merge($files, $sessions->pluck('picture')->filter()->all());
        foreach ($ids as $id) {
            foreach (glob(public_path('images/sessions/'.$id.'.*')) ?: [] as $image) {
                @unlink($image);
            }
        }

        DB::table('sessionObservers')->where('observer', $user->username)->delete();
    }

    /**
     * Remove drawings and pictures from the public disk, unless another row
     * still uses the same file (old names could be shared).
     *
     * @param  array<int, string>  $files
     */
    protected function deleteFiles(array $files): void
    {
        $uses = [
            'observations' => 'drawing_filename', 'observation_sessions' => 'picture', 'instruments' => 'picture',
            'eyepieces' => 'picture', 'filters' => 'picture', 'lenses' => 'picture', 'locations' => 'picture',
        ];

        foreach (array_unique($files) as $file) {
            $file = ltrim((string) $file, '/');
            if ($file === '' || str_contains($file, '..')) {
                continue;
            }
            foreach ($uses as $table => $column) {
                if (DB::table($table)->where($column, $file)->exists()) {
                    continue 2;
                }
            }

            try {
                Storage::disk('public')->delete($file);
            } catch (\Throwable $e) {
                report($e);
            }
        }
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
