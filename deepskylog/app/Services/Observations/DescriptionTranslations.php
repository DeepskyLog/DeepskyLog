<?php

namespace App\Services\Observations;

use App\Jobs\TranslateObservationDescriptions;
use Illuminate\Support\Facades\Cache;

/**
 * The translated descriptions of the observation cards, for observers who
 * have "translate the observations" on. A card never calls Google Translate
 * itself (a call takes one to two seconds, so a page of 20 cards took half a
 * minute): it shows the cached translation, or the original description and
 * queues the observation, and all observations queued on a page are
 * translated by one TranslateObservationDescriptions job after the response.
 * The next view of the page shows the translation.
 */
class DescriptionTranslations
{
    /** How long an observation stays queued, so reloading a page does not queue it again. */
    private const QUEUED_SECONDS = 600;

    /** @var array<string, array<int, int>> observation ids per "kind:language" */
    private static array $pending = [];

    /**
     * The cached translation of the description, or null after queueing the
     * observation for translation.
     *
     * @param  string  $kind  TranslateObservationDescriptions::DEEPSKY or ::COMET
     */
    public static function get(int $observationId, string $language, string $kind = TranslateObservationDescriptions::DEEPSKY): ?string
    {
        $translated = Cache::get(TranslateObservationDescriptions::cacheKey($kind, $observationId, $language));
        if ($translated !== null) {
            return $translated;
        }

        if (Cache::add("observation_translation_queued:{$kind}:{$observationId}:{$language}", true, self::QUEUED_SECONDS)) {
            if (self::$pending === []) {
                defer(fn () => self::dispatchPending());
            }
            self::$pending["{$kind}:{$language}"][] = $observationId;
        }

        return null;
    }

    private static function dispatchPending(): void
    {
        $pending = self::$pending;
        self::$pending = [];

        foreach ($pending as $group => $ids) {
            [$kind, $language] = explode(':', $group, 2);
            TranslateObservationDescriptions::dispatch(array_values(array_unique($ids)), $language, $kind);
        }
    }
}
