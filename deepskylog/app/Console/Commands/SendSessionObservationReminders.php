<?php

namespace App\Console\Commands;

use App\Models\ObservationSession;
use App\Models\User;
use App\Services\SessionObservationReminders;
use Illuminate\Console\Command;

/**
 * Reminds observers of their sessions that ended a day ago or more (at most
 * SessionObservationReminders::MAX_DAYS) without any observation of theirs:
 * one message per observer, listing those sessions with a link to the form of
 * a new observation filled in with the session's date and location. Each
 * session is reminded once (observation_sessions.observation_reminder_sent_at);
 * observers turn it off on the profile page (users.session_reminder).
 * Scheduled daily with --apply.
 */
class SendSessionObservationReminders extends Command
{
    protected $signature = 'sessions:send-observation-reminders
        {--apply : Send the reminders}';

    protected $description = 'Remind observers of their recent sessions without observations';

    public function handle(): int
    {
        $sessions = SessionObservationReminders::due()
            ->orderBy('begindate')
            ->get()
            ->groupBy('observerid');

        $users = User::whereIn('username', $sessions->keys())->get()->keyBy('username');

        $sent = 0;
        foreach ($sessions as $username => $userSessions) {
            $user = $users->get($username);
            if ($user === null) {
                continue;
            }
            if ($this->getOutput()->isVerbose()) {
                $this->line(sprintf('%s: %s', $username, $userSessions->map(fn (ObservationSession $s) => html_entity_decode((string) $s->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'))->implode(', ')));
            }
            if (! $this->option('apply')) {
                continue;
            }
            if (SessionObservationReminders::send($user, $userSessions)) {
                // A mass update: the reminder shows on no page, so neither the
                // response cache nor the cached translations are cleared.
                ObservationSession::whereIn('id', $userSessions->pluck('id'))->update(['observation_reminder_sent_at' => now()]);
                $sent++;
            }
        }

        $count = $sessions->flatten()->count();
        $this->info($this->option('apply')
            ? "Sent {$sent} reminder(s) for {$count} session(s)."
            : "{$count} session(s) of {$sessions->count()} observer(s) to remind (dry run; add --apply to send).");

        return self::SUCCESS;
    }
}
