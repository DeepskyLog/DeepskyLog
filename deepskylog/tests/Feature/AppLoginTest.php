<?php

use App\Http\Controllers\AppLoginController;
use App\Models\User;

// Logging in to the DeepskyLog GoTo app with Google or GitHub
// (AppLoginController): the browser hands a one-time code to the app, which
// redeems it with the secret whose SHA-256 it sent at the start.

function appLoginLink(string $html): string
{
    preg_match('/href="(dslgoto:\/\/login\?code=[A-Za-z0-9]+)"/', $html, $match);

    return $match[1] ?? '';
}

function appLoginCode(string $link): string
{
    parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

    return $query['code'];
}

it('sends a guest to the provider and returns to the app login afterwards', function () {
    $challenge = AppLoginController::challengeOf('secret-of-the-app');

    $this->get(route('app-login.start', ['provider' => 'google', 'challenge' => $challenge]))
        ->assertRedirect(route('oauth.redirect', 'google'))
        ->assertSessionHas('url.intended', route('app-login.start', ['challenge' => $challenge, 'provider' => 'google']));
});

it('refuses an unknown provider or a malformed challenge', function () {
    $this->get(route('app-login.start', ['provider' => 'facebook', 'challenge' => AppLoginController::challengeOf('x')]))->assertSessionHasErrors('provider');
    $this->get(route('app-login.start', ['provider' => 'google', 'challenge' => 'short']))->assertSessionHasErrors('challenge');
});

it('logs the observer in to the app with the code and the secret', function () {
    $user = User::factory()->create();
    $verifier = 'secret-of-the-app-'.str_repeat('x', 30);

    $html = $this->actingAs($user)
        ->get(route('app-login.start', ['provider' => 'google', 'challenge' => AppLoginController::challengeOf($verifier)]))
        ->assertOk()
        ->assertSee('Back to the app')
        ->getContent();
    $code = appLoginCode(appLoginLink($html));

    auth()->logout();
    $this->flushSession();

    $this->get(route('app-login.redeem', ['code' => $code, 'verifier' => $verifier]))
        ->assertRedirect(route('observing-lists.eyepiece'));
    $this->assertAuthenticatedAs($user);
});

it('refuses a code with the wrong secret, and a code used before', function () {
    $user = User::factory()->create();
    $verifier = 'secret-of-the-app';
    $start = fn () => appLoginCode(appLoginLink($this->actingAs($user)
        ->get(route('app-login.start', ['provider' => 'google', 'challenge' => AppLoginController::challengeOf($verifier)]))
        ->getContent()));

    $code = $start();
    auth()->logout();
    $this->get(route('app-login.redeem', ['code' => $code, 'verifier' => 'stolen']))
        ->assertRedirect(route('login'))->assertSessionHasErrors('app');
    $this->assertGuest();
    // The wrong secret used the code up
    $this->get(route('app-login.redeem', ['code' => $code, 'verifier' => $verifier]))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('gives the challenge of RFC 7636', function () {
    // RFC 7636, appendix B
    expect(AppLoginController::challengeOf('dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'))->toBe('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
});
