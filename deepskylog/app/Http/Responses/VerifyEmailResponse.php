<?php

namespace App\Http\Responses;

use App\Support\LegacySiteReturn;
use Laravel\Fortify\Http\Responses\VerifyEmailResponse as FortifyVerifyEmailResponse;

/**
 * Fortify's response after verifying the e-mail address, back to the old
 * site for an observer who registered from there (in the same browser).
 */
class VerifyEmailResponse extends FortifyVerifyEmailResponse
{
    public function toResponse($request)
    {
        if (! $request->wantsJson() && ($back = LegacySiteReturn::redirect($request))) {
            return $back;
        }

        return parent::toResponse($request);
    }
}
