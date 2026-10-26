<tbody id="dsl-aladin-preview-info" x-data @dsl-aladin-updated.window="$wire.call('recalculate', $event.detail)"
    x-init="(function() {
        try {
            var oid = (window.__dsl_server_selected && window.__dsl_server_selected.objectId) || (document.getElementById('aladin-lite-container') && document.getElementById('aladin-lite-container').getAttribute ? document.getElementById('aladin-lite-container').getAttribute('data-object-id') : null) || null;
            if (typeof oid === 'string') oid = oid.trim();
            if (oid && String(oid) !== '' && String(oid).toLowerCase() !== 'null' && String(oid).toLowerCase() !== 'undefined') {
                try { $wire.call('setObjectId', oid); } catch (e) {}
                var payload = { objectId: oid, instrument: (document.getElementById('aladin-instrument-hidden') || {}).value || null, eyepiece: (document.getElementById('aladin-eyepiece-hidden') || {}).value || null, lens: (document.getElementById('aladin-lens-hidden') || {}).value || null };
                try { $wire.call('recalculate', payload); } catch (e) {}
            }
            // Publish the Livewire wire:id as a global so other scripts can call the exact instance
            try {
                setTimeout(function() {
                    try {
                        var root = document.getElementById('dsl-aladin-preview-info');
                        if (root) {
                            var wid = root.getAttribute('wire:id') || root.getAttribute('data-wired-id') || null;
                            if (!wid) {
                                var possible = root.querySelector('[wire\\:id]');
                                if (possible) wid = possible.getAttribute('wire:id');
                            }
                            if (wid) window.__dsl_preview_wireId = wid;
                        }
                    } catch (e) {}
                }, 60);
            } catch (e) {}
        } catch (e) {}
    })">
    {{-- Debug script removed: preview auto-recalc now works and logging is cleaned. --}}
    {{-- live recalculation indicator removed to avoid showing "Recalculating..." text --}}
    {{-- Force recalculation button and debug info removed now that auto-recalc works --}}
    @if ($last_error)
        <tr>
            <td colspan="2" class="text-xs text-red-400 mb-2">Last error: {{ $last_error }}</td>
        </tr>
    @endif
    @php
        $objTypeNormalized = strtolower(trim((string) ($object_type ?? '')));
        $isComet = in_array($objTypeNormalized, ['comet', 'cometobjects'], true);
        $isMoon = $objTypeNormalized === 'moon';
    @endphp

    @unless ($isComet || $isMoon)
        <tr>
            <td class="pr-4 font-medium">
                <div class="inline-flex items-center space-x-2">
                    <span>{{ __('Contrast reserve') }}</span>

                    {{-- Info icon with popover describing contrast reserve classes --}}
                    <div x-data="{ openCRInfo: false }" class="relative inline-block">
                        <button @click.prevent="openCRInfo = !openCRInfo" @keydown.escape="openCRInfo = false"
                            :aria-expanded="openCRInfo.toString()" aria-haspopup="true" type="button"
                            class="ml-1 text-gray-400 hover:text-gray-200 focus:outline-none"
                            aria-label="Contrast reserve info">
                            {{-- simple info SVG --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-9-4a1 1 0 112 0 1 1 0 01-2 0zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="openCRInfo" x-cloak @click.outside="openCRInfo = false" x-transition
                            class="absolute z-10 left-0 mt-2 w-72 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg"
                            data-dsl-no-overlay-hide="true">
                            <div class="font-medium mb-2">{{ __('Contrast reserve classes') }}</div>
                            <ul class="text-xs space-y-2">
                                @php
                                    // helper for list rows: label and value
                                    $rows = [
                                        ['color' => 'bg-green-400', 'label' => __('Very easy'), 'value' => '> 1.0'],
                                        ['color' => 'bg-green-700', 'label' => __('Easy'), 'value' => '> 0.5'],
                                        [
                                            'color' => 'bg-orange-400',
                                            'label' => __('Quite difficult'),
                                            'value' => '> 0.35',
                                        ],
                                        ['color' => 'bg-orange-700', 'label' => __('Difficult'), 'value' => '> 0.1'],
                                        ['color' => 'bg-gray-300', 'label' => __('Questionable'), 'value' => '> -0.2'],
                                        ['color' => 'bg-gray-600', 'label' => __('Not visible'), 'value' => '< -0.2'],
                                    ];
                                @endphp

                                @foreach ($rows as $r)
                                    <li class="flex items-start">
                                        <span
                                            class="inline-block w-2 h-2 rounded-full {{ $r['color'] }} mr-2 mt-1"></span>
                                        <div class="flex-1 flex justify-between items-center">
                                            <span class="pr-2"> <strong>{{ $r['label'] }}:</strong> </span>
                                            <span class="font-mono text-right w-16">{{ $r['value'] }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                @if ($contrast_reserve === null && $contrast_reserve_category === 'not_visible')
                    <span class="text-gray-600" title="{{ __('The object never rises above the horizon at this location.') }}">{{ __('contrast.reserve.category.not_visible') }}</span>
                @elseif ($contrast_reserve === null)
                    {{ __('Unknown') }}
                @else
                    @php
                        $crCat = $contrast_reserve_category ?? null;
                        $crClass = 'text-white';
                        if ($crCat === 'very_easy') {
                            $crClass = 'text-green-400';
                        } elseif ($crCat === 'easy') {
                            $crClass = 'text-green-700';
                        } elseif ($crCat === 'quite_difficult') {
                            $crClass = 'text-orange-400';
                        } elseif ($crCat === 'difficult') {
                            $crClass = 'text-orange-700';
                        } elseif ($crCat === 'questionable') {
                            $crClass = 'text-gray-300';
                        } elseif ($crCat === 'not_visible') {
                            $crClass = 'text-gray-600';
                        }
                        $categoryText = $crCat ? __('contrast.reserve.category.' . $crCat) : __('Unknown');
                    @endphp

                    <div x-data="{ openCR: false }" class="inline-block relative">
                        <button @click.prevent="openCR = !openCR" @keydown.escape="openCR = false"
                            :aria-expanded="openCR.toString()" aria-haspopup="true" type="button"
                            class="focus:outline-none {{ $crClass }} font-medium">
                            {{ $contrast_reserve }}
                        </button>

                        {{-- Mark this popup so the Aladin overlay-hiding heuristics ignore it (data-dsl-no-overlay-hide) --}}
                        <div x-show="openCR" x-cloak @click.outside="openCR = false" x-transition
                            class="absolute z-10 left-0 mt-2 w-80 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg"
                            data-dsl-no-overlay-hide="true">
                            <div class="text-sm mb-2">
                                {{ __('contrast.reserve.summary', ['value' => $contrast_reserve, 'category' => $categoryText]) }}
                            </div>
                            <div class="text-xs text-gray-300 mb-1"><strong>{{ __('Location') }}:</strong>
                                {{ $contrast_used_location ?? __('Unknown') }}@if (!empty($contrast_used_sqm) || !empty($contrast_used_nelm))
                                    <span class="text-gray-400">(@if (!empty($contrast_used_sqm))
                                            SQM: {{ $contrast_used_sqm }}
                                            @endif@if (!empty($contrast_used_sqm) && !empty($contrast_used_nelm))
                                                /
                                                @endif@if (!empty($contrast_used_nelm))
                                                    NELM: {{ $contrast_used_nelm }}
                                                @endif)</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-300"><strong>{{ __('Instrument') }}:</strong>
                                {{ $contrast_used_instrument ?? __('Unknown') }}</div>
                            @if ($contrast_used_altitude !== null && $contrast_used_extinction !== null)
                                <div class="text-xs text-gray-300 mt-1">
                                    {{ __('Highest altitude :altitude°, dimmed :extinction mag by the atmosphere', ['altitude' => $contrast_used_altitude, 'extinction' => number_format($contrast_used_extinction, 2)]) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    @endunless
    @if ($moonlit !== null)
        {{-- The contrast reserve above is for a dark sky; this one for the sky of the night of the date (MoonlitContrastReserve) --}}
        <tr>
            <td class="pr-4 font-medium">{{ __('Contrast reserve with the Moon') }}</td>
            <td>
                @if ($moonlit['contrast_reserve'] === null)
                    <span class="text-gray-400">{{ __('The object does not get high enough in the dark hours of this night.') }}</span>
                @else
                    @php
                        $moonClass = match ($moonlit['category']) {
                            'very_easy' => 'text-green-400',
                            'easy' => 'text-green-700',
                            'quite_difficult' => 'text-orange-400',
                            'difficult' => 'text-orange-700',
                            'questionable' => 'text-gray-300',
                            'not_visible' => 'text-gray-600',
                            default => 'text-white',
                        };
                    @endphp
                    <span class="{{ $moonClass }} font-medium" title="{{ __('contrast.reserve.category.' . $moonlit['category']) }}">{{ $moonlit['contrast_reserve'] }}</span>
                    <span class="text-xs text-gray-400">
                        @if ($moonlit['moon_up'])
                            {{ __('at :time (altitude :altitude°), the sky lit by the Moon (:fraction% illuminated) to :sqm mag/arcsec²', ['time' => $moonlit['time'], 'altitude' => $moonlit['altitude'], 'fraction' => (int) round($moonlit['moon_fraction'] * 100), 'sqm' => number_format($moonlit['sqm'], 2)]) }}
                        @else
                            {{ __('at :time (altitude :altitude°), with the Moon below the horizon', ['time' => $moonlit['time'], 'altitude' => $moonlit['altitude']]) }}
                        @endif
                    </span>
                @endif
            </td>
        </tr>
    @endif
    @if (!empty($filter_advice))
        {{-- The contrast reserve with each of the observer's nebula filters (FilterAdvice); the
             kind of filter and the magnifications are in a popover on hover (tap on a touch screen) --}}
        <tr>
            <td class="pr-4 font-medium align-top">
                <div class="inline-flex items-center space-x-2">
                    <span>{{ __('Contrast reserve with filters') }}</span>
                    <div x-data="{ open: false }" class="relative inline-block" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click.prevent="open = !open" @keydown.escape="open = false" :aria-expanded="open.toString()"
                            aria-haspopup="true" type="button" class="ml-1 text-gray-400 hover:text-gray-200 focus:outline-none"
                            aria-label="{{ __('Contrast reserve with filters') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-9-4a1 1 0 112 0 1 1 0 01-2 0zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition
                            class="absolute z-10 left-0 mt-2 w-80 p-3 bg-gray-800 text-xs font-normal text-gray-100 rounded shadow-lg"
                            data-dsl-no-overlay-hide="true">
                            <p>{{ __('The difference with the contrast reserve without a filter is between brackets. Every filter of a kind gets the typical passband of that kind, and the nebula the typical spectrum of its type.') }}</p>
                            <p class="mt-2">{{ __('A filter darkens the image, so it often works best at a lower magnification (a larger exit pupil) than without one, up to the :pupil mm of the dark-adapted eye.', ['pupil' => (int) \App\Services\ContrastReserveCalculator::EYE_PUPIL_MM]) }}</p>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <ul>
                    @foreach ($filter_advice as $advice)
                        @php
                            $filterClass = match ($advice['category']) {
                                'very_easy' => 'text-green-400',
                                'easy' => 'text-green-700',
                                'quite_difficult' => 'text-orange-400',
                                'difficult' => 'text-orange-700',
                                'questionable' => 'text-gray-300',
                                'not_visible' => 'text-gray-600',
                                default => 'text-white',
                            };
                        @endphp
                        <li x-data="{ open: false }" class="relative" style="width: fit-content; cursor: help" @mouseenter="open = true" @mouseleave="open = false"
                            @click="if (! $event.target.closest('a')) open = !open" @click.outside="open = false">
                            <span class="{{ $filterClass }} font-medium">{{ number_format($advice['contrast_reserve'], 2) }}</span>
                            <span class="{{ $advice['gain'] > 0 ? 'text-green-400' : 'text-gray-400' }} text-xs">({{ $advice['gain'] > 0 ? '+' : '' }}{{ number_format($advice['gain'], 2) }})</span>
                            @if ($advice['url'])
                                <a href="{{ $advice['url'] }}" class="text-gray-300 hover:underline">{{ $advice['name'] }}</a>
                            @else
                                {{ $advice['name'] }}
                            @endif
                            <div x-show="open" x-cloak x-transition
                                style="top: 100%" class="absolute z-10 left-0 mt-1 w-80 p-3 bg-gray-800 text-xs text-gray-100 rounded shadow-lg"
                                data-dsl-no-overlay-hide="true">
                                <div class="font-medium">{{ $advice['kind'] }}@if ($advice['category']) · {{ __('contrast.reserve.category.' . $advice['category']) }}@endif</div>
                                <div class="mt-1">{{ __('Best at :magnification (exit pupil :pupil mm)', ['magnification' => $advice['magnification'] . 'x' . (!empty($advice['eyepieces']) ? ', ' . implode(', ', $advice['eyepieces']) : ''), 'pupil' => number_format((float) $advice['exit_pupil'], 1)]) }}</div>
                                @if ($advice['ideal_exit_pupil'] !== null && abs($advice['ideal_exit_pupil'] - $advice['exit_pupil']) >= 0.5)
                                    <div>{{ ucfirst(__('ideal: exit pupil :pupil mm (:magnification)', ['pupil' => number_format($advice['ideal_exit_pupil'], 1), 'magnification' => $advice['ideal_magnification'] . 'x'])) }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
    @endif
    {{-- The RA/Dec, Magnitude and Diameter rows were removed from this preview
         area because those values are shown at the top of the page. --}}
    @unless ($isComet || $isMoon)
        <tr>
            <td class="pr-4 font-medium">{{ __('Optimum detection magnification') }}</td>
            <td>
                @if (!empty($optimum_detection_magnification))
                    @php
                        $eps = [];
                        foreach ($optimum_eyepieces as $ep) {
                            $name = $ep['name'] ?? ($ep['label'] ?? null);
                            $slug = $ep['slug'] ?? null;
                            $userSlug = $ep['user_slug'] ?? null;
                            $parts = [];
                            if (!empty($name)) {
                                if (!empty($slug) && !empty($userSlug)) {
                                    $url = route('eyepiece.show', ['user' => $userSlug, 'eyepiece' => $slug]);
                                    $parts[] =
                                        '<a href="' .
                                        e($url) .
                                        '" class="text-gray-300 hover:underline">' .
                                        e($name) .
                                        '</a>';
                                } else {
                                    $parts[] = e($name);
                                }
                            }
                            if (!empty($parts)) {
                                $eps[] = implode(' — ', $parts);
                            }
                        }
                    @endphp
                    <div>
                        {{ $optimum_detection_magnification }}x
                        @if (!empty($eps))
                            - {!! implode(', ', $eps) !!}
                        @endif
                    </div>
                @else
                    {{ __('Unknown') }}
                @endif
            </td>
        </tr>
    @endunless
</tbody>
