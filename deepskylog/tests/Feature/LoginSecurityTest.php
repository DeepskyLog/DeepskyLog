<?php

use App\Models\Team;
use App\Models\User;
use App\Support\LegacyPassword;
use Illuminate\Support\Facades\Hash;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use Laravel\Socialite\Two\User as ProviderUser;

function loginProviderUser(string $email, string $id = 'provider-id'): ProviderUser
{
    return (new ProviderUser)
        ->setRaw(['sub' => $id, 'email' => $email, 'email_verified' => true])
        ->map(['id' => $id, 'nickname' => 'Jane', 'name' => 'Jane Doe', 'email' => $email, 'avatar' => null])
        ->setToken('user-token');
}

it('accepts a legacy MD5 password and replaces it with a normal hash', function () {
    $user = User::factory()->create(['password' => md5('s3cret')]);

    $this->post('/login', ['email' => $user->email, 'password' => 's3cret']);

    $this->assertAuthenticatedAs($user);
    expect(LegacyPassword::isMd5($user->fresh()->password))->toBeFalse()
        ->and(Hash::check('s3cret', $user->fresh()->password))->toBeTrue();
});

it('does not treat two "0e" MD5 hashes as equal', function () {
    // md5('240610708') and md5('QNKCDZO') both look like 0 to ==.
    $user = User::factory()->create(['password' => md5('240610708')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'QNKCDZO']);

    $this->assertGuest();
});

it('wraps the MD5 passwords in bcrypt, and they still log in', function () {
    $legacy = User::factory()->create(['password' => md5('s3cret')]);
    $modern = User::factory()->create();
    $modernHash = $modern->password;

    $this->artisan('users:rehash-md5-passwords')->assertSuccessful();
    expect($legacy->fresh()->password)->toBe(md5('s3cret'));

    $this->artisan('users:rehash-md5-passwords --apply')->assertSuccessful();
    expect(LegacyPassword::isMd5($legacy->fresh()->password))->toBeFalse()
        ->and($modern->fresh()->password)->toBe($modernHash);

    $this->post('/login', ['email' => $legacy->email, 'password' => 'wrong']);
    $this->assertGuest();

    $this->post('/login', ['email' => $legacy->email, 'password' => 's3cret']);
    $this->assertAuthenticatedAs($legacy);
    expect(Hash::check('s3cret', $legacy->fresh()->password))->toBeTrue();
});

it('throttles logins that differ only in capitalisation together', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    foreach (['jane@example.com', 'Jane@example.com', 'JANE@example.com', 'jane@EXAMPLE.com', 'Jane@Example.com'] as $email) {
        $this->post('/login', ['email' => $email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => 'jAnE@example.com', 'password' => 'password'])->assertStatus(429);
    $this->assertGuest();
});

it('throttles one account attacked from many addresses', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    for ($i = 1; $i <= 30; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.0.{$i}"])
            ->post('/login', ['email' => 'jane@example.com', 'password' => 'wrong'])
            ->assertStatus(302);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.1'])
        ->post('/login', ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertStatus(429);
});

it('sends an OAuth login with two-factor authentication to the challenge', function () {
    Team::forceCreate(['name' => 'Observers', 'user_id' => User::factory()->create()->id, 'personal_team' => false]);
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'two_factor_secret' => encrypt('secret'),
        'two_factor_confirmed_at' => now(),
    ]);
    app(CreatesConnectedAccounts::class)->create($user, 'google', loginProviderUser('jane@example.com'));

    $response = app(AuthenticatesOAuthCallback::class)->authenticate('google', loginProviderUser('jane@example.com'));

    $this->assertGuest();
    expect($response->getTargetUrl())->toBe(route('two-factor.login'))
        ->and(session('login.id'))->toBe($user->id);
});

it('logs an OAuth login without two-factor authentication in', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);
    app(CreatesConnectedAccounts::class)->create($user, 'google', loginProviderUser('jane@example.com'));

    app(AuthenticatesOAuthCallback::class)->authenticate('google', loginProviderUser('jane@example.com'));

    $this->assertAuthenticatedAs($user);
});

it('links a provider to the logged-in user after confirmation', function () {
    $user = User::factory()->create();
    cache()->put("socialstream.{$user->id}:github.provider", loginProviderUser('other@example.com', 'gh-1'));

    $this->actingAs($user)
        ->post(route('oauth.callback.confirm', 'github'), ['result' => 'confirm'])
        ->assertRedirect();

    expect($user->connectedAccounts()->where('provider', 'github')->where('provider_id', 'gh-1')->count())->toBe(1);
});

it('does not link a provider account of another user', function () {
    $owner = User::factory()->create();
    app(CreatesConnectedAccounts::class)->create($owner, 'github', loginProviderUser('owner@example.com', 'gh-1'));
    $user = User::factory()->create();
    cache()->put("socialstream.{$user->id}:github.provider", loginProviderUser('owner@example.com', 'gh-1'));

    $this->actingAs($user)
        ->post(route('oauth.callback.confirm', 'github'), ['result' => 'confirm'])
        ->assertRedirect();

    expect($user->connectedAccounts()->count())->toBe(0);
});

it('requires a login to confirm linking a provider', function () {
    $this->post(route('oauth.callback.confirm', 'github'), ['result' => 'confirm'])
        ->assertRedirect(route('login'));
});
