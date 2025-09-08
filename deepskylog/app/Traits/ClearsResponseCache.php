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
        // Clear entire response cache on create/update/delete. Adjust if you want targeted clears.
        static::created(function ($model) {
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                // swallow to avoid breaking model save
            }
        });

        static::updated(function ($model) {
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                // swallow
            }
        });

        static::deleted(function ($model) {
            try {
                ResponseCache::clear();
            } catch (\Throwable $e) {
                // swallow
            }
        });
    }
}
