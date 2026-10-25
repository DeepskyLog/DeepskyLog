<?php

namespace App\Services\Observations;

use App\Jobs\TranslateObservationDescriptions;
use App\Models\Observation;
use App\Models\ObservationTranslation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * The translated descriptions of the observations (#310), stored in
 * observation_translations by TranslateObservationDescriptions.
 *
 * Guests read the description in the language of the site, observers who
 * turned on "translate the observations" in their own language. A guest's
 * request never calls Google Translate or queues a translation (every page a
 * visitor or crawler opens would): the scheduled
 * observations:translate-descriptions translates new observations into all
 * languages of the site. For an observer who asked for translations, a card
 * shows the description and queues the missing translation; all
 * observations queued on a page are translated by one job after the
 * response, and the next view of the page shows them.
 */
class DescriptionTranslations
{
    /** How long an observation stays queued, so reloading a page does not queue it again. */
    private const QUEUED_SECONDS = 600;

    /** How long all translations stop after Google refused one. */
    public const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'observation_translations:paused';

    /** @var array<string, array<int, int>> observation ids per language */
    private static array $pending = [];

    /**
     * Loaded translations per language, then observation id (false: not
     * translated yet). Per instance, which is scoped to a request or job.
     *
     * @var array<string, array<int, string|false|null>>
     */
    private array $loaded = [];

    private static function instance(): self
    {
        return app(self::class);
    }

    /**
     * The language the current viewer reads the observations in, or null when
     * they read them as written: the site language for a guest, their own
     * language for an observer who turned on translations.
     */
    public static function language(): ?string
    {
        $user = Auth::user();
        $language = $user ? ($user->translate ? $user->language : null) : app()->getLocale();

        return in_array($language, self::siteLanguages(), true) ? $language : null;
    }

    /** @return list<string> */
    public static function siteLanguages(): array
    {
        return array_values(config('app.available_locales', []));
    }

    /**
     * Load the translations of these observations for the current viewer in
     * one query, so the cards of a page do not query one by one.
     *
     * @param  iterable<int|string>  $observationIds
     */
    public static function preload(iterable $observationIds): void
    {
        $language = self::language();
        if ($language === null) {
            return;
        }

        $loaded = &self::instance()->loaded;
        $ids = collect($observationIds)->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === 0 || array_key_exists($id, $loaded[$language] ?? []))
            ->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }

        $rows = ObservationTranslation::whereIn('observation_id', $ids)->where('language', $language)
            ->pluck('description', 'observation_id');
        foreach ($ids as $id) {
            // A missing row is remembered as false: not translated yet
            $loaded[$language][$id] = $rows->has($id) ? $rows->get($id) : false;
        }
    }

    /**
     * The translation of the observation's description the current viewer
     * reads, or null when they read the description itself.
     *
     * @param  bool  $now  translate in the request when the translation is missing
     *                     (the detail page of one observation), only for an observer who asked for translations
     */
    public static function forViewer(Observation $observation, bool $now = false): ?string
    {
        $language = self::language();
        if ($language === null || $observation->language === $language
            || trim(strip_tags((string) $observation->description)) === '') {
            return null;
        }

        $loaded = &self::instance()->loaded;
        self::preload([$observation->id]);
        $translation = $loaded[$language][$observation->id];
        if ($translation !== false) {
            return $translation;
        }

        if (Auth::guest()) {
            return null;
        }

        if ($now) {
            TranslateObservationDescriptions::dispatchSync([$observation->id], $language);
            unset($loaded[$language][$observation->id]);
            self::preload([$observation->id]);

            return $loaded[$language][$observation->id] ?: null;
        }

        self::queue($observation->id, $language);

        return null;
    }

    /**
     * Queue the observation for translation after the response, unless it was
     * queued recently.
     */
    public static function queue(int $observationId, string $language): void
    {
        if (Cache::add("observation_translation_queued:{$observationId}:{$language}", true, self::QUEUED_SECONDS)) {
            if (self::$pending === []) {
                defer(fn () => self::dispatchPending());
            }
            self::$pending[$language][] = $observationId;
        }
    }

    /** Forget the translations of an observation whose description or language changed. */
    public static function forget(Observation $observation): void
    {
        ObservationTranslation::where('observation_id', $observation->id)->delete();
        $loaded = &self::instance()->loaded;
        foreach (self::siteLanguages() as $language) {
            unset($loaded[$language][$observation->id]);
            Cache::forget("observation_translation_queued:{$observation->id}:{$language}");
        }
    }

    /** Stop all translations for PAUSE_SECONDS, after Google refused one. */
    public static function pause(): void
    {
        Cache::put(self::PAUSE_KEY, true, self::PAUSE_SECONDS);
    }

    public static function paused(): bool
    {
        return Cache::has(self::PAUSE_KEY);
    }

    private static function dispatchPending(): void
    {
        $pending = self::$pending;
        self::$pending = [];

        foreach ($pending as $language => $ids) {
            TranslateObservationDescriptions::dispatch(array_values(array_unique($ids)), $language);
        }
    }
}
