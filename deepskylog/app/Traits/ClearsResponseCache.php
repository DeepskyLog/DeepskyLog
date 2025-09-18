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
            try {
                // Try to forget only the cached homepage entry. If the cache
                // profile uses a suffix or complex keying this may not work;
                // in that case the fallback below clears all cached responses.
                ResponseCache::forget('/');
            } catch (\Throwable $e) {
                try {
                    ResponseCache::clear();
                } catch (\Throwable $e) {
                    // swallow to avoid breaking model save
                }
            }
        };

        static::created($invalidateHomepage);
        static::updated($invalidateHomepage);
        static::deleted($invalidateHomepage);
    }
}
