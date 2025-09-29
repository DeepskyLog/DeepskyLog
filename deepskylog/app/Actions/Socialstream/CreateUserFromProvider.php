<?php

namespace App\Actions\Socialstream;

use App\Models\Team;
use App\Models\User;
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
                    'provider_email' => $providerUser->getEmail(),
                    'provider_name' => $providerUser->getName(),
                    'session_id' => session()->getId(),
                    'session_cookie' => request()->cookie(config('session.cookie')),
                    'cookies' => request()->cookies->all(),
                ]);
            } catch (\Throwable $e) {
                // Do not break the flow for logging failures
            }

            return tap(User::create([
                'name' => $providerUser->getName(),
                'username' => Str::studly($providerUser->getName()),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                if (Features::profilePhotos()) {
                    if (Socialstream::hasProviderAvatarsFeature() && Jetstream::managesProfilePhotos() && $providerUser->getAvatar()) {
                        $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                    }
                }

                $user->switchConnectedAccount(
                    $this->createsConnectedAccounts->create($user, $provider, $providerUser)
                );
                $user->save();
                try {
                    \Illuminate\Support\Facades\Log::info('oauth: created user', [
                        'provider' => $provider,
                        'provider_id' => $providerUser->getId(),
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'session_id' => session()->getId(),
                        'session_cookie' => request()->cookie(config('session.cookie')),
                    ]);
                } catch (\Throwable $e) {
                    // ignore logging errors
                }
                $this->addToTeam($user, 'Observers');
            });
        });
    }

    /**
     * Add the user to the given team.
     */
    protected function addToTeam(User $user, string $team): void
    {
        $team = Team::where('name', $team)->firstOrFail();
        $user->teams()->attach($team);
        $user->switchTeam($team);
    }
}
