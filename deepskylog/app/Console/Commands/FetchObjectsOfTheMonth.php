<?php

namespace App\Console\Commands;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\ObjectOfTheMonth;
use App\Models\Team;
use App\Models\User;
use App\Services\ObjectsOfTheMonth\AstroforumFeed;
use App\Services\ObjectsOfTheMonth\ObjectsInText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Reads the new threads of the objects of the month of Astroforum
 * (App\Services\ObjectsOfTheMonth\AstroforumFeed) and proposes the objects
 * their first post names (ObjectsInText). The administrators and database
 * experts get a message about the threads whose months have not ended, to
 * confirm the objects on /admin/objects-of-the-month; a thread is only shown
 * once confirmed. A thread already stored is not read again.
 */
class FetchObjectsOfTheMonth extends Command
{
    protected $signature = 'astroforum:objects-of-the-month
        {--apply : Store the new threads and notify the administrators}';

    protected $description = 'Read the new objects of the month of Astroforum and propose their objects';

    public function handle(AstroforumFeed $feed, ObjectsInText $finder): int
    {
        if (! AstroforumFeed::enabled()) {
            $this->warn('Astroforum is turned off (ASTROFORUM=false).');

            return self::SUCCESS;
        }
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to store the threads and notify the administrators.');
        }

        $threads = $feed->threads();
        if ($threads === null) {
            $this->error('Astroforum did not answer.');

            return self::FAILURE;
        }

        $known = ObjectOfTheMonth::whereIn('thread_id', array_column($threads, 'id'))->pluck('thread_id')->flip();
        $new = [];
        $rows = [];
        foreach ($threads as $thread) {
            if ($known->has($thread['id'])) {
                continue;
            }
            if ($new !== [] || $rows !== []) {
                usleep(max(0, (int) config('services.astroforum.delay', 1000)) * 1000);
            }
            $text = $feed->firstPost($thread['url']);
            if ($text === null) {
                $this->warn('Could not read '.$thread['url'].': the next run tries again.');

                continue;
            }

            [$start, $end] = AstroforumFeed::period($thread['title'], $thread['posted_at']);
            $found = $finder->find($thread['title'], $text);
            $rows[] = [
                $thread['title'],
                $start->toDateString().' – '.$end->toDateString(),
                implode(', ', array_column($found['objects'], 'name')),
                implode(', ', $found['unresolved']),
            ];
            if (! $apply) {
                continue;
            }
            $item = ObjectOfTheMonth::create([
                'thread_id' => $thread['id'],
                'url' => $thread['url'],
                'title' => $thread['title'],
                'starts_on' => $start,
                'ends_on' => $end,
                'candidates' => $found['objects'],
                'unresolved' => $found['unresolved'],
            ]);
            if ($end->gte(today())) {
                $new[] = $item;
            }
        }

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Thread', 'Months', 'Objects found', 'Not in DeepskyLog'], $rows);
        }
        $this->info(($apply ? 'Threads added: ' : 'Threads to add: ').count($rows));

        if ($new !== []) {
            $this->notify($new);
        }

        return self::SUCCESS;
    }

    /**
     * Ask the administrators and database experts to confirm the objects.
     *
     * @param  list<ObjectOfTheMonth>  $items
     */
    private function notify(array $items): void
    {
        $recipients = User::query()
            ->whereHas('teams', fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]))
            ->get();

        foreach ($recipients as $recipient) {
            try {
                $locale = $recipient->language ?: config('app.locale');
                $subject = __('New object of the month on Astroforum', [], $locale);
                $links = collect($items)
                    ->map(fn (ObjectOfTheMonth $item) => '<li><a href="'.e(route('admin.objects-of-the-month.edit', $item)).'">'.e($item->title).'</a></li>')
                    ->implode('');
                $body = Message::sanitizeHtml('<p>'.e(__('Astroforum has a new object of the month. Please check the objects DeepskyLog found in it, so it is shown on the home page:', [], $locale)).'</p><ul>'.$links.'</ul>');

                $message = Message::create([
                    'sender' => 'deepskylog',
                    'receiver' => $recipient->username,
                    'subject' => $subject,
                    'message' => $body,
                    'date' => now(),
                ]);

                if ($recipient->sendMail && $recipient->email) {
                    Mail::to($recipient->email)
                        ->locale($locale)
                        ->send(new MessageReceived('DeepskyLog', $subject, $body, $recipient->name, route('messages.show', $message->id)));
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to notify '.$recipient->username.' of an object of the month: '.$e->getMessage());
            }
        }
    }
}
