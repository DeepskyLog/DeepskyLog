@extends("layout.master")

<h1>Test</h1>

{{ _i('Translated string') }}

{!! LaravelGettext::getSelector([
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',
    'nl' => 'Nederlands',
    'sv' => 'Svenska',
    'fr' => 'Français'
])->render(); !!}
