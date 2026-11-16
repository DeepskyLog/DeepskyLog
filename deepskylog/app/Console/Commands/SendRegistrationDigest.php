<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AdministratorMessages;
use Illuminate\Console\Command;

/**
 * Tells the administrators once a day which observers registered since the
 * last digest: one DeepskyLog message each (mail if sendMail) listing the new
 * accounts, nothing when there are none. users.registration_reported_at marks
 * an account as reported, so a missed run is caught up by the next one.
 */
class SendRegistrationDigest extends Command
{
    /** At most this many accounts are listed by name; the message gives the total. */
    public const MAX_LISTED = 100;

    protected $signature = 'users:send-registration-digest
        {--apply : Send the messages}';

    protected $description = 'Send the administrators the list of observers who registered since the last digest';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to send the messages.');
        }

        $users = User::query()
            ->whereNull('registration_reported_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $count = $users->count();

        $this->info("New accounts: {$count}");
        if ($count && $this->getOutput()->isVerbose()) {
            $this->table(
                ['Id', 'Username', 'Name', 'Email', 'Country', 'Registered'],
                $users->map(fn (User $u) => [$u->id, $u->username, $u->name, $u->email, $u->country, $u->created_at?->format('Y-m-d H:i')])->all()
            );
        }

        if (! $apply || $count === 0) {
            return self::SUCCESS;
        }

        $rows = $users->take(self::MAX_LISTED)->map(fn (User $u) => AdministratorMessages::accountRow($u))->all();
        $sent = AdministratorMessages::send(
            fn (string $locale) => trans_choice('{1} A new observer registered on DeepskyLog|[2,*] :count new observers registered on DeepskyLog', $count, ['count' => $count], $locale),
            function (string $locale) use ($count, $rows) {
                $html = '<p>'.e(trans_choice('{1} This observer registered since the last message:|[2,*] These :count observers registered since the last message:', $count, ['count' => $count], $locale)).'</p>'
                    .AdministratorMessages::accountList($rows, $locale);
                if ($count > count($rows)) {
                    $html .= '<p>'.e(__('and :count more.', ['count' => $count - count($rows)], $locale)).'</p>';
                }

                return $html.'<p><a href="'.e(route('observers.admin')).'">'.e(__('Manage the observers', [], $locale)).'</a></p>';
            }
        );

        User::query()->whereIn('id', $users->pluck('id'))->update(['registration_reported_at' => now()]);
        $this->info("Sent to administrators: {$sent}");

        return self::SUCCESS;
    }
}
