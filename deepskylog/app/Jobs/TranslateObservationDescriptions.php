<?php

namespace App\Jobs;

use App\Models\ObservationsOld;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateObservationDescriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly array $observationIds,
        public readonly string $language,
    ) {}

    public function handle(): void
    {
        if (empty($this->observationIds) || empty($this->language)) {
            return;
        }

        $tr = new GoogleTranslate($this->language, null, ['timeout' => 10.0]);

        foreach ($this->observationIds as $id) {
            $cacheKey = 'observation_deepsky_translation:' . $id . ':' . $this->language;

            if (Cache::has($cacheKey)) {
                continue; // Already translated
            }

            $observation = ObservationsOld::find($id);
            if (! $observation || empty($observation->description)) {
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
