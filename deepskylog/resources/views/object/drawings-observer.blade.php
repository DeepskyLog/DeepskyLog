<x-app-layout>
    @php
        $cards = \App\Services\Observations\ObservationCardData::instance();
        $first = $drawings->first()?->observed_on;
    @endphp
    <div>
        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-4 flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold text-white">
                    {{ __('Drawings of :object by :observer over time', ['object' => $object->name, 'observer' => $observer->name]) }}
                </h1>
                <a href="{{ $backUrl }}" class="ml-auto inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Back') }}
                </a>
            </header>

            @if ($drawings->isEmpty())
                <p class="text-gray-300">{{ __(':observer has no drawings of this object.', ['observer' => $observer->name]) }}</p>
            @else
                <p class="mb-4 text-sm text-gray-400">
                    {{ __('The drawings side by side, oldest to newest, with the aperture and the sky of each.') }}
                    <a href="{{ route('object.drawings.compare', ['slug' => $object->slug]) }}" class="text-indigo-400 hover:text-indigo-300">{{ __('Compare drawings') }}</a>
                    &middot;
                    <a href="{{ route('observer.show', ['observer' => $observer->slug]) }}" class="text-indigo-400 hover:text-indigo-300">{{ $observer->name }}</a>
                </p>

                <ol class="flex snap-x gap-4 overflow-x-auto pb-4">
                    @foreach ($drawings as $drawing)
                        @php
                            $instrument = $cards->instrument($drawing->instrument_id);
                            $aperture = \App\Services\Observations\DrawingComparison::aperture($drawing->drawing_aperture_mm, $inches);
                            $sqm = is_numeric($drawing->drawing_sqm) ? number_format((float) $drawing->drawing_sqm, 2) : null;
                            $nelm = is_numeric($drawing->drawing_nelm) ? number_format((float) $drawing->drawing_nelm, 1) : null;
                            $fromLocation = (bool) $drawing->drawing_sky_from_location;
                        @endphp
                        <li class="w-64 shrink-0 snap-start sm:w-72">
                            <figure>
                                <a href="{{ $drawing->detailUrl($observer->slug) }}" class="block">
                                    <img
                                        src="{{ $drawing->drawingUrl() }}"
                                        alt="{{ __('Drawing') }}: {{ $object->name }}, {{ $drawing->observed_on?->translatedFormat('j M Y') }}"
                                        loading="lazy"
                                        class="aspect-square w-full rounded bg-black object-contain"
                                    />
                                </a>
                                <figcaption class="mt-1 text-xs text-gray-400">
                                    <div class="text-sm font-semibold text-white">
                                        {{ $loop->iteration }}. {{ $drawing->observed_on?->translatedFormat('j M Y') }}
                                    </div>
                                    @if (! $loop->first && $first && $drawing->observed_on)
                                        <div>{{ __(':time after the first', ['time' => $drawing->observed_on->copy()->locale(app()->getLocale())->diffForHumans($first, \Carbon\CarbonInterface::DIFF_ABSOLUTE, false, 2)]) }}</div>
                                    @endif
                                    <div>
                                        {{ __('Aperture') }}: {{ $aperture ?? __('Unknown aperture') }}
                                        @if ($drawing->magnification)
                                            &middot; {{ $drawing->magnification }}&times;
                                        @endif
                                    </div>
                                    @if ($instrument)
                                        <div class="truncate">{{ html_entity_decode($instrument->fullName()) }}</div>
                                    @endif
                                    <div>
                                        {{ __('Sky') }}:
                                        @if ($sqm || $nelm)
                                            @if ($sqm)
                                                {{ __('SQM') }} {{ $sqm }}
                                            @endif
                                            @if ($sqm && $nelm)
                                                &middot;
                                            @endif
                                            @if ($nelm)
                                                {{ __('NELM') }} {{ $nelm }}
                                            @endif
                                            @if ($fromLocation)
                                                <span title="{{ __('Not recorded with the observation: the sky of its location') }}">({{ __('location') }})</span>
                                            @endif
                                        @else
                                            {{ __('unknown') }}
                                        @endif
                                    </div>
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ol>
                @if ($drawings->count() >= \App\Services\Observations\ObserverDrawings::MAX_DRAWINGS)
                    <p class="mt-3 text-sm text-gray-400">{{ __('The oldest :count are shown.', ['count' => \App\Services\Observations\ObserverDrawings::MAX_DRAWINGS]) }}</p>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
