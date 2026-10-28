<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\LegacySiteReturn;
use Database\Seeders\GroupSeeder;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config(['services.legacy_site.url' => 'http://old.example.org/']);
});

function legacyRegistration(): array
{
    return [
        'name' => 'Test User',
        'username' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ];
}

it('sends an observer who came from the old site back there after registering and after verifying', function () {
    $this->seed(GroupSeeder::class);

    $this->get('/register?return_to='.urlencode('http://old.example.org/'))->assertOk();
    $this->post('/register', legacyRegistration())->assertRedirect('http://old.example.org/');

    $user = User::where('username', 'TestUser')->firstOrFail();
    expect($user->hasVerifiedEmail())->toBeFalse();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );
    $this->get($verificationUrl)->assertRedirect('http://old.example.org/');

    expect(session()->has(LegacySiteReturn::SESSION_KEY))->toBeFalse();
});

it('keeps an observer who registers on this site here', function () {
    $this->seed(GroupSeeder::class);

    $this->get('/register')->assertOk();
    $this->post('/register', legacyRegistration())->assertRedirect(RouteServiceProvider::HOME);
});

it('ignores a return address that is not on the old site', function (string $url) {
    $this->seed(GroupSeeder::class);

    $this->get('/register?return_to='.urlencode($url))->assertOk();
    $this->post('/register', legacyRegistration())->assertRedirect(RouteServiceProvider::HOME);
})->with(['https://evil.example.com/', 'http://old.example.org.evil.com/', '//evil.example.com', 'javascript:alert(1)']);

it('sends an observer back to the domain of the old site they came from', function () {
    config(['services.legacy_site.url' => 'https://www.deepskylog.be/, https://www.deepskylog.nl/']);
    $this->seed(GroupSeeder::class);

    $this->get('/register?return_to='.urlencode('https://www.deepskylog.nl/'))->assertOk();
    $this->post('/register', legacyRegistration())->assertRedirect('https://www.deepskylog.nl/');
});

it('sends nobody back without the address of the old site', function () {
    config(['services.legacy_site.url' => null]);
    $this->seed(GroupSeeder::class);

    $this->get('/register?return_to='.urlencode('http://old.example.org/'))->assertOk();
    $this->post('/register', legacyRegistration())->assertRedirect(RouteServiceProvider::HOME);
});
