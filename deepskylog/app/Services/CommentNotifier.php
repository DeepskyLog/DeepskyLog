<?php

namespace App\Services;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Tells the owner of an observation or observing list about a new comment,
 * with a DeepskyLog message (and a mail when they asked for one) in the
 * owner's language. Nothing is sent for a comment of the owners themselves
 * or of an observer they blocked.
 */
class CommentNotifier
{
    private const PREVIEW_LENGTH = 300;

    /**
     * @param  string  $subjectKey  translation key of the subject
     * @param  string  $introKey  translation key of the first sentence, gets :user besides $replace
     * @param  array<string, string>  $replace  the placeholders of both keys
     * @param  string  $linkKey  translation key of the link text
     */
    public static function notify(
        User $owner,
        User $commenter,
        string $comment,
        string $subjectKey,
        string $introKey,
        array $replace,
        string $url,
        string $linkKey,
    ): void {
        if ((int) $owner->id === (int) $commenter->id || empty($owner->username)) {
            return;
        }

        try {
            if (MessageBlock::blocks($owner, $commenter)) {
                return;
            }

            $locale = $owner->language ?: config('app.locale');
            $subject = Message::sanitizeHtml(__($subjectKey, $replace, $locale));

            $preview = trim(strip_tags($comment));
            if (mb_strlen($preview) > self::PREVIEW_LENGTH) {
                $preview = mb_substr($preview, 0, self::PREVIEW_LENGTH) . '...';
            }

            $body = '<p>' . e(__($introKey, ['user' => $commenter->username] + $replace, $locale)) . '</p>';
            if ($preview !== '') {
                $body .= '<p><strong>' . e(__('Comment', [], $locale)) . ':</strong> ' . e($preview) . '</p>';
            }
            $body .= '<p><a href="' . e($url) . '">' . e(__($linkKey, [], $locale)) . '</a></p>';
            $body = Message::sanitizeHtml($body);

            $message = Message::create([
                'sender' => $commenter->username ?: 'deepskylog',
                'receiver' => $owner->username,
                'subject' => $subject,
                'message' => $body,
                'date' => now(),
            ]);

            if ($owner->sendMail) {
                Mail::to($owner->email)
                    ->locale($locale)
                    ->send(new MessageReceived($commenter->name, $subject, $body, $owner->name, route('messages.show', $message->id)));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send comment notification', [
                'owner_id' => $owner->id,
                'commenter_id' => $commenter->id,
                'subject' => $subjectKey,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
