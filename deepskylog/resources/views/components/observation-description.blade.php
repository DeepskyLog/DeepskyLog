{{--
    The description of an observation, translated into the language of the
    viewer when there is a translation (#310): the site language for guests,
    their own language for observers who turned on translations. A
    description the observer wrote in the viewer's language (#321) is shown
    instead, to every viewer who reads the site in that language. The
    original stays one click away.
--}}
@props ([
    "observation",
    // Translate in the request when the translation is missing (one observation, for observers who asked for it)
    "now" => false,
])
@php
    $translation = \App\Services\Observations\DescriptionTranslations::forViewer($observation, $now);
    $byObserver = $translation !== null && \App\Services\Observations\DescriptionTranslations::byObserver($observation);
    $languageName = null;
    if ($translation !== null && !empty($observation->language)) {
        try {
            $languageName = \Languages::lookup([$observation->language], app()->getLocale())[$observation->language] ?? null;
        } catch (\Throwable $e) {
            $languageName = null;
        }
    }
@endphp

@if ($translation !== null)
    <div x-data="{ original: false }">
        <div x-show="!original">
            {!! \App\Support\Html::purify($translation, true) !!}
        </div>
        <div x-show="original" x-cloak>
            {!! \App\Support\Html::purify($observation->description, true) !!}
        </div>
        <p class="mt-2 text-xs text-gray-400">
            @if ($byObserver)
                {{ $languageName ? __("Translated by the observer · original language: :language", ["language" => $languageName]) : __("Translated by the observer") }}
            @else
                {{ $languageName ? __("Translated by Google · original language: :language", ["language" => $languageName]) : __("Translated by Google") }}
            @endif
            &middot;
            <button
                type="button"
                class="underline hover:text-gray-200"
                x-on:click="original = !original"
                x-text="original ? @js(__("Show translation")) : @js(__("Show original"))"
            >
                {{ __("Show original") }}
            </button>
        </p>
    </div>
@else
    {!! \App\Support\Html::purify($observation->description, true) !!}
@endif
