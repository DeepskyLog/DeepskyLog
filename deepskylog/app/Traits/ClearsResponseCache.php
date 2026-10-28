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
        // forgetting single URLs is not enough. Changes that no cached page
        // shows (private settings, private lists, draft sessions) are skipped:
        // cached pages are only served to guests.
        static::created(function ($model) {
            if ($model->affectsCachedPages()) {
                static::clearResponseCache();
            }
        });
        static::updated(function ($model) {
            if ($model->responseCacheRelevantChanges() && ($model->affectsCachedPages() || $model->affectedCachedPagesBefore())) {
                static::clearResponseCache();
            }
        });
        static::deleted(function ($model) {
            if ($model->affectsCachedPages()) {
                static::clearResponseCache();
            }
        });
    }

    /**
     * Whether guests can see this record, so that a change to it can show up
     * on a cached page. Models with private or draft records override this.
     */
    protected function affectsCachedPages(): bool
    {
        return true;
    }

    /**
     * Whether guests could see this record before the current update (from
     * the original attributes), so that hiding a record clears the cache too.
     */
    protected function affectedCachedPagesBefore(): bool
    {
        $current = $this->getAttributes();
        $this->setRawAttributes($this->getRawOriginal());

        try {
            return $this->affectsCachedPages();
        } finally {
            $this->setRawAttributes($current);
        }
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
     *
     * Public so that writes that bypass the model events (raw queries on
     * related tables) can clear the cache as well.
     */
    public static function clearResponseCache(): void
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
