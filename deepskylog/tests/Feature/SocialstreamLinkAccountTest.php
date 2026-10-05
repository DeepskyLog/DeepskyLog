<?php

use App\Models\Team;
use App\Models\User;
use Laravel\Socialite\Two\User as ProviderUser;

beforeEach(function () {
    Team::forceCreate(['name' => 'Observers', 'user_id' => User::factory()->create()->id, 'personal_team' => false]);
});

function linkProviderUser(string $id): ProviderUser
{
    return (new ProviderUser)
        ->setRaw(['id' => $id, 'email' => 'jane@example.com'])
        ->map(['id' => $id, 'nickname' => 'Jane', 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'avatar' => null])
        ->setToken('user-token');
}

function confirmLink(User $user, ProviderUser $providerUser): Illuminate\Testing\TestResponse
{
    cache()->put("socialstream.{$user->id}:github.provider", $providerUser, now()->addMinutes(10));

    return test()->actingAs($user)
        ->post(route('oauth.callback.confirm', 'github'), ['result' => 'confirm'])
        ->assertRedirect(route('profile.show'));
}

it('shows that the provider account was linked', function () {
    $user = User::factory()->create();

    confirmLink($user, linkProviderUser('1001'));

    expect($user->connectedAccounts()->where('provider', 'github')->count())->toBe(1);
    $this->get(route('profile.show'))->assertSee('You have successfully linked your GitHub account.');
});

it('shows why a provider account of another user is not linked', function () {
    $other = User::factory()->create();
    $other->connectedAccounts()->create([
        'provider' => 'github', 'provider_id' => '1002', 'token' => 'x',
    ]);
    $user = User::factory()->create();

    confirmLink($user, linkProviderUser('1002'));

    expect($user->connectedAccounts()->count())->toBe(0);
    $this->get(route('profile.show'))->assertSee('It looks like this GitHub account is used by another user. Please log in.');
});
