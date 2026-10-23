<?php

namespace App\Jobs;

use App\Models\Observation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Translates the descriptions of observations into a language and caches
 * them for 30 days, under the key the deep-sky or comet cards read
 * (App\Services\Observations\DescriptionTranslations).
 */
class TranslateObservationDescriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const DEEPSKY = 'deepsky';

    public const COMET = 'comet';

    /**
     * Number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly array $observationIds,
        public readonly string $language,
        public readonly string $kind = self::DEEPSKY,
    ) {
    }

    public static function cacheKey(string $kind, int $observationId, string $language): string
    {
        return 'observation_' . $kind . '_translation:' . $observationId . ':' . $language;
    }

    public function handle(): void
    {
        if (empty($this->observationIds) || empty($this->language)) {
            return;
        }

        $tr = new GoogleTranslate($this->language, null, ['timeout' => 10.0]);

        foreach ($this->observationIds as $id) {
            $cacheKey = self::cacheKey($this->kind, (int) $id, $this->language);

            if (Cache::has($cacheKey)) {
                continue; // Already translated
            }

            $observation = Observation::find($id);
            if (!$observation || empty($observation->description)) {
                continue;
            }

            // Skip translation when the observation's language matches the target language
            if (($observation->language ?? 'nl') === $this->language) {
                continue;
            }

            try {
                $translated = $tr->translate(html_entity_decode($observation->description));
                if ($translated !== null) {
                    // Cache for 30 days (same TTL as inline blade caching)
                    Cache::put($cacheKey, $translated, now()->addDays(30));
                }
            } catch (\Throwable $e) {
                Log::warning('TranslateObservationDescriptions: translation failed', [
                    'observation_id' => $id,
                    'language' => $this->language,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
