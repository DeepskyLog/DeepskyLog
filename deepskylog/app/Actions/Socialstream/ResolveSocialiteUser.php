<?php

namespace App\Actions\Socialstream;

use App\Support\OAuthEmail;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     */
    public function resolve(string $provider): User
    {
        try {
            // Log diagnostic info at callback time
            Log::info('oauth: callback received', [
                'provider' => $provider,
                'url' => Request::url(),
                'method' => Request::method(),
            ]);

            $user = Socialite::driver($provider)->user();

            try {
                Log::info('oauth: provider user', [
                    'provider' => $provider,
                    'provider_id' => $user->getId(),
                    'has_token' => ! empty($user->token ?? null),
                ]);
            } catch (\Throwable $e) {
                Log::warning('oauth: failed to log provider user', ['error' => $e->getMessage()]);
            }

            if (Socialstream::generatesMissingEmails()) {
                $user->email = $user->getEmail() ?? OAuthEmail::generate($provider, $user->id);
            }

            return $user;
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('oauth: InvalidStateException on callback', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to let configured handler deal with it (or bubble up)
            throw $e;
        } catch (\Throwable $e) {
            Log::error('oauth: unexpected error on callback', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
