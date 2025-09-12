<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSessionInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $sessionId;

    public array $recipients;

    public string $senderUsername;

    /**
     * Create a new job instance.
     *
     * @param  array  $recipients  array of usernames to send invitation to
     * @param  string  $senderUsername  username string of sender
     */
    public function __construct(int $sessionId, array $recipients, string $senderUsername)
    {
        $this->sessionId = $sessionId;
        $this->recipients = array_values(array_unique($recipients));
        $this->senderUsername = $senderUsername;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $session = ObservationSession::find($this->sessionId);
        if (! $session) {
            return;
        }

        // Build invitation text
        $sessionName = $session->name;
        $location = '';
        try {
            $location = $session->locationid ? \App\Models\Location::find($session->locationid)?->name ?? '' : '';
        } catch (\Throwable $e) {
            // ignore
        }

        $begin = $session->begindate ?: '';
        $end = $session->enddate ?: '';

        // Build observer list: include primary observer and otherObservers
        $observers = $session->otherObservers();
        if (! empty($session->observerid) && ! in_array($session->observerid, $observers, true)) {
            array_unshift($observers, $session->observerid);
        }

        $observerList = implode(', ', $observers);

        $subject = __('Invitation: :name', ['name' => $sessionName]);

        $body = '';
        $body .= '<p>'.e(__('You have been invited to a session.')).'</p>';
        $body .= '<p><strong>'.e(__('Session')).':</strong> '.e($sessionName).'</p>';
        if ($observerList !== '') {
            $body .= '<p><strong>'.e(__('Observers')).':</strong> '.e($observerList).'</p>';
        }
        if ($location !== '') {
            $body .= '<p><strong>'.e(__('Location')).':</strong> '.e($location).'</p>';
        }
        if ($begin !== '') {
            $body .= '<p><strong>'.e(__('Begin')).':</strong> '.e($begin).'</p>';
        }
        if ($end !== '') {
            $body .= '<p><strong>'.e(__('End')).':</strong> '.e($end).'</p>';
        }

        // Add link to recipient's sessions page so they can view/manage their sessions
        try {
            // We'll include a placeholder; when composing per-recipient messages below we'll replace or append the correct URL for each recipient.
            $body .= '<p>'.__('View your sessions here:').' {SESSIONS_LINK}</p>';
        } catch (\Throwable $e) {
            // ignore URL building failures
        }

        $safeSubject = Message::sanitizeHtml($subject);
        $safeBody = Message::sanitizeHtml($body);

        foreach ($this->recipients as $username) {
            try {
                // Build per-recipient body with correct sessions link (use slug when available)
                $recipientUser = User::where('username', $username)->first();
                $recipientSlugOrName = $recipientUser ? ($recipientUser->slug ?? $recipientUser->username) : $username;
                $sessionsUrl = rtrim(config('app.url') ?: url('/'), '/').'/sessions/'.rawurlencode($recipientSlugOrName);

                $personalBody = str_replace('{SESSIONS_LINK}', '<a href="'.e($sessionsUrl).'">'.e(__('My sessions')).'</a>', $body);

                // Create internal message row
                Message::create([
                    'sender' => $this->senderUsername,
                    'receiver' => $username,
                    'subject' => $safeSubject,
                    'message' => Message::sanitizeHtml($personalBody),
                    'date' => now(),
                ]);

                // Optionally send email notification if user has opted-in
                if ($recipientUser && $recipientUser->sendMail) {
                    // Use the existing Mailable to notify via email; MessageReceived expects senderName, subject, messageHtml, recipientName
                    $senderName = User::where('username', $this->senderUsername)->value('name') ?? 'DeepskyLog';
                    Mail::to($recipientUser->email)->send(new \App\Mail\MessageReceived($senderName, $subject, Message::sanitizeHtml($personalBody), $recipientUser->name));
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to send session invitation to '.$username.': '.$e->getMessage());
            }
        }
    }
}
