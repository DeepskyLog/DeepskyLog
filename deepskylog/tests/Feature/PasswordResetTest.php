<?php

use App\Listeners\EmbedMailLogo;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

it('reset password link screen can be rendered', function () {
    if (! Features::enabled(Features::resetPasswords())) {
        $this->markTestSkipped('Password updates are not enabled.');
    }

    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

it('reset password link can be requested', function () {
    if (! Features::enabled(Features::resetPasswords())) {
        $this->markTestSkipped('Password updates are not enabled.');
    }

    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('reset password screen can be rendered', function () {
    if (! Features::enabled(Features::resetPasswords())) {
        $this->markTestSkipped('Password updates are not enabled.');
    }

    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

it('password can be reset with valid token', function () {
    if (! Features::enabled(Features::resetPasswords())) {
        $this->markTestSkipped('Password updates are not enabled.');
    }

    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function (object $notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });
});

it('sends the reset password mail in the language of the site', function () {
    if (! Features::enabled(Features::resetPasswords())) {
        $this->markTestSkipped('Password updates are not enabled.');
    }

    $user = User::factory()->create();

    $this->withSession(['locale' => 'nl'])->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $messages = app('mailer')->getSymfonyTransport()->messages();
    expect($messages)->toHaveCount(1);

    $email = $messages->first()->getOriginalMessage();
    $html = $email->getHtmlBody();

    expect($email->getSubject())->toBe('Je wachtwoord resetten')
        ->and($email->getFrom()[0]->getAddress())->toBe(config('mail.from.address'))
        ->and($email->getReplyTo()[0]->getAddress())->toBe(config('mail.reply_to.address'))
        ->and($html)->toContain('Met vriendelijke groeten,')
        ->and($html)->toContain('src="cid:'.EmbedMailLogo::CID.'"')
        ->and($html)->not->toContain('logo-mail.png')
        ->and(collect($email->getAttachments())->map->getName()->all())->toContain(EmbedMailLogo::CID);
});
