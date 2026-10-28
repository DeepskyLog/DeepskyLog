<?php

namespace App\Http\Responses;

use App\Support\LegacySiteReturn;
use Illuminate\Http\RedirectResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthRegisterResponse as SocialstreamOAuthRegisterResponse;
use Laravel\Fortify\Http\Responses\RegisterResponse;

/**
 * Socialstream's response after registering with Google or GitHub, back to
 * the old site for an observer who came from there.
 */
class OAuthRegisterResponse extends SocialstreamOAuthRegisterResponse
{
    public function toResponse($request): RedirectResponse|RegisterResponse
    {
        if (! $request->wantsJson() && ($back = LegacySiteReturn::redirect($request))) {
            return $back;
        }

        return parent::toResponse($request);
    }
}
