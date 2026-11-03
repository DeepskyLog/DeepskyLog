@props (["objectName", "slug"])

{{-- One drawing per aperture class of a deep-sky object, linking to the comparison page (object page) --}}
@php
    $comparison = app(\App\Services\Observations\DrawingComparison::class);
    $highlights = [];
    try {
        $highlights = $comparison->highlights($comparison->forObject($objectName));
    } catch (\Throwable $_) {
        $highlights = [];
    }
    $inches = (bool) (auth()->user()?->showInches ?? false);
    // The viewer's own drawings of the object over time, when they drew it more than once
    $viewer = auth()->user();
    $ownDrawings = 0;
    try {
        $ownDrawings = $viewer ? app(\App\Services\Observations\ObserverDrawings::class)->count($objectName, $viewer->username) : 0;
    } catch (\Throwable $_) {
        $ownDrawings = 0;
    }
    if (count($highlights) > 1) {
        \App\Services\Observations\ObservationCardData::instance()->preload(array_values($highlights));
    }
@endphp

@if (count($highlights) <= 1 && $ownDrawings > 1)
    <div class="mt-6">
        <a
            href="{{ route("object.drawings.observer", ["slug" => $slug, "observer" => $viewer->slug]) }}"
            class="text-sm text-indigo-400 hover:text-indigo-300"
        >
            {{ __("Your drawings over time") }} ({{ $ownDrawings }})
        </a>
    </div>
@endif

@if (count($highlights) > 1)
    <div class="mt-6">
        <div class="flex flex-wrap items-baseline gap-3">
            <h2 class="text-xl font-semibold text-white">{{ __("Drawings by aperture") }}</h2>
            <a
                href="{{ route("object.drawings.compare", ["slug" => $slug]) }}"
                class="text-sm text-indigo-400 hover:text-indigo-300"
            >
                {{ __("Compare drawings") }}
            </a>
            @if ($ownDrawings > 1)
                <a
                    href="{{ route("object.drawings.observer", ["slug" => $slug, "observer" => $viewer->slug]) }}"
                    class="text-sm text-indigo-400 hover:text-indigo-300"
                >
                    {{ __("Your drawings over time") }} ({{ $ownDrawings }})
                </a>
            @endif
        </div>
        <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($highlights as $key => $drawing)
                <div class="min-w-0">
                    <div class="mb-1 text-sm font-semibold text-gray-200">
                        {{ \App\Services\Observations\DrawingComparison::label($key, $inches) }}
                    </div>
                    <x-drawing-compare-card
                        :observation="$drawing"
                        :inches="$inches"
                    />
                </div>
            @endforeach
        </div>
    </div>
@endif
