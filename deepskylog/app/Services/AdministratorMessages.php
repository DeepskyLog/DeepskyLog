<?php

namespace App\Services;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one DeepskyLog message (and a mail if sendMail) to every member of
 * the Administrators team, each in their own language.
 */
class AdministratorMessages
{
    /** @return Collection<int, User> */
    public static function recipients(): Collection
    {
        return User::query()
            ->whereHas('teams', fn ($q) => $q->where('teams.id', Team::ADMINISTRATORS))
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  callable(string): string  $subject  the subject in the given locale
     * @param  callable(string): string  $body  the HTML of the message in the given locale
     * @return int the number of administrators the message was sent to
     */
    public static function send(callable $subject, callable $body): int
    {
        $sent = 0;
        foreach (self::recipients() as $recipient) {
            try {
                $locale = $recipient->language ?: config('app.locale');
                $title = $subject($locale);
                $html = Message::sanitizeHtml($body($locale));

                $message = Message::create([
                    'sender' => 'deepskylog',
                    'receiver' => $recipient->username,
                    'subject' => $title,
                    'message' => $html,
                    'date' => now(),
                ]);

                if ($recipient->sendMail && $recipient->email) {
                    Mail::to($recipient->email)
                        ->locale($locale)
                        ->send(new MessageReceived('DeepskyLog', $title, $html, $recipient->name, route('messages.show', $message->id)));
                }
                $sent++;
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $sent;
    }

    /**
     * An HTML list of accounts, one per line: the username (linked to the
     * observer page when the account still exists), then name, e-mail
     * address, country and registration date. Message HTML keeps no tables.
     *
     * @param  iterable<array{username: string, name: ?string, email: ?string, country: ?string, registered: ?string, url: ?string}>  $accounts
     */
    public static function accountList(iterable $accounts, string $locale): string
    {
        $items = collect($accounts)->map(function (array $a) use ($locale) {
            $username = $a['url']
                ? '<a href="'.e($a['url']).'">'.e($a['username']).'</a>'
                : e($a['username']);
            $details = collect([
                $a['name'] ?? null,
                $a['email'] ?? null,
                $a['country'] ?? null,
                filled($a['registered'] ?? null) ? __('registered :date', ['date' => $a['registered']], $locale) : null,
            ])->filter(fn ($v) => filled($v))->map(fn ($v) => e($v))->implode(', ');

            return '<li>'.$username.($details !== '' ? ' ('.$details.')' : '').'</li>';
        })->implode('');

        return '<ul>'.$items.'</ul>';
    }

    /** The row of accountList() for a user. */
    public static function accountRow(User $user, bool $link = true): array
    {
        return [
            'username' => $user->username,
            'name' => html_entity_decode((string) $user->name),
            'email' => $user->email,
            'country' => $user->country,
            'registered' => $user->created_at?->format('Y-m-d H:i'),
            'url' => $link && $user->slug ? route('observer.show', $user->slug) : null,
        ];
    }
}
