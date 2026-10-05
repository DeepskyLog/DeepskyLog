<?php

namespace App\Actions\Fortify;

use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use App\Support\OAuthEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public const USERNAME_PATTERN = '/^[\pL\pN][\pL\pN _.\-]*$/u';

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            // Letters, digits, spaces (many old usernames are full names) and
            // _ . -, so no @: login treats anything that looks like an email
            // address as one, and such a username could never be used to log in.
            'username' => ['required', 'string', 'max:30', 'regex:'.self::USERNAME_PATTERN, 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', OAuthEmail::notGeneratedRule()],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ], [
            'username.regex' => __('The username may only contain letters, digits, spaces, dots, hyphens and underscores, and has to start with a letter or a digit.'),
            'terms.accepted' => __('You have to accept the privacy policy to register.'),
            'terms.required' => __('You have to accept the privacy policy to register.'),
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                $this->addToTeam($user, Team::OBSERVERS);
                Message::dismissExistingBroadcastsFor($user->username);
            });
        });
    }

    /**
     * Add the user to the given team.
     */
    protected function addToTeam(User $user, int $teamId): void
    {
        $team = Team::findOrFail($teamId);
        $user->teams()->attach($team);
        $user->switchTeam($team);
    }
}
