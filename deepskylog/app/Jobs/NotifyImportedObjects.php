<?php

namespace App\Jobs;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Tell the administrators and database experts which objects an observation
 * import added to the database, so they can check them: a DeepskyLog message
 * from the importing observer (so they can reply), and a mail to those who
 * want their messages by mail.
 */
class NotifyImportedObjects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  string  $importerUsername  The observer whose import added the objects
     * @param  array<string, string>  $objects  Object name => slug
     */
    public function __construct(public string $importerUsername, public array $objects)
    {
    }

    public function handle(): void
    {
        $importer = User::where('username', $this->importerUsername)->first();
        if ($importer === null || $this->objects === []) {
            return;
        }

        $recipients = User::query()
            ->whereHas('teams', fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]))
            ->whereKeyNot($importer->id)
            ->get();

        foreach ($recipients as $recipient) {
            try {
                $locale = $recipient->language ?: config('app.locale');
                $subject = __('New objects added by an observation import', [], $locale);
                $body = Message::sanitizeHtml($this->body($importer, $locale));

                $message = Message::create([
                    'sender' => $importer->username,
                    'receiver' => $recipient->username,
                    'subject' => $subject,
                    'message' => $body,
                    'date' => now(),
                ]);

                if ($recipient->sendMail) {
                    Mail::to($recipient->email)
                        ->locale($locale)
                        ->send(new MessageReceived($importer->name, $subject, $body, $recipient->name, route('messages.show', $message->id)));
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to notify '.$recipient->username.' of imported objects: '.$e->getMessage());
            }
        }
    }

    private function body(User $importer, string $locale): string
    {
        $items = collect($this->objects)
            ->map(fn (string $slug, string $name) => '<li><a href="'.e(route('object.show', $slug)).'">'.e($name).'</a></li>')
            ->implode('');

        return '<p>'.e(trans_choice(
            '{1} The observation import of :observer added 1 object to the database. Please check it:|[2,*] The observation import of :observer added :count objects to the database. Please check them:',
            count($this->objects),
            ['observer' => $importer->name],
            $locale
        )).'</p><ul>'.$items.'</ul>';
    }
}
