<?php

namespace App\Jobs;

use App\Models\NewsItem;
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
 * Translates the title and text of a written news item (#960) into the other
 * languages of the site with Google Translate, into news_items.translations.
 * Queued when the item is saved; when Google refuses, the job tries again an
 * hour later.
 */
class TranslateNewsItem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;

    public int $tries = 5;

    public function __construct(public readonly int $newsItemId)
    {
    }

    public function handle(DescriptionTranslator $translator): void
    {
        $item = NewsItem::find($this->newsItemId);
        if ($item === null || $item->isAutomatic() || ! config('app.translate_observations')) {
            return;
        }
        if (DescriptionTranslations::paused()) {
            $this->release(3600);

            return;
        }

        $translations = [];
        foreach (array_values(config('app.available_locales')) as $language) {
            if ($language === $item->language) {
                continue;
            }
            try {
                $translations[$language] = [
                    'title' => (string) $translator->translate((string) $item->title, $item->language, $language),
                    'body'  => (string) $translator->translate(html_entity_decode((string) $item->body), $item->language, $language),
                ];
                usleep(250_000);
            } catch (RateLimitException|TranslationRequestException $e) {
                DescriptionTranslations::pause();
                Log::warning('TranslateNewsItem: Google refused, trying again in an hour', ['news_item_id' => $item->id, 'error' => $e->getMessage()]);
                $this->release(3600);

                return;
            } catch (\Throwable $e) {
                Log::warning('TranslateNewsItem: translation failed', ['news_item_id' => $item->id, 'language' => $language, 'error' => $e->getMessage()]);
            }
        }

        // Saved without touching the timestamps, so the item's text is not seen as changed.
        $item->timestamps = false;
        $item->forceFill(['translations' => $translations])->saveQuietly();
    }
}
