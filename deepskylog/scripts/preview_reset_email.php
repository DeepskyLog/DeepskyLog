<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

$token = 'TEST-TOKEN-123';
$not = new ResetPassword($token);

// Normally toMail receives $notifiable; we'll make a dummy notifiable with email and routeNotificationFor
$notifiable = new class {
    public $email = 'dev@example.com';
    public $name = 'Dev User';
    public function routeNotificationFor($channel)
    {
        return $this->email;
    }
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }
};

$mail = $not->toMail($notifiable);

// Use the Markdown renderer to convert the MailMessage into HTML
/** @var \Illuminate\Mail\Markdown $markdown */
$markdown = app(\Illuminate\Mail\Markdown::class);
echo $markdown->render('vendor.mail.html.message', ['slot' => $markdown->renderText($mail->introLines ? implode("\n\n", $mail->introLines) : '') . '\n\n' . ($mail->actionText ? '[ ' . $mail->actionText . ' ](' . $mail->actionUrl . ')' : '')]);
