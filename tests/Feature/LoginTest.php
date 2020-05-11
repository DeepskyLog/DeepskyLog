<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Can the user see the login form?
     */
    public function testUserCanViewALoginForm()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSuccessful();
        $response->assertViewIs('auth.login');
    }

    /**
     * The login form should not be accessible when logged in.
     */
    public function testUserCannotViewALoginFormWhenAuthenticated()
    {
        $user = factory('App\User')->make();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/home');
    }

    /**
     * Check for validation errors.
     */
    public function testLoginDisplaysValidationErrors()
    {
        $response = $this->post('/login', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors('password');
    }

    /**
     * Try to log in with the correct credentials.
     */
    public function testUserCanLoginWithCorrectCredentials()
    {
        // Create a user
        $user = factory(User::class)->create(
            [
                'username' => 'deepskylogUser',
                'password' => 'password123',
            ]
        );

        // Post to login
        $response = $this->post(
            '/login',
            [
                'email' => 'deepskylogUser',
                'password' => 'password123',
            ]
        );

        // Assert redirect 302 to /
        $response->assertRedirect('/home');
        $response->assertStatus(302);

        // Check if we are correctly authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Try to log in using the incorrect credentials.
     */
    public function testUserCannotLoginWithIncorrectPassword()
    {
        $user = factory(User::class)->create(
            [
                'password' => 'password123',
            ]
        );

        $response = $this->from('/login')->post(
            '/login',
            [
                'email' => $user->email,
                'password' => 'invalid-password',
            ]
        );

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Test the Remember me functionality and check if the cookie does exist.
     */
    public function testRememberMeFunctionality()
    {
        $user = factory(User::class)->create(
            [
                'id' => random_int(1, 100),
                'password' => $password = 'password123',
            ]
        );

        $response = $this->post(
            '/login',
            [
                'email' => $user->email,
                'password' => $password,
                'remember' => 'on',
            ]
        );

        $response->assertRedirect('/home');

        // cookie assertion
        $response->assertCookie(
            Auth::guard()->getRecallerName(),
            vsprintf(
                '%s|%s|%s',
                [$user->id, $user->getRememberToken(), $user->password]
            )
        );

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Check that the user cannot log in with an email that does not exist.
     */
    public function testUserCannotLoginWithEmailThatDoesNotExist()
    {
        $response = $this->from('/login')->post(
            '/login',
            [
                'email' => 'nobody@example.com',
                'password' => 'invalid-password',
            ]
        );

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Check that the user cannot log in with a username that does not exist.
     */
    public function testUserCannotLoginWithUsernameThatDoesNotExist()
    {
        $response = $this->from('/login')->post(
            '/login',
            [
                'email' => 'testUser',
                'password' => 'invalid-password',
            ]
        );

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Test if the user can log out succesfully.
     */
    public function testUserCanLogout()
    {
        $this->be(factory(User::class)->create());

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test that an not authenticated user can not log out.
     */
    public function testUserCannotLogoutWhenNotAuthenticated()
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Check that the user cannot make more than five login attempts in one minute.
     */
    public function testUserCannotMakeMoreThanFiveAttemptsInOneMinute()
    {
        $user = factory(User::class)->create(
            ['password' => 'i-love-laravel']
        );

        foreach (range(0, 5) as $_) {
            $response = $this->from('/login')->post(
                '/login',
                [
                    'email' => $user->email,
                    'password' => 'invalid-password',
                ]
            );
        }

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertMatchesRegularExpression(
            sprintf(
                '/^%s$/',
                str_replace('\:seconds', '\d+', preg_quote(__('auth.throttle'), '/'))
            ),
            collect(
                $response
                    ->baseResponse
                    ->getSession()
                    ->get('errors')
                    ->getBag('default')
                    ->get('email')
            )->first()
        );
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
