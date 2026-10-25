<?php

namespace App\Services\StarParties;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * The DeepskyLog messages (and mails, for observers who want those) of the
 * starparties (#315), each in the language of the receiver: an invitation,
 * an answer to the organiser, a change of the dates or site, a cancellation
 * and the reminder before the first night. A message from an observer is not
 * sent to an observer who blocked them.
 */
class StarPartyNotifier
{
    /** Invite an observer; false when they blocked the inviter. */
    public static function invite(StarParty $party, User $receiver, User $inviter): bool
    {
        return self::send($receiver, $inviter, fn (string $locale) => [
            __(':name invites you to the starparty :party', ['name' => $inviter->name, 'party' => $party->name], $locale),
            '<p>'.e(__(':name invites you to the starparty :party.', ['name' => $inviter->name, 'party' => $party->name], $locale)).'</p>'
                .self::summary($party, $locale)
                .'<p>'.e(__('Let the organiser know whether you come on the page of the starparty.', [], $locale)).'</p>',
        ]);
    }

    /** Tell the organiser that an observer comes, maybe comes or no longer comes. */
    public static function answered(StarParty $party, User $participant, string $status): void
    {
        $organiser = $party->organiser;
        if (! $organiser || (int) $organiser->id === (int) $participant->id) {
            return;
        }

        self::send($organiser, $participant, function (string $locale) use ($party, $participant, $status) {
            $line = match ($status) {
                StarPartyParticipant::GOING => __(':name comes to your starparty :party.', ['name' => $participant->name, 'party' => $party->name], $locale),
                StarPartyParticipant::MAYBE => __(':name may come to your starparty :party.', ['name' => $participant->name, 'party' => $party->name], $locale),
                default                     => __(':name does not come to your starparty :party.', ['name' => $participant->name, 'party' => $party->name], $locale),
            };

            return [$line, '<p>'.e($line).'</p>'.self::summary($party, $locale)];
        });
    }

    /**
     * Tell the participants (except the observer who made the change) that
     * the dates or the site changed.
     *
     * @param  list<string>  $changes  'dates' and/or 'site'
     */
    public static function changed(StarParty $party, User $actor, array $changes): void
    {
        foreach (self::participantsToTell($party, $actor) as $receiver) {
            self::send($receiver, $actor, function (string $locale) use ($party, $changes) {
                $lines = [];
                if (in_array('dates', $changes, true)) {
                    $lines[] = __('The starparty now takes place on :dates.', ['dates' => self::dates($party, $locale)], $locale);
                }
                if (in_array('site', $changes, true)) {
                    $lines[] = __('The starparty now takes place at :site.', ['site' => $party->location_name], $locale);
                }

                return [
                    __('The starparty :party has changed', ['party' => $party->name], $locale),
                    '<p>'.e(implode(' ', $lines)).'</p>'.self::summary($party, $locale),
                ];
            });
        }
    }

    /** Tell the participants that the starparty is cancelled, or takes place after all. */
    public static function cancelled(StarParty $party, User $actor): void
    {
        $cancelled = $party->isCancelled();
        foreach (self::participantsToTell($party, $actor) as $receiver) {
            self::send($receiver, $actor, fn (string $locale) => [
                $cancelled
                    ? __('The starparty :party is cancelled', ['party' => $party->name], $locale)
                    : __('The starparty :party takes place after all', ['party' => $party->name], $locale),
                '<p>'.e($cancelled
                    ? __('The starparty :party on :dates is cancelled.', ['party' => $party->name, 'dates' => self::dates($party, $locale)], $locale)
                    : __('The starparty :party on :dates takes place after all.', ['party' => $party->name, 'dates' => self::dates($party, $locale)], $locale)).'</p>'
                    .self::summary($party, $locale),
            ]);
        }
    }

    /** The reminder before the first night, with the nights at the site. */
    public static function remind(StarParty $party, User $receiver): void
    {
        self::send($receiver, null, function (string $locale) use ($party) {
            $nights = '';
            foreach (StarPartyNights::for($party) as $night) {
                $date = \Carbon\Carbon::parse($night['night'])->locale($locale)->isoFormat('dddd LL');
                if ($night['dark'] === null) {
                    $text = __('It does not get dark.', [], $locale);
                } else {
                    $text = __('Dark :from–:until', $night['dark'], $locale);
                    $text .= ', '.($night['moon'] ? __('Moon :fraction%, up :from–:until', $night['moon'], $locale) : __('No Moon', [], $locale));
                }
                $nights .= '<li>'.e($date).': '.e($text).'</li>';
            }

            return [
                __('Reminder: the starparty :party starts on :date', ['party' => $party->name, 'date' => $party->start_date->copy()->locale($locale)->isoFormat('LL')], $locale),
                '<p>'.e(__('The starparty :party starts soon. Clear skies!', ['party' => $party->name], $locale)).'</p>'
                    .self::summary($party, $locale)
                    .'<p>'.e(__('The nights at the site (local time):', [], $locale)).'</p><ul>'.$nights.'</ul>',
            ];
        });
    }

    /**
     * The observers who said they come or may come, and those invited, but
     * not the observer who made the change.
     *
     * @return list<User>
     */
    private static function participantsToTell(StarParty $party, User $actor): array
    {
        $users = $party->participants()
            ->whereIn('status', [StarPartyParticipant::GOING, StarPartyParticipant::MAYBE, StarPartyParticipant::INVITED])
            ->where('user_id', '<>', $actor->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->all();
        if ($party->organiser && (int) $party->user_id !== (int) $actor->id) {
            $users[] = $party->organiser;
        }

        return array_values(collect($users)->unique('id')->all());
    }

    /** The dates, the site and the organiser of the starparty, with a link to its page. */
    private static function summary(StarParty $party, string $locale): string
    {
        $url  = route('star-parties.show', $party);
        $html = '<p><strong>'.e(__('Starparty', [], $locale)).':</strong> <a href="'.e($url).'">'.e($party->name).'</a><br>'
            .'<strong>'.e(__('Nights', [], $locale)).':</strong> '.e(self::dates($party, $locale)).'<br>'
            .'<strong>'.e(__('Location', [], $locale)).':</strong> '.e($party->location_name.($party->country ? ' ('.$party->country.')' : ''));
        if ($party->venue) {
            $html .= '<br>'.e($party->venue);
        }
        if ($party->organiser) {
            $html .= '<br><strong>'.e(__('Organiser', [], $locale)).':</strong> '.e($party->organiser->name);
        }

        return $html.'</p>';
    }

    private static function dates(StarParty $party, string $locale): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);
        try {
            return $party->dateRange();
        } finally {
            app()->setLocale($previous);
        }
    }

    /**
     * Send one message, from the sender (DeepskyLog itself when null), in the
     * language of the receiver; false when the receiver blocked the sender.
     *
     * @param  callable(string): array{0: string, 1: string}  $compose  the subject and HTML text in a language
     */
    private static function send(User $receiver, ?User $sender, callable $compose): bool
    {
        if ($sender && MessageBlock::blocks($receiver, $sender)) {
            return false;
        }

        $locale           = $receiver->language ?: (string) config('app.locale', 'en');
        [$subject, $body] = $compose($locale);
        $body             = Message::sanitizeHtml($body);

        $message = Message::create([
            'sender'   => $sender?->username ?? 'deepskylog',
            'receiver' => $receiver->username,
            'subject'  => Message::sanitizeHtml($subject),
            'message'  => $body,
            'date'     => now(),
        ]);

        if ($receiver->sendMail && $receiver->email) {
            Mail::to($receiver->email)
                ->locale($locale)
                ->send(new MessageReceived($sender?->name ?? 'DeepskyLog', $subject, $body, $receiver->name, route('messages.show', $message->id)));
        }

        return true;
    }
}
