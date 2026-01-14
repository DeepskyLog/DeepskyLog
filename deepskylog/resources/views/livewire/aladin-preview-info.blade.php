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
                @if ($contrast_reserve === null)
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
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    @endunless
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
