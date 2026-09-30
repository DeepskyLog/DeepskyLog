<?php

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\Team;
use App\Models\User;
use App\Support\OAuthEmail;
use Illuminate\Validation\ValidationException;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use Laravel\Socialite\Two\User as ProviderUser;

beforeEach(function () {
    Team::forceCreate(['name' => 'Observers', 'user_id' => User::factory()->create()->id, 'personal_team' => false]);
    session()->put('socialstream.previous_url', route('login'));
});

function oauthUser(string $email, bool $verified = true, string $id = 'provider-id'): ProviderUser
{
    return (new ProviderUser)
        ->setRaw(['sub' => $id, 'email' => $email, 'email_verified' => $verified])
        ->map(['id' => $id, 'nickname' => 'Jane', 'name' => 'Jane Doe', 'email' => $email, 'avatar' => null])
        ->setToken('user-token');
}

function oauthCallback(string $provider, ProviderUser $providerUser): mixed
{
    return app(AuthenticatesOAuthCallback::class)->authenticate($provider, $providerUser);
}

it('does not sign into an account whose email was never verified', function () {
    $squatter = User::factory()->unverified()->create(['email' => 'victim@gmail.com']);

    oauthCallback('google', oauthUser('victim@gmail.com'));

    $this->assertGuest();
    expect($squatter->connectedAccounts()->count())->toBe(0);
});

it('does not link when the provider has not verified the email', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    oauthCallback('google', oauthUser('jane@example.com', verified: false));

    $this->assertGuest();
    expect($user->connectedAccounts()->count())->toBe(0);
});

it('links and signs into a verified account', function (string $provider) {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    oauthCallback($provider, oauthUser('jane@example.com'));

    $this->assertAuthenticatedAs($user);
    expect($user->connectedAccounts()->where('provider', $provider)->count())->toBe(1);
})->with(['google', 'github']);

it('does not link to an account on a generated email', function () {
    $email = OAuthEmail::generate('github', 12345);
    $squatter = User::factory()->create(['email' => $email]);

    oauthCallback('github', oauthUser($email, id: '12345'));

    $this->assertGuest();
    expect($squatter->connectedAccounts()->count())->toBe(0);
});

it('only marks the email of a new user verified when the provider did', function () {
    $verified = app(CreateUserFromProvider::class)->create('google', oauthUser('a@example.com', id: 'a'));
    $unverified = app(CreateUserFromProvider::class)->create('google', oauthUser('b@example.com', verified: false, id: 'b'));
    $generated = app(CreateUserFromProvider::class)->create('github', oauthUser(OAuthEmail::generate('github', 'c'), id: 'c'));

    expect($verified->hasVerifiedEmail())->toBeTrue()
        ->and($unverified->hasVerifiedEmail())->toBeFalse()
        ->and($generated->hasVerifiedEmail())->toBeTrue();
});

it('refuses registration on the domain of the generated emails', function () {
    app(CreateNewUser::class)->create([
        'name' => 'Mallory',
        'username' => 'mallory',
        'email' => OAuthEmail::generate('github', 12345),
        'password' => 'Str0ng-Passw0rd!',
        'password_confirmation' => 'Str0ng-Passw0rd!',
        'terms' => true,
    ]);
})->throws(ValidationException::class);

it('refuses changing the email to a generated one, but keeps an existing one', function () {
    $profile = fn (User $user, string $email) => app(UpdateUserProfileInformation::class)->update($user, [
        'name' => $user->name,
        'email' => $email,
        'country' => 'BE',
        'about' => null,
        'fstOffset' => 0,
        'copyrightSelection' => '',
    ]);

    $generated = User::factory()->create(['email' => OAuthEmail::generate('github', 1)]);
    $profile($generated, $generated->email);
    expect($generated->fresh()->email)->toBe(OAuthEmail::generate('github', 1));

    $profile(User::factory()->create(), OAuthEmail::generate('github', 2));
})->throws(ValidationException::class);
