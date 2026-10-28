<?php

namespace App\Jobs;

use App\Models\StarParty;
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
 * Translates the description of a starparty (#315) into the other languages
 * of the site with Google Translate, into star_parties.translations. Queued
 * when the description changes; when Google refuses, the job tries again an
 * hour later.
 */
class TranslateStarParty implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public int $tries = 5;

    public function __construct(public readonly int $starPartyId)
    {
    }

    public function handle(DescriptionTranslator $translator): void
    {
        $party = StarParty::find($this->starPartyId);
        if ($party === null || trim(strip_tags((string) $party->description)) === '' || ! config('app.translate_observations')) {
            return;
        }
        if (DescriptionTranslations::paused()) {
            $this->release(3600);

            return;
        }

        $translations = [];
        foreach (array_values(config('app.available_locales')) as $language) {
            if ($language === $party->language) {
                continue;
            }
            try {
                $translations[$language] = (string) $translator->translate(html_entity_decode((string) $party->description), $party->language, $language);
                usleep(250_000);
            } catch (RateLimitException|TranslationRequestException $e) {
                DescriptionTranslations::pause();
                Log::warning('TranslateStarParty: Google refused, trying again in an hour', ['star_party_id' => $party->id, 'error' => $e->getMessage()]);
                $this->release(3600);

                return;
            } catch (\Throwable $e) {
                Log::warning('TranslateStarParty: translation failed', ['star_party_id' => $party->id, 'language' => $language, 'error' => $e->getMessage()]);
            }
        }

        $party->timestamps = false;
        $party->forceFill(['translations' => $translations])->saveQuietly();
    }
}
