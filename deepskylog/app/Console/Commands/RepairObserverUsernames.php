<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair the usernames the old site stored HTML-encoded ('Torbj&ouml;rn
 * Holmqvist') or as UTF-8 read as Latin-1 ('TorbjÃ¶rn Holmqvist') in the
 * observer columns of sessions, co-observers, equipment, locations and the
 * old site's saved layouts and searches. A session belongs to its observer
 * only through observerid, so these sessions were cut off from the account;
 * the old site finds equipment and locations through the observer column.
 *
 * As messages:repair-usernames (whose repairedName() it uses), a name is only
 * renamed when the repaired name is the username of an account; the names of
 * deleted accounts are left as they are. Safe to run more than once.
 */
class RepairObserverUsernames extends Command
{
    protected $signature = 'observers:repair-usernames
        {--apply : Apply fixes to the database}';

    protected $description = 'Rename the HTML-encoded or garbled usernames of sessions, co-observers, equipment and locations to the username of the account';

    /** The columns that hold an observer's username. */
    public const COLUMNS = [
        'observation_sessions' => 'observerid',
        'sessionObservers' => 'observer',
        'instruments' => 'observer',
        'eyepieces' => 'observer',
        'filters' => 'observer',
        'lenses' => 'observer',
        'locations' => 'observer',
        'legacy_formlayouts' => 'observerid',
        'legacy_observerqueries' => 'observerid',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $columns = array_filter(self::COLUMNS, fn ($column, $table) => Schema::hasTable($table), ARRAY_FILTER_USE_BOTH);

        $names = collect($columns)
            ->flatMap(fn ($column, $table) => DB::table($table)->distinct()->pluck($column))
            ->filter(fn ($name) => is_string($name) && RepairMessageUsernames::repairedName($name) !== $name)
            ->unique()
            ->values();

        $accounts = User::query()
            ->whereIn('username', $names->map(fn ($name) => RepairMessageUsernames::repairedName($name))->unique()->all())
            ->pluck('username')
            ->keyBy(fn ($username) => mb_strtolower($username));

        $renames = [];
        $orphans = [];
        foreach ($names as $name) {
            $account = $accounts[mb_strtolower(RepairMessageUsernames::repairedName($name))] ?? null;
            if ($account === null) {
                $orphans[] = [$name, RepairMessageUsernames::repairedName($name), ...$this->counts($columns, $name)];
            } else {
                $renames[$name] = $account;
            }
        }

        $headers = array_keys($columns);
        if ($renames !== []) {
            $this->table(
                ['Name', 'Account', ...$headers],
                array_map(fn ($name) => [$name, $renames[$name], ...$this->counts($columns, $name)], array_keys($renames))
            );
        }

        if ($orphans !== []) {
            $this->newLine();
            $this->line('Left as they are, no account has the repaired name:');
            $this->table(['Name', 'Repaired name', ...$headers], $orphans);
        }

        if ($apply && $renames !== []) {
            DB::transaction(function () use ($columns, $renames) {
                foreach ($renames as $name => $account) {
                    foreach ($columns as $table => $column) {
                        $this->rename($table, $column, $name, $account);
                    }
                }
            });
        }

        $this->newLine();
        $this->info('Names renamed to the username of their account: '.count($renames));
        $this->info('Names without an account, left as they are: '.count($orphans));

        if (! $apply && $renames !== []) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }

    private function rename(string $table, string $column, string $name, string $account): void
    {
        if ($table === 'sessionObservers') {
            // (sessionid, observer) is the key: a session that already lists
            // the account keeps that row, the one under the old name goes.
            $sessions = $this->exactly(DB::table($table), $column, $name)->pluck('sessionid');
            DB::table($table)->insertOrIgnore($sessions->map(fn ($id) => ['sessionid' => $id, $column => $account])->all());
            $this->exactly(DB::table($table), $column, $name)->delete();

            return;
        }

        $this->exactly(DB::table($table), $column, $name)->update([$column => $account]);
    }

    /**
     * The rows under a name, per table.
     *
     * @param  array<string, string>  $columns
     * @return array<int, int>
     */
    private function counts(array $columns, string $name): array
    {
        return array_values(array_map(
            fn ($column, $table) => $this->exactly(DB::table($table), $column, $name)->count(),
            $columns,
            array_keys($columns)
        ));
    }

    /**
     * Rows whose column holds exactly this name. The columns compare
     * accent-insensitively on MariaDB, where 'Rene' would also match 'René'.
     */
    private function exactly($query, string $column, string $name)
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $query->whereRaw("BINARY `$column` = ?", [$name]);
        }

        return $query->where($column, $name);
    }
}
