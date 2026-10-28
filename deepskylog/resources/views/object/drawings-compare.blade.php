<x-app-layout>
    @php
        $perClass = \App\Services\Observations\DrawingComparison::PER_CLASS;
        $sides = array_values(array_unique(array_filter([$left, $right])));
    @endphp
    <div>
        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-4 flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold text-white">{{ __('Compare drawings of :object', ['object' => $object->name]) }}</h1>
                <a href="{{ $backUrl }}" class="ml-auto inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ __('Back') }}
                </a>
            </header>

            @if (empty($classes))
                <p class="text-gray-300">{{ __('There are no drawings of this object yet.') }}</p>
            @else
                <p class="mb-4 text-sm text-gray-400">
                    {{ __('The drawings of :object side by side, grouped by the aperture of the instrument they were made with.', ['object' => $object->name]) }}
                </p>

                {{-- The aperture classes with their number of drawings --}}
                <div class="mb-4 flex flex-wrap gap-2 text-sm">
                    @foreach ($classes as $key => $drawings)
                        <span @class([
                            'rounded px-2 py-1',
                            'bg-indigo-700 text-white' => in_array($key, $sides, true),
                            'bg-gray-800 text-gray-300' => ! in_array($key, $sides, true),
                        ])>
                            {{ \App\Services\Observations\DrawingComparison::label($key, $inches) }}: {{ $drawings->count() }}
                        </span>
                    @endforeach
                </div>

                @if (count($classes) > 1)
                    <form method="GET" action="{{ route('object.drawings.compare', ['slug' => $object->slug]) }}"
                        class="mb-6 flex flex-wrap items-end gap-4">
                        @foreach (['left' => __('Left'), 'right' => __('Right')] as $side => $sideLabel)
                            <label class="text-sm text-gray-300">
                                <span class="block mb-1">{{ $sideLabel }}</span>
                                <select name="{{ $side }}" onchange="this.form.submit()"
                                    class="rounded border-gray-600 bg-gray-800 text-gray-100 text-sm">
                                    @foreach ($classes as $key => $drawings)
                                        <option value="{{ $key }}" @selected($$side === $key)>
                                            {{ \App\Services\Observations\DrawingComparison::label($key, $inches) }} ({{ $drawings->count() }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                        <noscript>
                            <button type="submit" class="rounded bg-gray-700 px-3 py-2 text-sm text-white">{{ __('Compare') }}</button>
                        </noscript>
                    </form>
                @else
                    <p class="mb-6 text-sm text-gray-400">{{ __('All drawings of this object were made with a similar aperture.') }}</p>
                @endif

                <div @class(['grid gap-6', 'grid-cols-2' => count($sides) > 1])>
                    @foreach ($sides as $key)
                        <section class="min-w-0">
                            <h2 class="mb-3 text-lg font-semibold text-white">
                                {{ \App\Services\Observations\DrawingComparison::label($key, $inches) }}
                                <span class="text-sm font-normal text-gray-400">
                                    &middot; {{ trans_choice(':count drawing|:count drawings', $classes[$key]->count(), ['count' => $classes[$key]->count()]) }}
                                </span>
                            </h2>
                            <div @class([
                                'grid gap-4',
                                'grid-cols-1 lg:grid-cols-2' => count($sides) > 1,
                                'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4' => count($sides) === 1,
                            ])>
                                @foreach ($classes[$key]->take($perClass) as $drawing)
                                    <x-drawing-compare-card :observation="$drawing" :inches="$inches" />
                                @endforeach
                            </div>
                            @if ($classes[$key]->count() > $perClass)
                                <p class="mt-3 text-sm text-gray-400">
                                    {{ __('The newest :count are shown.', ['count' => $perClass]) }}
                                    <a href="{{ route('observations.drawings.show', ['slug' => $object->slug]) }}" class="text-indigo-400 hover:text-indigo-300">{{ __('All drawings') }}</a>
                                </p>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
