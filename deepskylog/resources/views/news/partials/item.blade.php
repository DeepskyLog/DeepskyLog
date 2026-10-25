{{-- One news item (#960): a written one in the reader's language, or an automatic one with its local circumstances --}}
@php
    $local = $item->isAutomatic()
        ? \App\Services\News\LocalCircumstances::for($item, $location)
        : null;
@endphp
<article class="space-y-2 text-sm text-gray-300" id="news-{{ $item->id }}">
    @if ($item->isAutomatic())
        @includeIf ("news.events." . $item->kind,
            [
                "item" => $item,
                "data" => $item->data ?? [],
                "local" => $local,
                "location" => $location
            ])
    @else
        <div class="prose prose-invert max-w-none text-gray-300">
            {!!
                \App\Support\Html::purify(
                    $item->displayBody(),
                    true,
                )
            !!}
        </div>
        @if ($item->isTranslated())
            <p class="text-xs text-gray-500">{{
                __(
                    "Translated by Google",
                )
            }}</p>
        @endif
    @endif
</article>
