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
        // Invalidate all response cache entries. With team-aware hashing the same
        // URL produces several cache entries (per locale, user and team), so
        // forgetting single URLs is not enough.
        static::created(fn () => static::clearResponseCache());
        static::updated(function ($model) {
            if ($model->responseCacheRelevantChanges()) {
                static::clearResponseCache();
            }
        });
        static::deleted(fn () => static::clearResponseCache());
    }

    /**
     * Attributes whose changes never show up on a cached page (for example
     * login tokens). Updates touching only these do not clear the cache.
     *
     * @return array<int, string>
     */
    protected function responseCacheIgnoredAttributes(): array
    {
        return [];
    }

    protected function responseCacheRelevantChanges(): bool
    {
        $ignored = array_merge($this->responseCacheIgnoredAttributes(), [$this->getUpdatedAtColumn()]);

        return array_diff(array_keys($this->getChanges()), $ignored) !== [];
    }

    /**
     * Clear the response cache. During an HTTP request this happens once,
     * after the response has been sent, however many models were saved; in
     * the console (queue workers, commands, tests) it happens immediately,
     * since those processes may run for a long time.
     */
    protected static function clearResponseCache(): void
    {
        $clear = function () {
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                // swallow to avoid breaking model save
            }
        };

        if (app()->runningInConsole()) {
            $clear();

            return;
        }

        // Shared by all models using the trait, and reset with the container.
        if (app()->bound('responsecache.clear-pending')) {
            return;
        }

        app()->instance('responsecache.clear-pending', true);
        app()->terminating(function () use ($clear) {
            app()->forgetInstance('responsecache.clear-pending');
            $clear();
        });
    }
}
