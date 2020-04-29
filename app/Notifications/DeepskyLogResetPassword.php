<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class DeepskyLogResetPassword extends ResetPassword
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(_i('Reset Password Notification'))
            ->line(_i('You are receiving this email because we received a password reset request for your account.'))
            ->action(_i('Reset Password'), url(config('app.url').route('password.reset', ['token' => $this->token], false)))
            ->line(_i('This password reset link will expire in %d minutes.', config('auth.passwords.users.expire')))
            ->line(_i('If you did not request a password reset, no further action is required.'));
    }
}
