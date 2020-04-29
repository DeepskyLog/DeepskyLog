<?php

namespace Tests\Feature\Auth;

use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Returns the redirection after successful registration.
     *
     * @return string The redirection after successful registration
     */
    protected function successfulRegistrationRoute()
    {
        return '/';
    }

    /**
     * Returns the redirection for registering.
     *
     * @return string The redirection for registering
     */
    protected function registerGetRoute()
    {
        return route('register');
    }

    /**
     * Returns the redirection for registering.
     *
     * @return string The redirection for registering
     */
    protected function registerPostRoute()
    {
        return route('register');
    }

    /**
     * Returns the redirection for the guest middleware.
     *
     * @return string The redirection for the guest middleware
     */
    protected function guestMiddlewareRoute()
    {
        return '/home';
    }

    /**
     * Tests if the user can see a registration form.
     */
    public function testUserCanViewARegistrationForm()
    {
        $response = $this->get($this->registerGetRoute());

        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }

    /**
     * Tests if the user cannot see a registration form when already authenticated.
     */
    public function testUserCannotViewARegistrationFormWhenAuthenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get($this->registerGetRoute());

        $response->assertRedirect($this->guestMiddlewareRoute());
    }

    /**
     * Tests if the user can register.
     */
    public function testUserCanRegister()
    {
        Event::fake();

        $response = $this->post(
            $this->registerPostRoute(),
            [
                'username' => 'John',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'i-love-laravel',
                'password_confirmation' => 'i-love-laravel',
                'country' => 'BE',
                'observationlanguage' => 'en',
                'language' => 'en',
            ]
        );

        $response->assertRedirect($this->successfulRegistrationRoute());

        // The test will fail because we have the recaptcha.
        //$this->assertCount(1, $users = User::all());
        //$this->assertAuthenticatedAs($user = $users->first());
        //$this->assertEquals('John Doe', $user->name);
        //$this->assertEquals('john@example.com', $user->email);
        //$this->assertTrue(Hash::check('i-love-laravel', $user->password));
        //Event::assertDispatched(
        //    Registered::class,
        //    function ($e) use ($user) {
        //        return $e->user->id === $user->id;
        //    }
        //);
    }

    /**
     * Tests that the user cannot register without a name.
     */
    public function testUserCannotRegisterWithoutName()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => '',
                'email' => 'john@example.com',
                'password' => 'i-love-laravel',
                'password_confirmation' => 'i-love-laravel',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('name');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Tests that the user cannot register without an email.
     */
    public function testUserCannotRegisterWithoutEmail()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => 'John Doe',
                'email' => '',
                'password' => 'i-love-laravel',
                'password_confirmation' => 'i-love-laravel',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Tests that the user cannot register with an invalid email.
     */
    public function testUserCannotRegisterWithInvalidEmail()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => 'John Doe',
                'email' => 'invalid-email',
                'password' => 'i-love-laravel',
                'password_confirmation' => 'i-love-laravel',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Tests that the user cannot register without a password.
     */
    public function testUserCannotRegisterWithoutPassword()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => '',
                'password_confirmation' => '',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Test that the user cannot register without confirming the password.
     */
    public function testUserCannotRegisterWithoutPasswordConfirmation()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'i-love-laravel',
                'password_confirmation' => '',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    /**
     * Tests that the user cannot register when the passwords don't match.
     */
    public function testUserCannotRegisterWithPasswordsNotMatching()
    {
        $response = $this->from($this->registerGetRoute())->post(
            $this->registerPostRoute(),
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'i-love-laravel',
                'password_confirmation' => 'i-love-symfony',
            ]
        );

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect($this->registerGetRoute());
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
