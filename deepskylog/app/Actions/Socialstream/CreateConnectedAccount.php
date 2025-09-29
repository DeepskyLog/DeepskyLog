<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateConnectedAccount implements CreatesConnectedAccounts
{
    /**
     * Create a connected account for a given user.
     */
    public function create(mixed $user, string $provider, ProviderUser $providerUser): ConnectedAccount
    {
        try {
            \Illuminate\Support\Facades\Log::info('oauth: creating connected account', [
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'provider_email' => $providerUser->getEmail(),
                'user_id' => is_object($user) ? $user->id : $user,
                'session_id' => session()->getId(),
                'session_cookie' => request()->cookie(config('session.cookie')),
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }

        return Socialstream::connectedAccountModel()::forceCreate([
            'user_id' => $user->id,
            'provider' => strtolower($provider),
            'provider_id' => $providerUser->getId(),
            'name' => $providerUser->getName(),
            'nickname' => $providerUser->getNickname(),
            'email' => $providerUser->getEmail(),
            'avatar_path' => $providerUser->getAvatar(),
            'token' => $providerUser->token,
            'secret' => $providerUser->tokenSecret ?? null,
            'refresh_token' => $providerUser->refreshToken ?? null,
            'expires_at' => property_exists($providerUser, 'expiresIn') ? now()->addSeconds($providerUser->expiresIn) : null,
        ]);
    }
}
