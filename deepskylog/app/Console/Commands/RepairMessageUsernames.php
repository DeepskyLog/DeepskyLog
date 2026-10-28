<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair the usernames of the old site's messages.
 *
 * The old site stored the sender and receiver of some messages, and the
 * receiver of the read and deleted marks, with HTML entities
 * ('Torbj&ouml;rn Holmqvist') or as UTF-8 read as Latin-1 ('TorbjÃ¶rn
 * Holmqvist'). These names match no account, so the observer doesn't see the
 * messages sent to them, their sent messages don't link to them, and every
 * broadcast they read or deleted on the old site is unread again.
 *
 * A name is only renamed when the repaired name is the username of an
 * account (compared case-insensitively, the spelling of the account is
 * used). The others belong to deleted accounts and are left as they are: a
 * username can't contain '&', so nobody can see them now, while a new
 * observer registering as 'René' would see the messages of the old 'René'
 * if they were renamed.
 *
 * Safe to run more than once: only names that change are touched.
 */
class RepairMessageUsernames extends Command
{
    protected $signature = 'messages:repair-usernames
        {--apply : Apply fixes to the database}';

    protected $description = 'Rename the HTML-encoded or garbled usernames of old messages and read/deleted marks to the username of the account';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $names = DB::table('messages')->distinct()->pluck('sender')
            ->merge(DB::table('messages')->distinct()->pluck('receiver'))
            ->merge(DB::table('messages_read')->distinct()->pluck('receiver'))
            ->merge(DB::table('messages_deleted')->distinct()->pluck('receiver'))
            ->filter(fn ($name) => is_string($name) && self::repairedName($name) !== $name)
            ->unique()
            ->values();

        $accounts = User::query()
            ->whereIn('username', $names->map(fn ($name) => self::repairedName($name))->unique()->all())
            ->pluck('username')
            ->keyBy(fn ($username) => mb_strtolower($username));

        $renames = [];
        $orphans = [];
        foreach ($names as $name) {
            $account = $accounts[mb_strtolower(self::repairedName($name))] ?? null;
            if ($account === null) {
                $orphans[] = [$name, self::repairedName($name), ...$this->counts($name)];
            } else {
                $renames[$name] = $account;
            }
        }

        if ($renames !== []) {
            $this->table(
                ['Name', 'Account', 'Received', 'Sent', 'Read marks', 'Deleted marks'],
                array_map(fn ($name) => [$name, $renames[$name], ...$this->counts($name)], array_keys($renames))
            );
        }

        if ($orphans !== []) {
            $this->newLine();
            $this->line('Left as they are, no account has the repaired name:');
            $this->table(['Name', 'Repaired name', 'Received', 'Sent', 'Read marks', 'Deleted marks'], $orphans);
        }

        if ($apply && $renames !== []) {
            DB::transaction(function () use ($renames) {
                foreach ($renames as $name => $account) {
                    $this->rename($name, $account);
                }
            });

            foreach (array_unique($renames) as $account) {
                Message::forgetUnreadCount($account);
            }
        }

        $this->newLine();
        $this->info('Names renamed to the username of their account: ' . count($renames));
        $this->info('Names without an account, left as they are: ' . count($orphans));

        if (! $apply && $renames !== []) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }

    /**
     * The name with its HTML entities decoded and, when it is UTF-8 read as
     * Latin-1 ('Ã¶' for 'ö'), with that undone.
     */
    public static function repairedName(string $name): string
    {
        $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // A garbled name only holds Latin-1 characters, with 'Â' or 'Ã'
        // followed by a character from U+0080 to U+00BF: the two bytes of
        // a UTF-8 character from U+0080 to U+00FF.
        if (preg_match('/[\x{C2}\x{C3}][\x{80}-\x{BF}]/u', $name) && ! preg_match('/[^\x{00}-\x{FF}]/u', $name)) {
            $bytes = mb_convert_encoding($name, 'ISO-8859-1', 'UTF-8');
            if (mb_check_encoding($bytes, 'UTF-8')) {
                $name = $bytes;
            }
        }

        return $name;
    }

    /**
     * The messages received and sent under a name, and its read and deleted marks.
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function counts(string $name): array
    {
        return [
            $this->exactly(DB::table('messages'), 'receiver', $name)->count(),
            $this->exactly(DB::table('messages'), 'sender', $name)->count(),
            $this->exactly(DB::table('messages_read'), 'receiver', $name)->count(),
            $this->exactly(DB::table('messages_deleted'), 'receiver', $name)->count(),
        ];
    }

    private function rename(string $name, string $account): void
    {
        $this->exactly(DB::table('messages'), 'receiver', $name)->update(['receiver' => $account]);
        $this->exactly(DB::table('messages'), 'sender', $name)->update(['sender' => $account]);

        // A mark the account already has for the same message is kept, the
        // one under the old name is then dropped.
        foreach (['messages_read' => 'read_at', 'messages_deleted' => 'deleted_at'] as $table => $at) {
            $marks = $this->exactly(DB::table($table), 'receiver', $name)->get(['id', $at]);
            foreach ($marks->chunk(500) as $chunk) {
                DB::table($table)->insertOrIgnore(
                    $chunk->map(fn ($mark) => ['id' => $mark->id, 'receiver' => $account, $at => $mark->$at])->values()->all()
                );
            }
            $this->exactly(DB::table($table), 'receiver', $name)->delete();
        }
    }

    /**
     * Rows whose column holds exactly this name. The columns compare
     * accent-insensitively on MariaDB, where 'Rene' would also match 'René'.
     */
    private function exactly($query, string $column, string $name)
    {
        if (DB::connection()->getDriverName() === 'mysql' || DB::connection()->getDriverName() === 'mariadb') {
            return $query->whereRaw("BINARY `$column` = ?", [$name]);
        }

        return $query->where($column, $name);
    }
}
