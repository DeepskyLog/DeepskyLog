<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The route to the new password request.
     *
     * @return string The route to the new password request
     */
    protected function passwordRequestRoute()
    {
        return route('password.request');
    }

    /**
     * The route to the email password request.
     *
     * @return string The route to the new password request
     */
    protected function passwordEmailGetRoute()
    {
        return route('password.email');
    }

    /**
     * The route to the email password request.
     *
     * @return string The route to the email password request
     */
    protected function passwordEmailPostRoute()
    {
        return route('password.email');
    }

    /**
     * Check if a user can view the email password form.
     *
     * @return void
     */
    public function testUserCanViewAnEmailPasswordForm()
    {
        $response = $this->get($this->passwordRequestRoute());

        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.email');
    }

    /**
     * Check if a user can view the email password form when already authenticated.
     *
     * @return void
     */
    public function testUserCannotViewAnEmailPasswordFormWhenAuthenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get($this->passwordRequestRoute());

        $response->assertStatus(302);
        $response->assertRedirect('/home');
    }

    /**
     * Check if a user receives an email with a password reset link.
     *
     * @return void
     */
    public function testUserReceivesAnEmailWithAPasswordResetLink()
    {
        Notification::fake();
        $user = factory(User::class)->create(
            ['email' => 'john@example.com']
        );

        $response = $this->post(
            $this->passwordEmailPostRoute(),
            ['email' => 'john@example.com']
        );

        $this->assertNotNull($token = DB::table('password_resets')->first());
        Notification::assertSentTo(
            $user,
            \App\Notifications\DeepskyLogResetPassword::class,
            function ($notification, $channels) use ($token) {
                return Hash::check($notification->token, $token->token) === true;
            }
        );
    }

    /**
     * Check that an unregistered user does not receive an email.
     *
     * @return void
     */
    public function testUserDoesNotReceiveEmailWhenNotRegistered()
    {
        Notification::fake();

        $response = $this->from(
            $this->passwordEmailGetRoute()
        )->post(
            $this->passwordEmailPostRoute(),
            ['email' => 'nobody@example.com']
        );

        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
        Notification::assertNotSentTo(
            factory(User::class)->make(['email' => 'nobody@example.com']),
            ResetPassword::class
        );
    }

    /**
     * Check that an email is required.
     *
     * @return void
     */
    public function testEmailIsRequired()
    {
        $response = $this->from(
            $this->passwordEmailGetRoute()
        )->post($this->passwordEmailPostRoute(), []);

        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
    }

    /**
     * Check that the email is a valid email.
     *
     * @return void
     */
    public function testEmailIsAValidEmail()
    {
        $response = $this->from($this->passwordEmailGetRoute())->post(
            $this->passwordEmailPostRoute(),
            ['email' => 'invalid-email']
        );

        $response->assertRedirect($this->passwordEmailGetRoute());
        $response->assertSessionHasErrors('email');
    }
}
