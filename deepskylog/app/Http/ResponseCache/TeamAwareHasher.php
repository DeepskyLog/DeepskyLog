<?php

namespace App\Http\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\DefaultHasher;

/**
 * Team-aware hasher for ResponseCache.
 *
 * This extends Spatie's DefaultHasher and adds the current locale and the
 * authenticated user's id and current_team_id (if any) into the generated
 * hash. That ensures cached responses are specific per language and
 * user/team, so a guest who chose another language or a user switching
 * teams gets the correct page. The locale is known here because the
 * Localization middleware runs before CacheResponse.
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
        $base = parent::getHashFor($request) . '|locale:' . app()->getLocale();

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
}
