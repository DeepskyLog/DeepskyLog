<?php

namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    /**
     * Boot the trait and register model event listeners to clear response cache on changes.
     */
    public static function bootClearsResponseCache()
    {
        // Targeted invalidation: forget the homepage URL when models that affect
        // the welcome page change. Fall back to clearing the entire response
        // cache if forgetting the specific URL fails for any reason.
        $invalidateHomepage = function ($model) {
            // Invalidate all response cache entries. We previously attempted
            // to forget('/') but with team-aware hashing the same URL may
            // produce multiple cache entries (per user/team). Clearing the
            // whole response cache ensures stale team-specific pages are removed.
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                // swallow to avoid breaking model save
            }
        };

        static::created($invalidateHomepage);
        static::updated($invalidateHomepage);
        static::deleted($invalidateHomepage);
    }
}
