<?php

namespace App\Console\Commands;

use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Services\StarParties\StarPartyNotifier;
use Illuminate\Console\Command;

/**
 * Reminds the observers who come or may come to a starparty (#315), with the
 * nights at the site, StarParty::REMINDER_DAYS days before the first night.
 * Each starparty gets one reminder (star_parties.reminder_sent_at, cleared
 * when the dates change); one created closer to its start gets it at once.
 */
class SendStarPartyReminders extends Command
{
    protected $signature = 'star-parties:send-reminders
        {--apply : Send the reminders}';

    protected $description = 'Remind the participants of the starparties that start in the coming days';

    public function handle(): int
    {
        $parties = StarParty::whereNull('cancelled_at')
            ->whereNull('reminder_sent_at')
            ->whereBetween('start_date', [today()->toDateString(), today()->addDays(StarParty::REMINDER_DAYS)->toDateString()])
            ->with(['participants' => fn ($q) => $q->whereIn('status', [StarPartyParticipant::GOING, StarPartyParticipant::MAYBE]), 'participants.user'])
            ->orderBy('start_date')
            ->get();

        $sent = 0;
        foreach ($parties as $party) {
            $receivers = $party->participants->pluck('user')->filter();
            if ($this->getOutput()->isVerbose()) {
                $this->line(sprintf('%s (%s): %s', $party->name, $party->start_date->toDateString(), $receivers->pluck('username')->implode(', ') ?: '-'));
            }
            if (! $this->option('apply')) {
                continue;
            }
            foreach ($receivers as $receiver) {
                StarPartyNotifier::remind($party, $receiver);
                $sent++;
            }
            $party->forceFill(['reminder_sent_at' => now()])->save();
        }

        $this->info($this->option('apply')
            ? "Sent {$sent} reminder(s) for {$parties->count()} starparty(ies)."
            : "{$parties->count()} starparty(ies) to remind (dry run; add --apply to send).");

        return self::SUCCESS;
    }
}
