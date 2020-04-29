<?php

namespace Tests\Feature\Auth;

use App\User;
use Carbon\Traits\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $verificationVerifyRouteName = 'verification.verify';

    /**
     * The redirect after a succesfull verification.
     *
     * @return string /home
     */
    protected function successfulVerificationRoute()
    {
        return '/home';
    }

    /**
     * The redirect after a verification notice.
     *
     * @return string The verification notice route
     */
    protected function verificationNoticeRoute()
    {
        return route('verification.notice');
    }

    /**
     * The redirect after a valid verification.
     *
     * @param $user The User
     *
     * @return URL The route after a valid verification
     */
    protected function validVerificationVerifyRoute($user)
    {
        return URL::signedRoute(
            $this->verificationVerifyRouteName,
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    /**
     * The redirect after an invalid verification.
     *
     * @param $user The User
     *
     * @return route The route after an invalid verification
     */
    protected function invalidVerificationVerifyRoute($user)
    {
        return route(
            $this->verificationVerifyRouteName,
            [
                'id' => $user->id,
                'hash' => 'invalid-hash',
            ]
        );
    }

    /**
     * The redirect to resend a verification.
     *
     * @return route The route to resend a verification
     */
    protected function verificationResendRoute()
    {
        return route('verification.resend');
    }

    /**
     * The route to log in.
     *
     * @return route The route to log in
     */
    protected function loginRoute()
    {
        return route('login');
    }

    /**
     * Check that a guest can not see the Verification notice.
     */
    public function testGuestCannotSeeTheVerificationNotice()
    {
        $response = $this->get($this->verificationNoticeRoute());

        $response->assertRedirect($this->loginRoute());
    }

    /**
     * Check that a user can see the Verification notice when the user is not
     * verified.
     */
    public function testUserSeesTheVerificationNoticeWhenNotVerified()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => null]
        );

        $response = $this->actingAs($user)->get($this->verificationNoticeRoute());

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify');
    }

    /**
     * Check that a verified user is redirected to the homepage when visiting the
     * verification page.
     */
    public function testVerifiedUserRedirectedHomeWhenVisitingVerificationNotice()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => now()]
        );

        $response = $this->actingAs($user)->get($this->verificationNoticeRoute());

        $response->assertRedirect($this->successfulVerificationRoute());
    }

    /**
     * Check that a guest can not see the Verification page.
     */
    public function testGuestCannotSeeTheVerificationVerifyRoute()
    {
        $user = factory(User::class)->create(
            [
                'id' => 1,
                'email_verified_at' => null,
            ]
        );

        $response = $this->get($this->validVerificationVerifyRoute($user));

        $response->assertRedirect($this->loginRoute());
    }

    /**
     * Check that a user can not Verify other users.
     */
    public function testUserCannotVerifyOthers()
    {
        $user = factory(User::class)->create(
            [
                'id' => 1,
                'email_verified_at' => null,
            ]
        );

        $user2 = factory(User::class)->create(
            ['id' => 2, 'email_verified_at' => null]
        );

        $response = $this->actingAs($user)->get(
            $this->validVerificationVerifyRoute($user2)
        );

        $response->assertForbidden();
        $this->assertFalse($user2->fresh()->hasVerifiedEmail());
    }

    /**
     * Check that a verified user is redirected correctly.
     */
    public function testUserIsRedirectedToCorrectRouteWhenAlreadyVerified()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => now()]
        );

        $response = $this->actingAs($user)->get(
            $this->validVerificationVerifyRoute($user)
        );

        $response->assertRedirect($this->successfulVerificationRoute());
    }

    /**
     * Check that forbidden is returned when signature is invalid.
     *
     * @test
     */
    public function forbiddenIsReturnedWhenSignatureIsInvalidInVerificationVerify()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => now()]
        );

        $response = $this->actingAs($user)->get(
            $this->invalidVerificationVerifyRoute($user)
        );

        $response->assertStatus(403);
    }

    /**
     * Check that users can verify themselves.
     */
    public function testUserCanVerifyThemselves()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => null]
        );

        $response = $this->actingAs($user)->get(
            $this->validVerificationVerifyRoute($user)
        );

        $response->assertRedirect($this->successfulVerificationRoute());
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /**
     * Check that a guest can not resend a verification email.
     */
    public function testGuestCannotResendAVerificationEmail()
    {
        $response = $this->post($this->verificationResendRoute());

        $response->assertRedirect($this->loginRoute());
    }

    /**
     * Check that a user is correctly redirected if already correclty verified.
     */
    public function testUserIsRedirectedToCorrectRouteIfAlreadyVerified()
    {
        $user = factory(User::class)->create(
            ['email_verified_at' => now()]
        );

        $response = $this->actingAs($user)->post($this->verificationResendRoute());

        $response->assertRedirect($this->successfulVerificationRoute());
    }

    /**
     * Check that a user can resend a Verification mail.
     */
    public function testUserCanResendAVerificationEmail()
    {
        Notification::fake();
        Notification::assertNothingSent();

        $user = factory(User::class)->create(
            ['email_verified_at' => null]
        );

        $response = $this->actingAs($user)
            ->from($this->verificationNoticeRoute())
            ->post($this->verificationResendRoute());

        // Works in test on mac
        $response->assertRedirect($this->verificationNoticeRoute());
        Notification::assertSentTo(
            $user,
            \App\Notifications\DeepskyLogVerificationNotification::class
        );
    }
}
