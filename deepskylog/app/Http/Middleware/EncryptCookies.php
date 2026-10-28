<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Set by the layout's script; only read as a timezone identifier.
        \App\Support\EphemerisDate::TIMEZONE_COOKIE,
    ];
}
