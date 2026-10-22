<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\User;
use App\Services\ObserverCountries;
use Illuminate\Console\Command;

/**
 * Fills in the country of residence (users.country, an ISO code) of the
 * observers who did not set it: a country name (from the old site) becomes
 * its code, an empty country the one ObserverCountries::suggestFor() finds
 * (e-mail address, locations, standard location, observations). Observers
 * for whom none of these gives a known country are only counted. The
 * observer can change the country on the profile page.
 */
class FillUserCountries extends Command
{
    protected $signature = 'users:fill-country
        {--apply : Write the countries}';

    protected $description = 'Fill in the missing country of residence of the observers from their e-mail address and locations';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the countries.');
        }

        $filled = 0;
        $unknown = 0;
        $rows = [];

        User::query()
            ->select(['id', 'name', 'username', 'email', 'country', 'stdlocation'])
            ->where(fn ($query) => $query->whereNull('country')->orWhere('country', '')->orWhereRaw('LENGTH(country) != 2'))
            ->orderBy('id')
            ->each(function (User $user) use ($apply, &$filled, &$unknown, &$rows) {
                $current = trim((string) $user->country);
                $code = $current !== '' ? Country::codeFor($current) : null;
                $code ??= ObserverCountries::suggestFor($user);

                if ($code === null) {
                    $unknown++;

                    return;
                }

                $filled++;
                $rows[] = [$user->id, $user->username, $current, $code];
                if ($apply) {
                    $user->forceFill(['country' => $code])->save();
                }
            });

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Id', 'Username', 'Country', 'New country'], $rows);
        }

        $this->info(($apply ? 'Filled in: ' : 'To fill in: ').$filled);
        $this->info('Without a known country: '.$unknown);

        return self::SUCCESS;
    }
}
