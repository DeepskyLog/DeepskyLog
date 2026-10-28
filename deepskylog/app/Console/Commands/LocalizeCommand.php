<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Collection;
use JsonException;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Scans the configured directories for translatable strings and merges any new
 * ones into resources/lang/<locale>.json with an empty value, ready to be
 * translated by hand.
 *
 * This replaces amirami/localizator, which never gained Laravel 13 support.
 * The extraction and merge behaviour is deliberately identical to that package
 * so the generated files stay byte for byte the same.
 *
 * Only the JSON translation files are written. The short-key files in
 * resources/lang/<locale>/ (observations.php, contrast.php, ...) are maintained
 * by hand: a generator rewrites them with var_export(), which drops their
 * comments and grouping, and cannot see through interpolated keys such as
 * __("observations.visibility.$visKey"). Dotted keys are therefore collected
 * and then discarded rather than written anywhere.
 */
class LocalizeCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'localize
                            {lang? : Comma separated list of locales (defaults to the app locale)}
                            {--remove-missing : Also drop keys that are no longer found in the scanned files}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate locale JSON files with the translatable strings found in the scanned files.';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $locales = $this->locales();

        $this->info('Localizing: '.implode(', ', $locales));

        $keys = $this->parseKeys();

        $this->line('Found '.$keys->count().' translatable string(s) in '.implode(', ', config('localize.search.dirs')).'.');

        $rows = [];

        foreach ($locales as $locale) {
            $rows[] = $this->localize($locale, $keys);
        }

        $this->newLine();
        $this->table(['Locale', 'Total', 'Added', 'Removed', 'Untranslated'], $rows);

        return self::SUCCESS;
    }

    /**
     * The locales to write, taken from the argument or falling back to the app locale.
     *
     * @return array<int, string>
     */
    protected function locales(): array
    {
        if (! $lang = $this->argument('lang')) {
            return [config('app.locale')];
        }

        return array_values(array_filter(array_map('trim', explode(',', $lang))));
    }

    /**
     * Every translatable string found in the scanned files, dotted short keys excluded.
     */
    protected function parseKeys(): Collection
    {
        return (new Collection($this->files()))
            ->map(fn (SplFileInfo $file) => $this->stringsIn($file))
            ->flatten()
            ->map(fn (string $string) => stripslashes($string))
            ->reject(fn (string $string) => $this->isShortKey($string))
            ->unique()
            ->values();
    }

    /**
     * The files to scan for translatable strings.
     */
    protected function files(): Finder
    {
        $directories = array_map(
            static fn (string $dir) => base_path($dir),
            config('localize.search.dirs')
        );

        return (new Finder)
            ->in($directories)
            ->notPath(config('localize.search.exclude'))
            ->name(config('localize.search.patterns'))
            ->files();
    }

    /**
     * The translatable strings passed to one of the configured functions in a single file.
     */
    protected function stringsIn(SplFileInfo $file): Collection
    {
        $keys = new Collection;
        $contents = $file->getContents();

        foreach (config('localize.search.functions') as $function) {
            if (preg_match_all($this->searchPattern($function), $contents, $matches)) {
                $keys->push($matches[2]);
            }
        }

        return $keys->isEmpty() ? $keys : $keys->flatten()->unique();
    }

    /**
     * Match the first, quoted argument of a call to the given translation function.
     */
    protected function searchPattern(string $function): string
    {
        return '/('.preg_quote($function, '/').')\([\r\n\s]{0,}\h*[\'"](.+)[\'"]\h*[\r\n\s]{0,}[),]/U';
    }

    /**
     * Dotted keys such as observations.visibility.great resolve against the
     * hand maintained PHP files, so they never belong in the JSON files.
     */
    protected function isShortKey(string $key): bool
    {
        return (bool) preg_match('/^[^.\s]\S*\.\S*[^.\s]$/', $key);
    }

    /**
     * Merge the found keys into the locale file, keeping the existing translations.
     *
     * @return array<int, int|string>
     */
    protected function localize(string $locale, Collection $keys): array
    {
        $found = $keys->combine($this->emptyValuesFor($locale, $keys));
        $existing = $this->existingTranslations($locale);

        $translations = $found
            ->merge($this->option('remove-missing') ? $existing->intersectByKeys($found) : $existing)
            ->when(
                config('localize.sort'),
                fn (Collection $translations) => $translations->sortKeys(SORT_NATURAL | SORT_FLAG_CASE)
            );

        $this->write($locale, $translations);

        return [
            $locale,
            $translations->count(),
            $translations->count() - $existing->intersectByKeys($translations)->count(),
            $existing->count() - $existing->intersectByKeys($translations)->count(),
            $translations->filter(fn ($value) => $value === '')->count(),
        ];
    }

    /**
     * The value every newly found key starts out with.
     *
     * Strings are their own key, so for the app locale itself the key doubles as
     * the translation. Every other locale starts out untranslated.
     */
    protected function emptyValuesFor(string $locale, Collection $keys): Collection
    {
        return $locale === config('app.locale')
            ? $keys
            : (new Collection)->pad($keys->count(), '');
    }

    /**
     * The translations already present in the locale file.
     */
    protected function existingTranslations(string $locale): Collection
    {
        if (! is_file($file = lang_path("{$locale}.json"))) {
            return new Collection;
        }

        try {
            $decoded = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // Bailing out here rather than starting from an empty set: writing the
            // file now would silently discard every existing translation.
            throw new RuntimeException(
                "resources/lang/{$locale}.json is not valid JSON ({$e->getMessage()}). ".
                'Fix or restore the file before running localize again.', 0, $e
            );
        }

        if (! is_array($decoded)) {
            throw new RuntimeException("resources/lang/{$locale}.json does not contain a JSON object.");
        }

        return new Collection($decoded);
    }

    /**
     * Write the locale file, matching the formatting of the existing ones.
     */
    protected function write(string $locale, Collection $translations): void
    {
        file_put_contents(
            lang_path("{$locale}.json"),
            json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
