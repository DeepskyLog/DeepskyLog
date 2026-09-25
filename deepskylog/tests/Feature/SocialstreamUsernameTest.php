<?php

use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\User;
use Laravel\Socialite\Two\User as SocialiteUser;

function socialiteUser(string $id, ?string $name, string $email, ?string $nickname = null): SocialiteUser
{
    return (new SocialiteUser)
        ->map([
            'id' => $id,
            'nickname' => $nickname,
            'name' => $name,
            'email' => $email,
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('token-' . $id)
        ->setRefreshToken('refresh-' . $id)
        ->setExpiresIn(3600);
}

beforeEach(function () {
    // The action adds every new user to the Observers team.
    $this->getTeamFromString('Observers');
});

it('gives social login users with the same name different usernames', function () {
    $action = app(CreateUserFromProvider::class);

    $first = $action->create('github', socialiteUser('1', 'John Smith', 'john1@example.com'));
    $second = $action->create('github', socialiteUser('2', 'John Smith', 'john2@example.com'));
    $third = $action->create('google', socialiteUser('3', 'John Smith', 'john3@example.com'));

    expect([$first->username, $second->username, $third->username])
        ->toBe(['JohnSmith', 'JohnSmith2', 'JohnSmith3']);
});

it('does not reuse a username taken by a registered user', function () {
    User::factory()->create(['username' => 'JaneDoe']);

    $user = app(CreateUserFromProvider::class)
        ->create('github', socialiteUser('4', 'Jane Doe', 'jane@example.com'));

    expect($user->username)->toBe('JaneDoe2');
});

it('falls back to the nickname or email when the provider gives no name', function () {
    $action = app(CreateUserFromProvider::class);

    $withNickname = $action->create('github', socialiteUser('5', null, 'someone@example.com', 'stargazer'));
    $withEmailOnly = $action->create('github', socialiteUser('6', null, 'deep.sky@example.com'));

    expect($withNickname->username)->toBe('Stargazer')
        ->and($withEmailOnly->username)->toBe('DeepSky');
});

it('refuses two users with the same username in the database', function () {
    User::factory()->create(['username' => 'Taken']);

    expect(fn() => User::factory()->create(['username' => 'Taken']))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
