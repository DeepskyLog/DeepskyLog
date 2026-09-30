<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\LegacyPassword;
use Illuminate\Console\Command;

/**
 * Wrap the MD5 passwords left from the old DeepskyLog in bcrypt.
 *
 * The users keep their password: LegacyPassword::check() compares the MD5 of
 * what they type with the wrapped hash, and the login then stores a normal
 * hash. Only a leak of the database no longer reveals the weak MD5 hashes.
 *
 * Safe to run more than once: only 32-hex-digit passwords are touched.
 */
class RehashMd5Passwords extends Command
{
    protected $signature = 'users:rehash-md5-passwords
        {--apply : Apply the changes to the database}';

    protected $description = 'Wrap the legacy MD5 passwords in bcrypt';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $count = 0;

        User::query()->whereNotNull('password')->select(['id', 'password'])
            ->chunkById(500, function ($users) use ($apply, &$count) {
                foreach ($users as $user) {
                    if (! LegacyPassword::isMd5($user->password)) {
                        continue;
                    }

                    $count++;

                    if ($apply) {
                        // Bypass the model's hashed cast and events: the value is already a hash.
                        User::whereKey($user->id)->toBase()->update(['password' => LegacyPassword::wrap($user->password)]);
                    }
                }
            });

        $this->info(($apply ? 'Rehashed' : 'Would rehash')." {$count} MD5 password(s).");

        return self::SUCCESS;
    }
}
