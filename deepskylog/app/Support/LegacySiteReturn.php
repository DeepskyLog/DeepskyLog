<?php

namespace App\Support;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sends an observer who came from the old DeepskyLog site to register back
 * there. The old site links to /register?return_to=<its address>; the address
 * is only kept when it lies on the old site (services.legacy_site.url), so
 * the parameter can't send anyone elsewhere. It is used after registering
 * and, as the account still has to be verified then, again after verifying
 * the e-mail address, when it is forgotten.
 */
class LegacySiteReturn
{
    public const SESSION_KEY = 'legacy_site_return';

    public static function remember(Request $request): void
    {
        $url = $request->query('return_to');
        if (is_string($url) && self::isAllowed($url)) {
            $request->session()->put(self::SESSION_KEY, $url);
        }
    }

    public static function isAllowed(string $url): bool
    {
        // The old site runs under several domains (www.deepskylog.be, .nl, ...), separated by commas
        $sites = config('services.legacy_site.url');
        if (! is_string($sites)) {
            return false;
        }

        foreach (explode(',', $sites) as $site) {
            $site = rtrim(trim($site), '/');
            // With the slash, http://localhost/ does not allow http://localhost.example.com.
            if ($site !== '' && ($url === $site || str_starts_with($url, $site.'/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The redirect back to the old site, or null when the observer did not
     * come from there. The address is kept while the e-mail address still
     * has to be verified.
     */
    public static function redirect(Request $request): ?RedirectResponse
    {
        $user = $request->user();
        $done = ! $user instanceof MustVerifyEmail || $user->hasVerifiedEmail();
        $url = $done
            ? $request->session()->pull(self::SESSION_KEY)
            : $request->session()->get(self::SESSION_KEY);

        return is_string($url) && self::isAllowed($url) ? redirect()->away($url) : null;
    }
}
