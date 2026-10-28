<?php

namespace App\Http\Responses;

use App\Support\LegacySiteReturn;
use Laravel\Fortify\Http\Responses\RegisterResponse as FortifyRegisterResponse;

/**
 * Fortify's response after registering, back to the old site for an observer
 * who came from there.
 */
class RegisterResponse extends FortifyRegisterResponse
{
    public function toResponse($request)
    {
        if (! $request->wantsJson() && ($back = LegacySiteReturn::redirect($request))) {
            return $back;
        }

        return parent::toResponse($request);
    }
}
