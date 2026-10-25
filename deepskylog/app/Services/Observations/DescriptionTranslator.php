<?php

namespace App\Services\Observations;

use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Translates an observation's description with Google Translate (the free
 * endpoint of stichoza/google-translate-php). Kept apart so another provider
 * can take its place, and the tests can replace it.
 *
 * Throws the library's RateLimitException or TranslationRequestException when
 * Google refuses or cannot be reached, LargeTextException for a text over
 * 5000 characters.
 */
class DescriptionTranslator
{
    private ?string $detectedSource = null;

    /**
     * @param  string|null  $source  the language of the text, null to let Google detect it
     */
    public function translate(string $text, ?string $source, string $target): ?string
    {
        $translator = new GoogleTranslate($target, $source, ['timeout' => 10.0]);
        $translated = $translator->translate($text);
        $this->detectedSource = $translator->getLastDetectedSource();

        return $translated;
    }

    /** The language Google found in the last text, when no source was given. */
    public function detectedSource(): ?string
    {
        return $this->detectedSource;
    }
}
