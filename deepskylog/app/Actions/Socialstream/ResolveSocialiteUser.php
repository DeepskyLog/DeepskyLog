<?php

namespace App\Actions\Socialstream;

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
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'session_id' => session()->getId(),
                'session_cookie' => request()->cookie(config('session.cookie')),
                'cookies' => request()->cookies->all(),
                'query' => request()->query(),
            ]);

            $user = Socialite::driver($provider)->user();

            if (Socialstream::generatesMissingEmails()) {
                $user->email = $user->getEmail() ?? ("{$user->id}@{$provider}".config('app.domain'));
            }

            return $user;
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            Log::warning('oauth: InvalidStateException on callback', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'session_id' => session()->getId(),
                'session_cookie' => request()->cookie(config('session.cookie')),
                'cookies' => request()->cookies->all(),
                'query' => request()->query(),
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
