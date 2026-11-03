<?php

namespace App\Console\Commands;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\ObjectFollow;
use App\Models\User;
use App\Services\PushNotifications;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the observers who asked for it on the profile page (users.follow_digest)
 * a DeepskyLog message, and a push notification, with the observations the
 * observers they follow added since the last digest (how many per observer)
 * and those others added of the objects they follow (how many per object,
 * with the drawings), with links. One message a day, not one per
 * observation, so an import of hundreds of observations gives one line.
 * Nothing is sent when there is nothing new.
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

    protected $description = 'Send the daily message with the new observations of the observers and objects someone follows';

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
            ->where(fn ($query) => $query->whereHas('following')->orWhereHas('followedObjects'))
            ->where(fn ($query) => $query->whereNull('follow_digest_sent_at')->orWhere('follow_digest_sent_at', '<=', $until->copy()->subHours(self::MIN_HOURS)))
            ->orderBy('id')
            ->each(function (User $user) use ($apply, $until, &$sent, &$rows) {
                $since = $user->follow_digest_sent_at ?? $until->copy()->subDay();
                $since = $since->max($until->copy()->subDays(self::MAX_DAYS));

                $counts = $user->followedObserverObservations()
                    ->where('created_at', '>=', $since)
                    ->where('created_at', '<', $until)
                    ->selectRaw('observer_username, COUNT(*) AS aggregate')
                    ->groupBy('observer_username')
                    ->pluck('aggregate', 'observer_username');

                $objects = $user->followedObjectObservations()
                    ->where('created_at', '>=', $since)
                    ->where('created_at', '<', $until)
                    ->selectRaw('target_table, target_key, COUNT(*) AS observations, SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) AS drawings')
                    ->groupBy('target_table', 'target_key')
                    ->get()
                    ->map(fn ($row) => ['table' => $row->target_table, 'key' => (string) $row->target_key, 'observations' => (int) $row->observations, 'drawings' => (int) $row->drawings])
                    ->all();

                if ($counts->isNotEmpty() || $objects !== []) {
                    $sent++;
                    $rows[] = [$user->id, $user->username, $counts->count(), $counts->sum(), count($objects), array_sum(array_column($objects, 'observations'))];
                    if ($apply) {
                        // an observation of a followed object by a followed observer counts once
                        $total = $user->followedObservations()
                            ->where('created_at', '>=', $since)
                            ->where('created_at', '<', $until)
                            ->count();
                        $this->send($user, $counts->all(), $objects, $total);
                    }
                }

                if ($apply) {
                    $user->forceFill(['follow_digest_sent_at' => $until])->save();
                }
            });

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Id', 'Username', 'Observers', 'Their observations', 'Objects', 'Their observations'], $rows);
        }

        $this->info(($apply ? 'Digests sent: ' : 'Digests to send: ').$sent);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $counts  the number of new observations per username
     * @param  list<array{table: string, key: string, observations: int, drawings: int}>  $objects  the new observations per followed object
     * @param  int  $total  the number of new observations
     */
    private function send(User $user, array $counts, array $objects, int $total): void
    {
        try {
            $locale = $user->language ?: config('app.locale');
            $observers = User::whereIn('username', array_keys($counts))->get(['name', 'username', 'slug'])->keyBy('username');
            arsort($counts);
            usort($objects, fn (array $a, array $b) => $b['observations'] <=> $a['observations']);

            $subject = __('New observations of what you follow', [], $locale);

            $body = '';
            if ($counts !== []) {
                $body .= '<p>'.e(trans_choice(
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
                $body .= '</ul>';
            }
            if ($objects !== []) {
                $body .= '<p>'.e(trans_choice(
                    ':count object you follow has new observations.|:count objects you follow have new observations.',
                    count($objects),
                    ['count' => count($objects)],
                    $locale,
                )).'</p><ul>';
                foreach ($objects as $object) {
                    $target = ObjectFollow::resolve($object['table'], $object['key']);
                    $name = e($target['name'] ?? $object['key']);
                    $link = $target ? '<a href="'.e(route('object.show', ['slug' => $target['slug']])).'">'.$name.'</a>' : $name;
                    $text = trans_choice(':count new observation|:count new observations', $object['observations'], ['count' => $object['observations']], $locale);
                    if ($object['drawings'] > 0) {
                        $text .= ', '.trans_choice(':count with a drawing|:count with drawings', $object['drawings'], ['count' => $object['drawings']], $locale);
                    }
                    $body .= '<li>'.$link.': '.e($text).'</li>';
                }
                $body .= '</ul>';
            }
            $body .= '<p><a href="'.e(url('/observations?following=1')).'">'.e(__('Observations of what you follow', [], $locale)).'</a></p>';
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

            PushNotifications::send(
                $user,
                $subject,
                trans_choice(':count new observation|:count new observations', $total, ['count' => $total], $locale),
                url('/observations?following=1'),
                'follow-digest',
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send the follow digest', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
