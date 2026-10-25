<?php

namespace App\Jobs;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * The DeepskyLog message (and mail, if the observer wants those) that a
 * PDF or zip file of a QueuedPdfJob is ready or failed, in the language it
 * was asked for in. Queued QueuedPdfJob::NOTIFY_DELAY_SECONDS after the job
 * ended, and not sent when a page showed the observer the result meanwhile.
 */
class NotifyQueuedPdfFinished implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  class-string<QueuedPdfJob>  $kind
     */
    public function __construct(
        public string $kind,
        public string $token,
        public int $userId,
        public string $locale,
        public bool $ready,
    ) {
    }

    public function handle(): void
    {
        $kind = $this->kind;
        if (! is_subclass_of($kind, QueuedPdfJob::class) || QueuedPdfJob::seen($this->token)) {
            return;
        }

        // Without a status the PDF was removed or replaced meanwhile.
        $status = Cache::get($kind::statusKey($this->token));
        $user = User::find($this->userId);
        if (! is_array($status) || $user === null || (int) ($status['user_id'] ?? 0) !== $this->userId) {
            return;
        }

        $locale = $this->locale;
        $label = trim((string) ($status['label'] ?? '')) ?: (string) ($status['filename'] ?? '');
        $name = $kind::kindLabel($locale) . ($label !== '' ? ' - ' . $label : '');
        $overview = '<a href="' . e(route('downloads.mine')) . '">' . e(__('Your PDFs and zip files', [], $locale)) . '</a>';

        if ($this->ready) {
            $subject = __('Your file is ready: :name', ['name' => $name], $locale);
            $body = '<p>' . e(__('The file ":name" you asked for is ready. You can download it until :date.', [
                'name' => $name,
                'date' => $kind::expiresAt($status)?->locale($locale)->isoFormat('LLL') ?? '',
            ], $locale)) . '</p><p>';
            if (($url = $kind::downloadUrl($this->token)) !== null) {
                $body .= '<a href="' . e($url) . '">' . e(__('Download', [], $locale)) . '</a> - ';
            }
            $body .= $overview . '</p>';
        } else {
            $subject = __('Your file could not be made: :name', ['name' => $name], $locale);
            $body = '<p>' . e(__('The file ":name" you asked for could not be made. You can ask for it again; if that fails too, please contact the administrators.', ['name' => $name], $locale))
                . '</p><p>' . $overview . '</p>';
        }

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
    }
}
