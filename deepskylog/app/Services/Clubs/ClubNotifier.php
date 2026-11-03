<?php

namespace App\Services\Clubs;

use App\Mail\MessageReceived;
use App\Models\Club;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\ObservingChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * The DeepskyLog messages (and mails, for observers who want those) of the
 * clubs, each in the language of the receiver: an invitation, a request to
 * join to the club administrators, the answer to that request and a new
 * challenge of the club to its members. A message from an observer is not
 * sent to an observer who blocked them.
 */
class ClubNotifier
{
    /** Invite an observer; false when they blocked the inviter. */
    public static function invite(Club $club, User $receiver, User $inviter): bool
    {
        return self::send($receiver, $inviter, fn (string $locale) => [
            __(':name invites you to the club :club', ['name' => $inviter->name, 'club' => $club->name], $locale),
            '<p>'.e(__(':name invites you to become a member of the club :club.', ['name' => $inviter->name, 'club' => $club->name], $locale)).'</p>'
                .self::link($club, $locale)
                .'<p>'.e(__('Accept or decline the invitation on the page of the club.', [], $locale)).'</p>',
        ]);
    }

    /** Tell the club administrators that an observer asks to join. */
    public static function requested(Club $club, User $observer): void
    {
        foreach ($club->admins()->get() as $admin) {
            self::send($admin, $observer, fn (string $locale) => [
                __(':name asks to join the club :club', ['name' => $observer->name, 'club' => $club->name], $locale),
                '<p>'.e(__(':name asks to become a member of the club :club. Let them in or decline on the page of the club.', ['name' => $observer->name, 'club' => $club->name], $locale)).'</p>'
                    .self::link($club, $locale),
            ]);
        }
    }

    /** Tell the observer that a club administrator let them in, or did not. */
    public static function answered(Club $club, User $observer, User $admin, bool $approved): void
    {
        self::send($observer, $admin, fn (string $locale) => [
            $approved
                ? __('You are now a member of the club :club', ['club' => $club->name], $locale)
                : __('Your request to join the club :club was declined', ['club' => $club->name], $locale),
            '<p>'.e($approved
                ? __('Welcome! You are now a member of the club :club.', ['club' => $club->name], $locale)
                : __('Your request to join the club :club was declined.', ['club' => $club->name], $locale)).'</p>'
                .self::link($club, $locale),
        ]);
    }

    /** Tell the members (except the observer who set it) about a new challenge of the club. */
    public static function challenge(Club $club, ObservingChallenge $challenge, User $actor): void
    {
        foreach ($club->members()->where('users.id', '<>', $actor->id)->get() as $member) {
            self::send($member, $actor, function (string $locale) use ($club, $challenge) {
                $previous = app()->getLocale();
                app()->setLocale($locale);
                try {
                    $task  = $challenge->task();
                    $dates = $challenge->starts_on->copy()->locale($locale)->isoFormat('LL').' – '.$challenge->ends_on->copy()->locale($locale)->isoFormat('LL');
                } finally {
                    app()->setLocale($previous);
                }

                return [
                    __('New challenge of the club :club: :challenge', ['club' => $club->name, 'challenge' => $challenge->name], $locale),
                    '<p>'.e(__('The club :club has a new observing challenge.', ['club' => $club->name], $locale)).'</p>'
                        .'<p><strong><a href="'.e(route('challenges.show', $challenge)).'">'.e($challenge->name).'</a></strong><br>'
                        .e($task).'<br>'.e($dates).'</p>',
                ];
            });
        }
    }

    private static function link(Club $club, string $locale): string
    {
        return '<p><strong>'.e(__('Club', [], $locale)).':</strong> <a href="'.e(route('clubs.show', $club)).'">'.e($club->name).'</a></p>';
    }

    /**
     * Send one message from the sender, in the language of the receiver;
     * false when the receiver blocked the sender.
     *
     * @param  callable(string): array{0: string, 1: string}  $compose  the subject and HTML text in a language
     */
    private static function send(User $receiver, User $sender, callable $compose): bool
    {
        if (MessageBlock::blocks($receiver, $sender)) {
            return false;
        }

        $locale           = $receiver->language ?: (string) config('app.locale', 'en');
        [$subject, $body] = $compose($locale);
        $body             = Message::sanitizeHtml($body);

        $message = Message::create([
            'sender'   => $sender->username,
            'receiver' => $receiver->username,
            'subject'  => Message::sanitizeHtml($subject),
            'message'  => $body,
            'date'     => now(),
        ]);

        if ($receiver->sendMail && $receiver->email) {
            Mail::to($receiver->email)
                ->locale($locale)
                ->send(new MessageReceived($sender->name, $subject, $body, $receiver->name, route('messages.show', $message->id)));
        }

        return true;
    }
}
