<?php

namespace App\Console\Commands;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the observers who asked for it on the profile page (users.follow_digest)
 * a DeepskyLog message with the observations the observers they follow added
 * since the last digest: how many per observer, with links. One message a
 * day, not one per observation, so an import of hundreds of observations
 * gives one line. Nothing is sent when there is nothing new.
 *
 * users.follow_digest_sent_at is when the observer's digest was last made,
 * also when there was nothing to send; the next digest covers the
 * observations added since (at most a week, if the command did not run).
 * Scheduled daily with --apply.
 */
class SendFollowDigest extends Command
{
    /** A digest covers at most this many days. */
    private const MAX_DAYS = 7;

    /** An observer made a digest less than this many hours ago is skipped, so a second run on the same day sends nothing. */
    private const MIN_HOURS = 20;

    protected $signature = 'follows:send-digest
        {--apply : Send the messages}';

    protected $description = 'Send the daily message with the new observations of the observers someone follows';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to send the messages.');
        }

        // Timestamps are stored to the second: observations of the second
        // the command runs in go into the next digest.
        $until = now()->startOfSecond();
        $sent = 0;
        $rows = [];

        User::query()
            ->where('follow_digest', true)
            ->whereHas('following')
            ->where(fn ($query) => $query->whereNull('follow_digest_sent_at')->orWhere('follow_digest_sent_at', '<=', $until->copy()->subHours(self::MIN_HOURS)))
            ->orderBy('id')
            ->each(function (User $user) use ($apply, $until, &$sent, &$rows) {
                $since = $user->follow_digest_sent_at ?? $until->copy()->subDay();
                $since = $since->max($until->copy()->subDays(self::MAX_DAYS));

                $counts = $user->followedObservations()
                    ->where('created_at', '>=', $since)
                    ->where('created_at', '<', $until)
                    ->selectRaw('observer_username, COUNT(*) AS aggregate')
                    ->groupBy('observer_username')
                    ->pluck('aggregate', 'observer_username');

                if ($counts->isNotEmpty()) {
                    $sent++;
                    $rows[] = [$user->id, $user->username, $counts->count(), $counts->sum()];
                    if ($apply) {
                        $this->send($user, $counts->all());
                    }
                }

                if ($apply) {
                    $user->forceFill(['follow_digest_sent_at' => $until])->save();
                }
            });

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Id', 'Username', 'Observers', 'Observations'], $rows);
        }

        $this->info(($apply ? 'Digests sent: ' : 'Digests to send: ').$sent);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $counts  the number of new observations per username
     */
    private function send(User $user, array $counts): void
    {
        try {
            $locale = $user->language ?: config('app.locale');
            $observers = User::whereIn('username', array_keys($counts))->get(['name', 'username', 'slug'])->keyBy('username');
            arsort($counts);

            $subject = __('New observations of the observers you follow', [], $locale);

            $body = '<p>'.e(trans_choice(
                ':count observer you follow added new observations.|:count observers you follow added new observations.',
                count($counts),
                ['count' => count($counts)],
                $locale,
            )).'</p><ul>';
            foreach ($counts as $username => $count) {
                $observer = $observers->get($username);
                $name = e($observer->name ?? $username);
                $link = $observer ? '<a href="'.e(route('observations.show', ['observer' => $observer->slug])).'">'.$name.'</a>' : $name;
                $body .= '<li>'.$link.': '.e(trans_choice(':count new observation|:count new observations', $count, ['count' => $count], $locale)).'</li>';
            }
            $body .= '</ul><p><a href="'.e(url('/observations?following=1')).'">'.e(__('Observations of the observers you follow', [], $locale)).'</a></p>';
            $body .= '<p>'.e(__('You can turn this daily message off in your profile settings.', [], $locale)).'</p>';
            $body = Message::sanitizeHtml($body);

            $message = Message::create([
                'sender' => 'deepskylog',
                'receiver' => $user->username,
                'subject' => Message::sanitizeHtml($subject),
                'message' => $body,
                'date' => now(),
            ]);

            if ($user->sendMail) {
                Mail::to($user->email)
                    ->locale($locale)
                    ->send(new MessageReceived('DeepskyLog', $subject, $body, $user->name, route('messages.show', $message->id)));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send the follow digest', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
