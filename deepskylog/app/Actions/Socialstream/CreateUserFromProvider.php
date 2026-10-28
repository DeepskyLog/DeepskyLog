<?php

namespace App\Actions\Socialstream;

use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use App\Support\OAuthEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUserContract;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts
     */
    public $createsConnectedAccounts;

    /**
     * Create a new action instance.
     */
    public function __construct(CreatesConnectedAccounts $createsConnectedAccounts)
    {
        $this->createsConnectedAccounts = $createsConnectedAccounts;
    }

    /**
     * Create a new user from a social provider user.
     */
    public function create(string $provider, ProviderUserContract $providerUser): mixed
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            // Log diagnostic info about the provider user and session at creation time
            try {
                \Illuminate\Support\Facades\Log::info('oauth: creating user from provider', [
                    'provider' => $provider,
                    'provider_id' => $providerUser->getId(),
                ]);
            } catch (\Throwable $e) {
                // Do not break the flow for logging failures
            }

            return tap(User::create([
                'name' => $providerUser->getName() ?: $this->uniqueUsername($providerUser),
                'username' => $this->uniqueUsername($providerUser),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                // A generated email cannot be verified, and only stands in for
                // the missing one.
                if (OAuthEmail::isVerifiedByProvider($provider, $providerUser) || OAuthEmail::isGenerated($user->email)) {
                    $user->markEmailAsVerified();
                } else {
                    $user->sendEmailVerificationNotification();
                }

                if (Features::profilePhotos()) {
                    if (Socialstream::hasProviderAvatarsFeature() && Jetstream::managesProfilePhotos() && $providerUser->getAvatar()) {
                        $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                    }
                }

                $this->createsConnectedAccounts->create($user, $provider, $providerUser);
                try {
                    \Illuminate\Support\Facades\Log::info('oauth: created user', [
                        'provider' => $provider,
                        'provider_id' => $providerUser->getId(),
                        'user_id' => $user->id,
                    ]);
                } catch (\Throwable $e) {
                    // ignore logging errors
                }
                $this->addToTeam($user, Team::OBSERVERS);
                Message::dismissExistingBroadcastsFor($user->username);
            });
        });
    }

    /**
     * A username for the provider user that no other user has.
     *
     * Providers give a display name rather than a username, so two people with
     * the same name would otherwise share a username, and with it their
     * observations, which are linked to users by username. A name that is
     * taken gets a number appended (JohnSmith, JohnSmith2, JohnSmith3, ...).
     */
    protected function uniqueUsername(ProviderUserContract $providerUser): string
    {
        $name = $providerUser->getName()
            ?: $providerUser->getNickname()
            ?: Str::before((string) $providerUser->getEmail(), '@');

        // Letters and digits only (e.g. "deep.sky" becomes "DeepSky"), leaving
        // room for the number within the 30 characters of a registered username.
        $words = trim((string) preg_replace('/[^\pL\pN]+/u', ' ', (string) $name));
        $base = Str::limit(Str::studly($words), 25, '') ?: 'Observer';

        $username = $base;
        for ($suffix = 2; User::where('username', $username)->exists(); $suffix++) {
            $username = $base . $suffix;
        }

        return $username;
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
