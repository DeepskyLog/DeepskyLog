<?php

namespace App\Http\ResponseCache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\Hasher\DefaultHasher;

/**
 * Team-aware hasher for ResponseCache.
 *
 * This extends Spatie's DefaultHasher and adds the authenticated user's
 * id and current_team_id (if any) into the generated hash. That ensures
 * cached responses are specific per user/team so switching teams will
 * fetch the correct cached page.
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
        $base = parent::getHashFor($request);

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
