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
