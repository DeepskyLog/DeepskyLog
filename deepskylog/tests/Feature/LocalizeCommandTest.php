<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// The command is exercised against a throwaway view directory and lang path so
// the real resources/lang/*.json files are never touched by the test suite.
beforeEach(function () {
    $this->viewsDir = base_path('storage/framework/testing/localize-views');
    $this->langDir = base_path('storage/framework/testing/localize-lang');

    File::ensureDirectoryExists($this->viewsDir);
    File::ensureDirectoryExists($this->langDir);

    config()->set('app.locale', 'en');
    config()->set('localize.search.dirs', ['storage/framework/testing/localize-views']);
    config()->set('localize.search.exclude', []);
    config()->set('localize.search.patterns', ['*.php']);
    config()->set('localize.search.functions', ['__', 'trans', '@lang']);
    config()->set('localize.sort', true);

    app()->useLangPath($this->langDir);

    File::put($this->viewsDir.'/page.blade.php', <<<'BLADE'
        <p>{{ __('Zebra') }}</p>
        <p>{{ trans('Apple') }}</p>
        <p>@lang('Mango')</p>
        <p>{{ __('observations.visibility.great') }}</p>
        BLADE);
});

afterEach(function () {
    File::deleteDirectory($this->viewsDir);
    File::deleteDirectory($this->langDir);
});

function translations(string $locale): array
{
    return json_decode(File::get(lang_path("{$locale}.json")), true);
}

it('collects the strings of every configured function, untranslated and sorted', function () {
    $this->artisan('localize', ['lang' => 'nl'])->assertSuccessful();

    $translations = translations('nl');

    expect(array_keys($translations))->toBe(['Apple', 'Mango', 'Zebra'])
        ->and(array_values($translations))->toBe(['', '', '']);
});

it('leaves dotted short keys out of the json file', function () {
    $this->artisan('localize', ['lang' => 'nl'])->assertSuccessful();

    expect(translations('nl'))->not->toHaveKey('observations.visibility.great');
});

it('uses the string itself as the translation for the app locale', function () {
    $this->artisan('localize', ['lang' => 'en'])->assertSuccessful();

    expect(translations('en'))->toBe(['Apple' => 'Apple', 'Mango' => 'Mango', 'Zebra' => 'Zebra']);
});

it('keeps existing translations and only adds the new strings', function () {
    File::put(lang_path('nl.json'), json_encode(['Zebra' => 'Zebra (nl)']));

    $this->artisan('localize', ['lang' => 'nl'])->assertSuccessful();

    expect(translations('nl'))->toBe(['Apple' => '', 'Mango' => '', 'Zebra' => 'Zebra (nl)']);
});

it('keeps strings that are no longer used unless --remove-missing is passed', function () {
    File::put(lang_path('nl.json'), json_encode(['Retired' => 'Retired (nl)']));

    $this->artisan('localize', ['lang' => 'nl'])->assertSuccessful();

    expect(translations('nl'))->toHaveKey('Retired');

    $this->artisan('localize', ['lang' => 'nl', '--remove-missing' => true])->assertSuccessful();

    expect(translations('nl'))->not->toHaveKey('Retired')
        ->and(array_keys(translations('nl')))->toBe(['Apple', 'Mango', 'Zebra']);
});

it('writes every locale passed as a comma separated list', function () {
    $this->artisan('localize', ['lang' => 'nl,fr'])->assertSuccessful();

    expect(array_keys(translations('nl')))->toBe(['Apple', 'Mango', 'Zebra'])
        ->and(array_keys(translations('fr')))->toBe(['Apple', 'Mango', 'Zebra']);
});

it('refuses to run on a corrupt locale file instead of discarding its translations', function () {
    File::put(lang_path('nl.json'), '{"Zebra": "Zebra (nl)",,,}');

    expect(fn () => Artisan::call('localize', ['lang' => 'nl']))
        ->toThrow(RuntimeException::class, 'is not valid JSON');

    expect(File::get(lang_path('nl.json')))->toBe('{"Zebra": "Zebra (nl)",,,}');
});
