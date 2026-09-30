<?php

namespace App\Http\ResponseCache;

use App\Support\EphemerisDate;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

/**
 * Cache profile that only serves and stores cached pages for guests.
 *
 * A guest with a session cookie gets cached pages too, unless their session
 * holds something that changes the page (see NEUTRAL_SESSION_KEYS): the CSRF
 * token is swapped in by the CsrfTokenReplacer, and the locale and the
 * ephemeris date are part of the hash (TeamAwareHasher). Requests with query
 * parameters the hasher does not key on are not cached either.
 */
class TeamAwareCacheProfile extends CacheAllSuccessfulGetRequests
{
    /**
     * Session keys that do not change how a page renders for a guest. Any
     * other key (flash data, persisted table filters, ...)
     * keeps the request out of the cache.
     */
    private const NEUTRAL_SESSION_KEYS = ['_token', '_previous', '_flash', 'url', 'locale', EphemerisDate::SESSION_KEY];

    /**
     * Checked before a cached page is served and before one is stored, so
     * everything that decides which page a request gets belongs here.
     */
    public function enabled(Request $request): bool
    {
        if (!parent::enabled($request)) {
            return false;
        }

        if ($request->headers->has('Authorization')) {
            return false;
        }

        if (TeamAwareHasher::cacheableQuery($request) === null) {
            return false;
        }

        try {
            if ($request->user()) {
                return false;
            }

            if ($request->hasSession()) {
                $session = $request->session();

                // Neither serve nor store a cached page while the session holds
                // flash data for this request (status messages, validation
                // errors, old input), otherwise the cached page would be shown
                // without them.
                if (!empty($session->get('_flash.old', []))) {
                    return false;
                }

                if (array_diff(array_keys($session->all()), self::NEUTRAL_SESSION_KEYS) !== []) {
                    return false;
                }
            }
        } catch (\Throwable $e) {
            // If something goes wrong while checking the user, be conservative
            return false;
        }

        return true;
    }

    public function shouldCacheRequest(Request $request): bool
    {
        // Never cache non-GET requests (parent handles this but keep explicit)
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return false;
        }

        return $this->enabled($request) && parent::shouldCacheRequest($request);
    }
}
