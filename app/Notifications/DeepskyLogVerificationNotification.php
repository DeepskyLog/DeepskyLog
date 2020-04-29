<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class DeepskyLogVerificationNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable);
        }

        return (new MailMessage())
            ->subject(_i('Verify Email Address'))
            ->line(_i('Please click the button below to verify your email address.'))
            ->action(
                _i('Verify Email Address'),
                $this->verificationUrl($notifiable)
            )
            ->line(_i('If you did not create an account, no further action is required.'));
    }
}
