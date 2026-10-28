<?php

namespace App\Jobs;

use App\Models\Observation;
use App\Models\ObservationTranslation;
use App\Services\Observations\DescriptionTranslations;
use App\Services\Observations\DescriptionTranslator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;

/**
 * Translates the descriptions of observations into a language and stores
 * them in observation_translations (#310), read by the cards, the detail page
 * and the expanded search rows through DescriptionTranslations.
 *
 * When Google refuses (rate limit) or cannot be reached, the job stops and
 * pauses all translations for DescriptionTranslations::PAUSE_SECONDS; the
 * scheduled observations:translate-descriptions picks the rest up later. A
 * description that cannot be translated for another reason (too long) gets a
 * row without a description, so it is not tried again on every run.
 */
class TranslateObservationDescriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of seconds the job can run before timing out.
     */
    public int $timeout = 80;

    /** Pause between two calls, in microseconds, to stay under Google's rate limit. */
    private const PAUSE_BETWEEN_CALLS = 250_000;

    public function __construct(
        public readonly array $observationIds,
        public readonly string $language,
    ) {
    }

    public function handle(DescriptionTranslator $translator): void
    {
        if (empty($this->observationIds) || empty($this->language)
            || !config('app.translate_observations') || DescriptionTranslations::paused()) {
            return;
        }

        $done = ObservationTranslation::whereIn('observation_id', $this->observationIds)
            ->where('language', $this->language)
            ->pluck('observation_id')
            ->flip();

        $observations = Observation::whereIn('id', $this->observationIds)
            ->get(['id', 'description', 'language', 'seen_with_observation_id']);

        $called = false;
        foreach ($observations as $observation) {
            if ($done->has($observation->id) || $observation->language === $this->language) {
                continue;
            }
            // Only markup: nothing to translate, and nothing to pick up again
            if (trim(html_entity_decode(strip_tags((string) $observation->description)), " \u{a0}\t\n\r") === '') {
                $this->store($observation, null);

                continue;
            }

            // The objects seen in the same field share the description of the
            // observation entered with the form: translate it once
            $shared = $this->fromSameField($observation);
            if ($shared !== null) {
                $this->store($observation, $shared);

                continue;
            }

            if ($called) {
                usleep(self::PAUSE_BETWEEN_CALLS);
            }
            $called = true;

            try {
                $source = $observation->language ?: null;
                $translated = $translator->translate(html_entity_decode($observation->description), $source, $this->language);
                // Without a language Google detects one, which may be this language
                if ($source === null && $translator->detectedSource() === $this->language) {
                    $translated = null;
                }
                $this->store($observation, $translated);
            } catch (RateLimitException|TranslationRequestException $e) {
                DescriptionTranslations::pause();
                Log::warning('TranslateObservationDescriptions: Google refused, translations paused', [
                    'observation_id' => $observation->id,
                    'language' => $this->language,
                    'error' => $e->getMessage(),
                ]);

                return;
            } catch (\Throwable $e) {
                $this->store($observation, null);
                Log::warning('TranslateObservationDescriptions: translation failed', [
                    'observation_id' => $observation->id,
                    'language' => $this->language,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * The translation of another observation of the same field with the same
     * description, or null.
     */
    private function fromSameField(Observation $observation): ?string
    {
        $group = $observation->seen_with_observation_id ?? $observation->id;
        $others = Observation::where(fn ($q) => $q->whereKey($group)->orWhere('seen_with_observation_id', $group))
            ->whereKeyNot($observation->id)
            ->where('description', $observation->description)
            ->pluck('id');
        if ($others->isEmpty()) {
            return null;
        }

        return ObservationTranslation::whereIn('observation_id', $others)
            ->where('language', $this->language)
            ->where('by_observer', false)
            ->whereNotNull('description')
            ->value('description');
    }

    private function store(Observation $observation, ?string $translated): void
    {
        ObservationTranslation::updateOrCreate(
            ['observation_id' => $observation->id, 'language' => $this->language],
            ['description' => $translated === '' ? null : $translated],
        );
    }
}
