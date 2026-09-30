<?php

use App\Actions\Socialstream\CreateUserFromProvider;
use App\Actions\Socialstream\GenerateRedirectForProvider;
use App\Actions\Socialstream\ResolveSocialiteUser;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

function oauthRequest(string $uri): void
{
    $request = Request::create($uri, 'GET', [], [
        config('session.cookie') => 'secret-session-cookie',
        'XSRF-TOKEN' => 'secret-xsrf',
    ]);
    app()->instance('request', $request);
}

function providerUser(): ProviderUser
{
    return (new ProviderUser)
        ->map(['id' => 'abc', 'nickname' => 'Jane', 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'avatar' => null])
        ->setToken('user-token');
}

it('does not log cookies, session ids or OAuth codes', function () {
    $written = [];
    Log::shouldReceive('info', 'warning', 'error', 'debug')
        ->andReturnUsing(function ($message, $context = []) use (&$written) {
            $written[] = $message.' '.json_encode($context);
        });

    $provider = Mockery::mock();
    $provider->shouldReceive('redirect')->andReturn(new RedirectResponse('https://example.com'));
    $provider->shouldReceive('user')->andReturn(providerUser());
    Socialite::shouldReceive('driver')->andReturn($provider);

    Team::forceCreate(['name' => 'Observers', 'user_id' => User::factory()->create()->id, 'personal_team' => false]);

    oauthRequest('/oauth/google');
    app(GenerateRedirectForProvider::class)->generate('google');

    oauthRequest('/oauth/google/callback?code=secret-oauth-code&state=secret-state');
    app(ResolveSocialiteUser::class)->resolve('google');
    app(CreateUserFromProvider::class)->create('google', providerUser());

    $log = implode("\n", $written);
    expect($written)->not->toBeEmpty()
        ->and($log)->not->toContain('secret-')
        ->and($log)->not->toContain(session()->getId());
});
