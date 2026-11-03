<?php

namespace App\Services;

use App\Mail\MessageReceived;
use App\Models\DeepskyObject;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Tells the administrators and database experts that an observer reported an
 * error in an object (DeepskyObject::reportError()): one DeepskyLog message
 * each (mail if sendMail) in their language, with the report and links to the
 * object, its edit page, the reviews and a reply to the observer. Not to the
 * reporter.
 */
class ObjectErrorReportNotifier
{
    public static function notify(DeepskyObject $object, string $kind, string $note, User $reporter): void
    {
        $recipients = User::query()
            ->whereHas('teams', fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]))
            ->where('id', '!=', $reporter->id)
            ->get();

        $slug = $object->slug ?: $object->name;

        foreach ($recipients as $recipient) {
            try {
                $locale  = $recipient->language ?: config('app.locale');
                $subject = __('Error reported in :name', ['name' => $object->name], $locale);
                $body    = Message::sanitizeHtml(
                    '<p>' . e(__(':observer reports an error in :name (:kind):', [
                        'observer' => $reporter->name,
                        'name'     => $object->name,
                        'kind'     => __(DeepskyObject::ERROR_KINDS[$kind] ?? DeepskyObject::ERROR_KINDS['other'], [], $locale),
                    ], $locale)) . '</p>'
                    . '<blockquote>' . e(trim($note)) . '</blockquote>'
                    . '<p><a href="' . e(route('object.show', ['slug' => $slug])) . '">' . e($object->name) . '</a>'
                    . ' · <a href="' . e(route('object.edit', ['slug' => $slug])) . '">' . e(__('Edit Object', [], $locale)) . '</a>'
                    . ' · <a href="' . e(route('admin.object-reviews')) . '">' . e(__('All reviews', [], $locale)) . '</a>'
                    . ' · <a href="' . e(route('messages.create', ['to' => $reporter->username])) . '">' . e(__('Send a message to :observer', ['observer' => $reporter->name], $locale)) . '</a></p>'
                );

                $message = Message::create([
                    'sender'   => 'deepskylog',
                    'receiver' => $recipient->username,
                    'subject'  => $subject,
                    'message'  => $body,
                    'date'     => now(),
                ]);

                if ($recipient->sendMail && $recipient->email) {
                    Mail::to($recipient->email)
                        ->locale($locale)
                        ->send(new MessageReceived('DeepskyLog', $subject, $body, $recipient->name, route('messages.show', $message->id)));
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to tell ' . $recipient->username . ' of an error reported in ' . $object->name . ': ' . $e->getMessage());
            }
        }
    }
}
