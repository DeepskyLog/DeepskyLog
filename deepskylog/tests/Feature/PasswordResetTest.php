<?php

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
