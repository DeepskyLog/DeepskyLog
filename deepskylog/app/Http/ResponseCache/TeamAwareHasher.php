<?php

namespace App\Http\ResponseCache;

use App\Support\EphemerisDate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Hasher\DefaultHasher;

/**
 * Team-aware hasher for ResponseCache.
 *
 * This extends Spatie's DefaultHasher and adds the current locale and the
 * authenticated user's id and current_team_id (if any) into the generated
 * hash. That ensures cached responses are specific per language and
 * user/team, so a guest who chose another language or a user switching
 * teams gets the correct page. The locale is known here because the
 * Localization middleware runs before CacheResponse. The ephemeris date
 * (chosen in the ephemeris aside, else today) is part of the hash as well:
 * the aside and the ephemerides on the page depend on it.
 *
 * Only the query parameters of responsecache.query_parameters are part of
 * the hash, sorted by name, so the order of the parameters and tracking
 * parameters (responsecache.ignored_query_parameters) do not give new cache
 * entries. TeamAwareCacheProfile does not cache requests with any other
 * parameter.
 */
class TeamAwareHasher extends DefaultHasher
{
    /**
     * Get the cache key / hash for the given request.
     *
     * @param Request $request
     * @return string
     */
    public function getHashFor(Request $request): string
    {
        $base = parent::getHashFor($request) . '|locale:' . app()->getLocale() . '|date:' . EphemerisDate::current();

        try {
            $user = $request->user();
        } catch (\Throwable $e) {
            $user = null;
        }

        if ($user) {
            $suffix = sprintf('user:%s|team:%s', $user->getAuthIdentifier(), $user->current_team_id ?? '0');
            return $base . '|' . md5($suffix);
        }

        return $base;
    }

    protected function getNormalizedRequestUri(Request $request): string
    {
        $query = static::cacheableQuery($request) ?? [];
        $queryString = $query === [] ? '' : '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $request->getBaseUrl() . $request->getPathInfo() . $queryString;
    }

    /**
     * The query parameters the cache is keyed on, sorted by name, or null
     * when the request has a parameter that is neither whitelisted nor a
     * tracking parameter.
     *
     * @return array<string, mixed>|null
     */
    public static function cacheableQuery(Request $request): ?array
    {
        $allowed = config('responsecache.query_parameters', []);
        $ignored = config('responsecache.ignored_query_parameters', []);

        $query = [];
        foreach ($request->query->all() as $name => $value) {
            $name = (string) $name;
            if (Str::is($ignored, $name)) {
                continue;
            }
            if (!Str::is($allowed, $name)) {
                return null;
            }
            $query[$name] = $value;
        }
        ksort($query);

        return $query;
    }
}
