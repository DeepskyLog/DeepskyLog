<?php

use App\Providers\RouteServiceProvider;
use Database\Seeders\GroupSeeder;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

it('registration screen can be rendered', function () {
    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is not enabled.');
    }

    $response = $this->get('/register');

    $response->assertStatus(200);
});

it('new users can register', function () {
    $this->seed(GroupSeeder::class);

    if (! Features::enabled(Features::registration())) {
        $this->markTestSkipped('Registration support is not enabled.');
    }

    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});

function registrationInput(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ], $overrides);
}

it('accepts usernames with letters, digits, spaces, dots, hyphens and underscores', function (string $username) {
    $this->seed(GroupSeeder::class);

    $this->post('/register', registrationInput(['username' => $username]))->assertSessionHasNoErrors();

    $this->assertAuthenticated();
})->with(['Jan Peeters', 'jan.peeters', 'jan_peeters-2', 'Ångström']);

it('refuses usernames that could not be used to log in or look odd', function (string $username) {
    $this->seed(GroupSeeder::class);

    $this->post('/register', registrationInput(['username' => $username]))->assertSessionHasErrors('username');

    $this->assertGuest();
})->with(['other@example.com', 'a/b', 'what?', '<b>x</b>', '.dot', str_repeat('a', 31)]);

it('explains that the privacy policy has to be accepted', function () {
    $this->seed(GroupSeeder::class);

    $this->post('/register', registrationInput(['terms' => false]))
        ->assertSessionHasErrors(['terms' => __('You have to accept the privacy policy to register.')]);
});
