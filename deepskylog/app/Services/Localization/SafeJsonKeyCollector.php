<?php

namespace App\Services\Localization;

use Amirami\Localizator\Collections\JsonKeyCollection;
use Amirami\Localizator\Contracts\Collectable;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Drop-in replacement for the localizator's JsonKeyCollector.
 *
 * The package version json_decode()s resources/lang/<locale>.json without
 * checking for errors. On a malformed file it silently yields an empty
 * collection, after which `php artisan localize <locale>` writes the file back
 * with nothing but the freshly scanned keys and empty values — every existing
 * translation is lost. Failing loudly instead keeps the file intact.
 */
class SafeJsonKeyCollector implements Collectable
{
    public function getTranslated(string $locale): Collection
    {
        $file = lang_path("{$locale}.json");

        if (! file_exists($file)) {
            return new JsonKeyCollection;
        }

        $decoded = json_decode(file_get_contents($file), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "{$file} is not valid JSON (".json_last_error_msg().'). '
                .'Repair the file first — running localize now would overwrite every translation in it.'
            );
        }

        return new JsonKeyCollection($decoded ?? []);
    }
}
