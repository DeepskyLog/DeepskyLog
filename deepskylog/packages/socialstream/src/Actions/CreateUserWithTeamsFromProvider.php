<?php

namespace JoelButcher\Socialstream\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User as ProviderUserContract;

class CreateUserWithTeamsFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     */
    public CreatesConnectedAccounts $createsConnectedAccounts;

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
    public function create(string $provider, ProviderUserContract $providerUser): User
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            return tap(User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                if (Socialstream::hasProviderAvatarsFeature() && $providerUser->getAvatar()) {
                    $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                }

                $this->createsConnectedAccounts->create($user, $provider, $providerUser);

                $this->addToObservers($user);
            });
        });
    }

    /**
     * Add the user to the Observers team. DeepskyLog has a fixed set of teams,
     * so no personal team is created.
     */
    protected function addToObservers(User $user): void
    {
        $team = Team::where('name', 'Observers')->firstOrFail();
        $user->teams()->attach($team);
        $user->switchTeam($team);
    }
}
