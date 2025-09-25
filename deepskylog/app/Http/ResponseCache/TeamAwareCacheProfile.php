<?php

namespace App\Http\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

/**
 * Cache profile that avoids caching requests which are likely session- or
 * user-specific. This prevents serving cached pages to authenticated users
 * or when the session cookie is present.
 */
class TeamAwareCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        // Never cache non-GET requests (parent handles this but keep explicit)
        if (!in_array($request->method(), ['GET', 'HEAD'])) {
            return false;
        }

        // If the request has an Authorization header, don't cache
        if ($request->headers->has('Authorization')) {
            return false;
        }

        // If the request has the session cookie set, likely user-specific
        $sessionCookieName = config('session.cookie');
        if ($sessionCookieName && $request->cookies->has($sessionCookieName)) {
            return false;
        }

        // If the user is authenticated, don't cache
        try {
            if ($request->user()) {
                return false;
            }
        } catch (\Throwable $e) {
            // If something goes wrong while checking user, be conservative
            return false;
        }

        return parent::shouldCacheRequest($request);
    }
}
