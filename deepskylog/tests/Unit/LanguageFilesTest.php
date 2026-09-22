<?php

// A corrupt lang JSON file is silently reset to an empty translation set by
// `php artisan localize <lang>`: the localizator json_decode()s the existing
// file, gets null, and writes back only the freshly scanned keys with empty
// values. These tests catch the corruption before the translations are lost.

test('every json language file contains valid json', function () {
    $files = glob(lang_path('*.json'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $contents = file_get_contents($file);
        $decoded = json_decode($contents, true);

        expect(json_last_error())
            ->toBe(JSON_ERROR_NONE, basename($file).' is not valid JSON: '.json_last_error_msg());
        expect($decoded)->toBeArray(basename($file).' does not contain a JSON object.');
        expect($decoded)->not->toBeEmpty(basename($file).' contains no translations.');
    }
});

test('every php language file returns an array', function () {
    $files = glob(lang_path('*/*.php'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        expect(require $file)
            ->toBeArray($file.' does not return an array.');
    }
});
