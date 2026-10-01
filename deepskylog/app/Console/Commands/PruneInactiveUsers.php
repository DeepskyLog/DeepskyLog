<?php

namespace App\Console\Commands;

use App\Mail\InactiveAccountWarning;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Throwable;

/**
 * Delete the accounts that never recorded anything in DeepskyLog, as the
 * privacy policy promises: "In case the registered user has not recorded any
 * information in DeepskyLog within 24 months after registration, his account
 * will be made obsolete and personal information deleted from the database."
 *
 * An account is kept as soon as it has one row of its own in any of the
 * tables below: observations, sessions (also as a co-observer of somebody
 * else's session), equipment, locations, observing lists and their items,
 * comments, likes and subscriptions, sketches of the week/month, sent
 * messages, saved searches or report layouts. Rows the site makes for the
 * user (achievements, experience, streaks, object metrics, table settings,
 * teams, tokens, connected accounts) don't count.
 *
 * The first account (the owner of the system teams), administrators and
 * database experts are never deleted, nor is an account with a login session.
 *
 * Each account gets two warnings by email before it is deleted: a month
 * before, when the date of the deletion is fixed (at least a month after the
 * warning, so accounts already older than the period get the full month), and
 * a week before. Only an account that got both is deleted, with Jetstream's
 * DeleteUser action, the same as an observer deleting their own account.
 * The command runs daily, so the warnings go out on time.
 */
class PruneInactiveUsers extends Command
{
    /** The tables holding something an observer recorded, with the column that points to the user id. */
    private const RECORDS_BY_ID = [
        'observations' => 'observer_user_id',
        'instruments' => 'user_id',
        'eyepieces' => 'user_id',
        'lenses' => 'user_id',
        'filters' => 'user_id',
        'locations' => 'user_id',
        'instrument_sets' => 'user_id',
        'observing_lists' => 'owner_user_id',
        'observing_list_items' => 'added_by_user_id',
        'observing_list_comments' => 'user_id',
        'observing_list_likes' => 'user_id',
        'observing_list_subscriptions' => 'user_id',
        'observation_comments' => 'user_id',
        'observation_likes' => 'user_id',
        'sketch_of_the_week' => 'user_id',
        'sketch_of_the_month' => 'user_id',
        'saved_object_searches' => 'user_id',
        'saved_observation_searches' => 'user_id',
        'report_layouts' => 'user_id',
        'sessions' => 'user_id',
    ];

    /** The tables holding something an observer recorded, with the column that holds the username. */
    private const RECORDS_BY_USERNAME = [
        'observations' => 'observer_username',
        'observation_sessions' => 'observerid',
        'sessionObservers' => 'observer',
        'messages' => 'sender',
    ];

    protected $signature = 'users:prune-inactive
        {--apply : Send the warnings and delete the accounts}
        {--months=24 : Months after registration in which the observer has to record something}';

    protected $description = 'Warn and then delete the accounts that recorded nothing in DeepskyLog within 24 months after registration';

    private bool $apply;

    private int $failed = 0;

    public function handle(DeletesUsers $deleter): int
    {
        $this->apply = (bool) $this->option('apply');
        $months = (int) $this->option('months');

        if ($months < 1) {
            $this->error('--months has to be at least 1.');

            return self::FAILURE;
        }

        if (! $this->apply) {
            $this->info('Running in dry-run mode. Use --apply to send the warnings and delete the accounts.');
        }

        // An observer who recorded something after a warning keeps the account;
        // should they remove it all again, the warnings start over.
        $reset = User::query()
            ->whereNotNull('inactivity_deletion_at')
            ->whereNotIn('id', self::inactive($months)->select('users.id'));
        $this->info('Warned accounts that recorded something since: '.$reset->count());
        if ($this->apply) {
            $reset->update(['inactivity_deletion_at' => null, 'inactivity_final_warning_at' => null]);
        }

        // A month before: the date of the deletion is fixed here, and is at
        // least a month after this warning, also for accounts that were
        // already older than the period.
        $this->step(
            'First warning (a month before)',
            self::inactive($months)
                ->whereNull('inactivity_deletion_at'),
            function (User $user) use ($months) {
                $date = $user->created_at->copy()->addMonths($months)->max(now()->addMonth());
                $this->sendWarning($user, $date);
                $user->forceFill(['inactivity_deletion_at' => $date])->save();
            },
            fn (User $user) => $user->created_at->copy()->addMonths($months)->max(now()->addMonth())
        );

        // A week before; when the run comes late, the deletion moves to a week
        // after this warning.
        $this->step(
            'Final warning (a week before)',
            self::inactive($months)
                ->whereNull('inactivity_final_warning_at')
                ->where('inactivity_deletion_at', '<=', now()->addWeek()),
            function (User $user) {
                $date = $user->inactivity_deletion_at->copy()->max(now()->addWeek());
                $this->sendWarning($user, $date);
                $user->forceFill(['inactivity_deletion_at' => $date, 'inactivity_final_warning_at' => now()])->save();
            },
            fn (User $user) => $user->inactivity_deletion_at->copy()->max(now()->addWeek())
        );

        $this->step(
            'Deleted',
            self::inactive($months)
                ->whereNotNull('inactivity_final_warning_at')
                ->where('inactivity_deletion_at', '<=', now()),
            fn (User $user) => $deleter->delete($user),
            fn (User $user) => $user->inactivity_deletion_at
        );

        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * List the accounts of one step and, with --apply, handle them one by one.
     */
    private function step(string $title, Builder $query, callable $handle, callable $date): void
    {
        $users = $query->orderBy('id')->get();

        $this->info("{$title}: {$users->count()}");
        if ($users->isNotEmpty()) {
            $this->table(
                ['Id', 'Username', 'Name', 'Email', 'Registered', 'Deletion'],
                $users->map(fn (User $u) => [
                    $u->id, $u->username, $u->name, $u->email,
                    $u->created_at?->format('Y-m-d'), $date($u)?->format('Y-m-d'),
                ])->all()
            );
        }

        if (! $this->apply) {
            return;
        }

        foreach ($users as $user) {
            try {
                $handle($user);
            } catch (Throwable $e) {
                $this->failed++;
                $this->error("{$title} failed for {$user->username}: {$e->getMessage()}");
                report($e);
            }
        }
    }

    private function sendWarning(User $user, CarbonInterface $date): void
    {
        Mail::to($user->email)
            ->locale($user->language ?: config('app.locale'))
            ->send(new InactiveAccountWarning($user, $date));
    }

    /**
     * The accounts that recorded nothing, from a month before the end of the
     * period on, when they get the first warning.
     */
    public static function inactive(int $months = 24): Builder
    {
        $query = User::query()
            ->where('id', '<>', 1)
            ->where('created_at', '<', now()->subMonths($months - 1))
            ->whereDoesntHave('teams', fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]))
            ->where(fn ($q) => $q->whereNull('current_team_id')
                ->orWhereNotIn('current_team_id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]));

        foreach (self::RECORDS_BY_ID as $table => $column) {
            $query->whereNotExists(fn ($q) => $q->from($table)->whereColumn("{$table}.{$column}", 'users.id'));
        }
        foreach (self::RECORDS_BY_USERNAME as $table => $column) {
            $query->whereNotExists(fn ($q) => $q->from($table)->whereColumn("{$table}.{$column}", 'users.username'));
        }

        return $query;
    }
}
