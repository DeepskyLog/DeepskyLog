<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOAuthCallback
{
    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(string $provider, User $providerAccount): SocialstreamResponse|RedirectResponse;

    /**
     * Links the provider account to the logged-in user once they confirmed it.
     */
    public function link(Authenticatable $user, string $provider, User $providerAccount): SocialstreamResponse;
}
