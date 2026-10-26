<div class="text-sm text-gray-200">
    <style>
        .dsl-field-view svg { display: block; width: 100%; height: auto; }
    </style>

    {{-- Equipment, location and moment --}}
    @php
        $choices = $result['choices'];
        $selectClass = 'mt-1 block w-full rounded-md border-gray-600 bg-gray-800 text-sm text-gray-100 focus:border-indigo-500 focus:ring-indigo-500';
    @endphp
    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <label class="block">
            <span class="text-gray-400">{{ __('Instrument set') }}</span>
            <select wire:model.live="instrumentSet" class="{{ $selectClass }}">
                <option value="all">{{ __('All equipment') }}</option>
                @foreach ($choices['sets'] as $item)
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $instrumentSet)>{{ html_entity_decode($item->name) }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Telescope') }}</span>
            <select wire:model.live="instrument" class="{{ $selectClass }}">
                @forelse ($choices['instruments'] as $item)
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $instrument)>{{ html_entity_decode($item->fullName()) }}</option>
                @empty
                    <option value="">{{ __('No instruments') }}</option>
                @endforelse
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Eyepiece') }}</span>
            <select wire:model.live="eyepiece" class="{{ $selectClass }}" @disabled(! $result['instrument'] || (float) $result['instrument']->fixedMagnification > 0)>
                @if (! $result['eyepiece'])
                    <option value="">{{ __('No eyepiece') }}</option>
                @endif
                @foreach ($choices['eyepieces'] as $item)
                    @php $mag = $result['instrument']?->magnificationFor($item, $result['lens']); @endphp
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $eyepiece)>{{ html_entity_decode($item->fullName()) }}{{ $mag ? ' (' . $mag . 'x)' : '' }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Lens') }}</span>
            <select wire:model.live="lens" class="{{ $selectClass }}">
                <option value="none" @selected($lens === 'none')>{{ __('No lens') }}</option>
                @foreach ($choices['lenses'] as $item)
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $lens)>{{ html_entity_decode($item->name) }}</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Finderscope') }}</span>
            <select wire:model.live="finder" class="{{ $selectClass }}">
                <option value="none">{{ __('No finderscope') }}</option>
                @foreach ($choices['finders'] as $item)
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $finder)>{{ html_entity_decode($item->fullName()) }} ({{ (int) $item->fixedMagnification }}x)</option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Location') }}</span>
            <select wire:model.live="location" class="{{ $selectClass }}">
                @forelse ($choices['locations'] as $item)
                    <option value="{{ $item->id }}" @selected((string) $item->id === (string) $location)>{{ html_entity_decode($item->name) }}</option>
                @empty
                    <option value="">{{ __('No locations') }}</option>
                @endforelse
            </select>
        </label>

        <label class="block">
            <span class="text-gray-400">{{ __('Night of') }}</span>
            <input type="date" value="{{ $result['night'] }}" wire:key="field-views-night-{{ $result['night'] }}"
                x-on:change="$event.target.value && $wire.highest($event.target.value)"
                class="{{ $selectClass }}" />
        </label>

        <div class="block">
            <label for="field-views-datetime" class="text-gray-400">{{ __('Date and time') }} ({{ $result['timezone'] }})</label>
            <div class="mt-1 flex gap-2">
                <input id="field-views-datetime" type="datetime-local" value="{{ $datetimeValue }}" wire:key="field-views-datetime-{{ $datetimeValue }}"
                    x-on:change="$event.target.value && $wire.set('datetime', $event.target.value)" style="min-width: 0"
                    class="block w-full rounded-md border-gray-600 bg-gray-800 text-sm text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" />
                <button type="button" wire:click="now" style="flex-shrink: 0" class="rounded-md bg-gray-700 px-3 text-xs font-medium text-white hover:bg-gray-600">{{ __('Now') }}</button>
                <button type="button" wire:click="highest('{{ $result['night'] }}')" style="flex-shrink: 0" class="rounded-md bg-gray-700 px-3 text-xs font-medium text-white hover:bg-gray-600" title="{{ __('When the object is highest during the night') }}">{{ __('Highest') }}</button>
            </div>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ $pdfUrl }}" target="_blank" rel="nofollow noopener noreferrer"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700">
            {{ __('Download as PDF') }}
        </a>
        @if ($canCoupleFinder)
            <button type="button" wire:click="coupleFinder" class="rounded-md bg-gray-700 px-4 py-2 font-medium text-white hover:bg-gray-600">
                {{ __('Use :finder as the finderscope of :instrument', ['finder' => html_entity_decode($result['finder']->fullName()), 'instrument' => html_entity_decode($result['instrument']->fullName())]) }}
            </button>
        @endif
        @if (session('field-views-message'))
            <span class="text-green-400">{{ session('field-views-message') }}</span>
        @endif
        <span class="text-gray-400">
            {{ __('Limiting magnitude of the sky: :nelm', ['nelm' => number_format($result['nelm'], 1)]) }}
            @if ($result['location'])
                · {{ $result['orientation'] === 'north' ? __('Equatorial mount: north up') : __('Alt-azimuth mount: zenith up') }}
            @endif
        </span>
    </div>

    <p class="mb-3 text-gray-300">
        @php $shownAt = $result['moment']->format('Y-m-d H:i'); @endphp
        @if ($result['time_mode'] === 'highest')
            {{ __('Shown at :time, when :object is highest during the night of :night (altitude :alt°).', ['time' => $shownAt, 'object' => $result['object']->name, 'night' => $result['night'], 'alt' => round($result['altitude'])]) }}
            @if ($result['darkness'] === 'nautical')
                {{ __('It does not get astronomically dark that night; the end of nautical twilight is used.') }}
            @endif
        @elseif ($result['time_mode'] === 'now')
            {{ __('Shown at the current time, :time.', ['time' => $shownAt]) }}
        @else
            {{ __('Shown at the chosen time, :time.', ['time' => $shownAt]) }}
        @endif
    </p>

    @foreach ($result['warnings'] as $warning)
        <div class="mb-3 rounded-md border border-amber-500/60 bg-amber-900/30 px-4 py-2 text-amber-100" role="note">{{ $warning }}</div>
    @endforeach

    {{-- The three views --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3" wire:loading.class="opacity-50">
        @foreach ($result['views'] as $key => $view)
            <section class="rounded-lg border border-gray-700 bg-gray-800/50 p-3" wire:key="field-view-{{ $key }}">
                <h2 class="text-base font-semibold text-white">{{ $view['title'] }}</h2>
                @if (! empty($view['subtitle']))
                    <p class="text-gray-400">{{ $view['subtitle'] }}</p>
                @endif
                @isset($view['svg'])
                    <p class="text-gray-400">
                        {{ __('Field of view: :fov', ['fov' => $view['fov']]) }} · {{ __('Limiting magnitude: :mag', ['mag' => number_format($view['limit'], 1)]) }}
                    </p>
                    @if (array_key_exists('contrast', $view))
                        @php
                            $crCategory = $view['contrast']['category'] ?? null;
                            $crClass = match ($crCategory) {
                                'very_easy' => 'text-green-400',
                                'easy' => 'text-green-600',
                                'quite_difficult' => 'text-orange-400',
                                'difficult' => 'text-orange-600',
                                'questionable' => 'text-gray-300',
                                'not_visible' => 'text-gray-500',
                                default => 'text-gray-400',
                            };
                        @endphp
                        <p class="{{ $crClass }}">
                            @if ($view['contrast'] === null)
                                {{ __('Contrast reserve: unknown (the object has no magnitude or size)') }}
                            @elseif ($view['contrast']['value'] === null)
                                {{ __('Contrast reserve: :category', ['category' => __('contrast.reserve.category.not_visible')]) }}
                            @else
                                {{ __('contrast.reserve.summary', ['value' => number_format($view['contrast']['value'], 2), 'category' => __('contrast.reserve.category.'.$crCategory)]) }}
                            @endif
                        </p>
                    @endif
                    @if (! empty($view['filters']))
                        <ul class="text-gray-400">
                            @foreach ($view['filters'] as $advice)
                                <li>
                                    @if ($advice['current'])
                                        {{ __('With :filter: :value', ['filter' => $advice['name'], 'value' => number_format($advice['current']['contrast_reserve'], 2)]) }}
                                        <span class="{{ $advice['current']['gain'] > 0 ? 'text-green-400' : 'text-gray-500' }}">({{ $advice['current']['gain'] > 0 ? '+' : '' }}{{ number_format($advice['current']['gain'], 2) }})</span>
                                    @else
                                        {{ $advice['name'] }}
                                    @endif
                                    <span class="text-xs">· {{ __('best at :magnification (exit pupil :pupil mm): :value', ['magnification' => $advice['magnification'] . 'x' . (!empty($advice['eyepieces']) ? ', ' . implode(', ', $advice['eyepieces']) : ''), 'pupil' => number_format((float) $advice['exit_pupil'], 1), 'value' => number_format($advice['contrast_reserve'], 2)]) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="dsl-field-view mt-2">{!! $view['svg'] !!}</div>
                @else
                    <p class="mt-4 text-gray-300">{{ $view['message'] }}</p>
                @endisset
            </section>
        @endforeach
    </div>

    <div class="mt-4 space-y-1 text-xs text-gray-400">
        <p>{{ __('The naked-eye view shows 90° of the sky around the object, with the zenith up and the ground below the horizon. The dashed circles show the field of the finder in the naked-eye view and the field of the telescope in the finder view.') }}</p>
        <p>{{ __('The finder and telescope views are turned as the mount turns them (zenith up for an alt-azimuth mount, north up for an equatorial one) and mirrored as set for the instrument. The arrows in the corner point north (N) and east (E).') }}</p>
        <p>{{ __('The contrast reserve is that of the object at the magnification of the view, under the sky of the location (its SQM, else its limiting magnitude), with the object dimmed by the extinction at its altitude.') }}</p>
        <p>{{ __('For an emission nebula, the telescope view also gives the contrast reserve with each of your nebula filters, with the difference without a filter between brackets. Every filter of a kind (broadband, UHC, O-III, H-beta) gets the typical passband of that kind, and the nebula the typical spectrum of its type.') }}</p>
        <p>{{ __('Each filter also gets the best magnification of the eyepieces of the instrument set with that filter. A filter darkens the image, so it often works best at a lower magnification (a larger exit pupil) than without one, up to the :pupil mm of the dark-adapted eye.', ['pupil' => (int) \App\Services\ContrastReserveCalculator::EYE_PUPIL_MM]) }}</p>
        <p>{{ __('The limiting magnitudes follow from the limiting magnitude or SQM of the location, the aperture and magnification of the instrument, and the extinction at the altitude of the object. Stars of the naked-eye view fade towards the horizon.') }}</p>
    </div>
</div>
