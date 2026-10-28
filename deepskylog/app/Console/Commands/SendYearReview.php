<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\User;
use App\Services\ObserverYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sends every observer with observations in the year that just ended a
 * DeepskyLog message (no mail) with a few numbers and the link to their year
 * in review. Each observer gets it once a year (users.year_review_sent_year);
 * observers turn it off on the profile page (users.year_review). Scheduled
 * daily in the first week of January with --apply.
 */
class SendYearReview extends Command
{
    protected $signature = 'observers:send-year-review
        {--apply : Send the messages}
        {--year= : The year to review (default: last year)}';

    protected $description = 'Send observers the message with the review of the year that ended';

    public function handle(): int
    {
        $year = (int) ($this->option('year') ?: now()->year - 1);
        if (! ObserverYear::isShown($year)) {
            $this->error("{$year} has not ended yet.");

            return self::FAILURE;
        }

        $users = User::query()
            ->where('year_review', true)
            ->where(fn ($query) => $query->whereNull('year_review_sent_year')->orWhere('year_review_sent_year', '<', $year))
            ->whereIn('username', DB::table('observations')
                ->whereBetween('observed_on', [$year . '-01-01', $year . '-12-31'])
                ->select('observer_username'))
            ->orderBy('id')
            ->get();

        $sent = 0;
        foreach ($users as $user) {
            if ($this->getOutput()->isVerbose()) {
                $this->line($user->username);
            }
            if (! $this->option('apply')) {
                continue;
            }
            if ($this->send($user, $year)) {
                $user->forceFill(['year_review_sent_year' => $year])->save();
                $sent++;
            }
        }

        $this->info($this->option('apply')
            ? "Sent the review of {$year} to {$sent} observer(s)."
            : "{$users->count()} observer(s) to send the review of {$year} (dry run; add --apply to send).");

        return self::SUCCESS;
    }

    private function send(User $user, int $year): bool
    {
        try {
            $locale  = $user->language ?: config('app.locale');
            $summary = (new ObserverYear($user, $year))->summary();
            $totals  = $summary['totals'];

            $subject = __('Your year :year on DeepskyLog', ['year' => $year], $locale);

            $body = '<p>' . e(__('This is what you observed in :year:', ['year' => $year], $locale)) . '</p><ul>'
                . '<li>' . e(trans_choice(':count observation|:count observations', $totals['observations'], ['count' => $totals['observations']], $locale)) . '</li>'
                . '<li>' . e(trans_choice(':count night|:count nights', $totals['nights'], ['count' => $totals['nights']], $locale)) . '</li>'
                . '<li>' . e(trans_choice(':count different object|:count different objects', $totals['objects'], ['count' => $totals['objects']], $locale)) . '</li>';
            if ($summary['new_objects']['count'] > 0) {
                $body .= '<li>' . e(trans_choice(':count object seen for the first time|:count objects seen for the first time', $summary['new_objects']['count'], ['count' => $summary['new_objects']['count']], $locale)) . '</li>';
            }
            if ($totals['drawings'] > 0) {
                $body .= '<li>' . e(trans_choice(':count drawing|:count drawings', $totals['drawings'], ['count' => $totals['drawings']], $locale)) . '</li>';
            }
            $body .= '</ul><p><a href="' . e(ObserverYear::url($user, $year)) . '">' . e(__('See your year in review', [], $locale)) . '</a></p>';
            $body .= '<p>' . e(__('You can turn this message off in your profile settings.', [], $locale)) . '</p>';

            Message::create([
                'sender'   => 'deepskylog',
                'receiver' => $user->username,
                'subject'  => Message::sanitizeHtml($subject),
                'message'  => Message::sanitizeHtml($body),
                'date'     => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to send the year in review', ['user_id' => $user->id, 'year' => $year, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
