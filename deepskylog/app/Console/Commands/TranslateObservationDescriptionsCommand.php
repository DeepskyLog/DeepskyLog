<?php

namespace App\Console\Commands;

use App\Jobs\TranslateObservationDescriptions;
use App\Models\Observation;
use App\Services\Observations\DescriptionTranslations;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Queues the newest observations whose description is not yet translated
 * into a language of the site (#310), for the guests who read the site in
 * that language. Scheduled every ten minutes with a small limit, so new
 * observations are translated soon after they are saved and the older ones
 * follow gradually, without running into Google's rate limit. Nothing is
 * queued while translations are paused after Google refused one.
 */
class TranslateObservationDescriptionsCommand extends Command
{
    protected $signature = 'observations:translate-descriptions
        {--apply : Queue the translations}
        {--limit= : Observations per language (default app.translations_per_run)}
        {--language=* : Only these languages of the site}';

    protected $description = 'Queue the translation of the newest untranslated observation descriptions into the languages of the site';

    /** Observations per job, so a job ends well within its timeout. */
    private const PER_JOB = 20;

    /** How long a queued observation is not queued again. */
    private const QUEUED_SECONDS = 3600;

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to queue the translations.');
        }

        if (! config('app.translate_observations')) {
            $this->warn('Translations are turned off (TRANSLATE_OBSERVATIONS).');

            return self::SUCCESS;
        }
        if (DescriptionTranslations::paused()) {
            $this->warn('Translations are paused: Google refused a translation less than an hour ago.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) ($this->option('limit') ?: config('app.translations_per_run', 20)));
        $languages = DescriptionTranslations::siteLanguages();
        if ($this->option('language')) {
            $languages = array_values(array_intersect($languages, (array) $this->option('language')));
        }

        foreach ($languages as $language) {
            $ids = $this->untranslated($language)
                ->limit($limit * 3)
                ->pluck('id')
                ->filter(fn ($id) => ! Cache::has($this->queuedKey((int) $id, $language)))
                ->take($limit)
                ->values();

            if ($apply) {
                foreach ($ids as $id) {
                    Cache::put($this->queuedKey((int) $id, $language), true, self::QUEUED_SECONDS);
                }
                foreach ($ids->chunk(self::PER_JOB) as $chunk) {
                    TranslateObservationDescriptions::dispatch($chunk->values()->all(), $language);
                }
                $this->info("{$language}: queued {$ids->count()}");
            } else {
                $this->info("{$language}: untranslated {$this->untranslated($language)->count()}, would queue {$ids->count()}");
            }
        }

        return self::SUCCESS;
    }

    /** The observations with a description that has no translation into the language, newest first. */
    private function untranslated(string $language)
    {
        return Observation::query()
            ->whereNotNull('description')
            ->where('description', '<>', '')
            ->where(fn ($q) => $q->whereNull('language')->orWhere('language', '<>', $language))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('observation_translations')
                ->whereColumn('observation_translations.observation_id', 'observations.id')
                ->where('observation_translations.language', $language))
            ->orderByDesc('id');
    }

    private function queuedKey(int $id, string $language): string
    {
        return "observation_translation_scheduled:{$id}:{$language}";
    }
}
