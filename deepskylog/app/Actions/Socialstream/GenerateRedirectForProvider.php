<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse
    {
        try {
            // Log some diagnostic info about the session / request before redirect
            Log::info('oauth: redirect init', [
                'provider' => $provider,
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'session_id' => session()->getId(),
                'session_cookie' => request()->cookie(config('session.cookie')),
                'cookies' => request()->cookies->all(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('oauth: failed to log redirect diagnostics', ['error' => $e->getMessage()]);
        }

        return Socialite::driver($provider)->redirect();
    }
}
