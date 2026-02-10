<div data-dsl-ephemeris-aside-root>
    @if (empty($suppress))
        <div class="w-full lg:w-64 lg:flex-none lg:min-w-[16rem] self-stretch" data-dsl-ephemeris-aside>
            <div class="bg-gray-900 p-4 rounded shadow text-gray-100 flex flex-col flex-1 h-full">
            <h3 class="text-lg font-semibold mb-2 text-white">{{ __('Ephemerides') }}</h3>

            {{-- Use the WireUI datetime picker to match other forms (sketch/session) and bind to Livewire property --}}
            @auth
                <div class="mb-2">
                    <x-datetime-picker without-time="true" without-timezone="true" name="ephem_date" label="{{ __('Date') }}"
                        wire:model.live="date" value="{{ $date }}" class="w-full" />
                </div>
            @endauth

            <h4 class="text-sm font-semibold mt-2 text-white">{{ __('Moon / Sun') }}</h4>
            <div class="text-xs text-gray-400 mt-1">{{ __('on') }} <strong
                    class="text-gray-200">{{ $date }}</strong></div>

            @php
                // Split the sun_times string ("sunrise / sunset / transit") into parts
                $sunParts = is_string($sun_times) ? array_pad(explode(' / ', $sun_times), 3, '-') : ['-', '-', '-'];
                [$sunrise, $sunset, $suntransit] = $sunParts;

                // Nautical and astronomical are already "end / begin" strings from Location helpers; display as given
                $nauticalParts = is_string($nautical) ? array_pad(explode(' / ', $nautical), 2, '-') : ['-', '-'];
                [$nautEnd, $nautBegin] = $nauticalParts;

                $astroParts = is_string($astronomical) ? array_pad(explode(' / ', $astronomical), 2, '-') : ['-', '-'];
                [$astroEnd, $astroBegin] = $astroParts;

                // Moon rise/set
                $moonRise = $moon_rise ?? '-';
                $moonSet = $moon_set ?? '-';

                // Moon phase visualization values
                $phase = is_null($moon_phase_ratio) ? 0.0 : (float) $moon_phase_ratio; // 0..1
                // offset for the illuminating circle (-20..20) where 0 -> full, negative -> waning, positive -> waxing
                $offset = (1 - 2 * $phase) * 20;
                $illumPct = is_null($moon_illuminated) ? null : round($moon_illuminated * 100);
            @endphp

            <table class="w-full text-sm text-gray-300 mt-2">
                @auth
                    <tr>
                        <td class="py-1">{{ __('Moon') }}</td>
                        <td class="text-right py-1">{{ $moonRise }}</td>
                        <td class="text-right py-1">{{ $moonSet }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">{{ __('Sun') }}</td>
                        <td class="text-right py-1">{{ $sunrise }}</td>
                        <td class="text-right py-1">{{ $sunset }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">{{ __('Naut.') }}</td>
                        <td class="text-right py-1">{{ $nautBegin }}</td>
                        <td class="text-right py-1">{{ $nautEnd }}</td>
                    </tr>
                    <tr>
                        <td class="py-1">{{ __('Astro.') }}</td>
                        <td class="text-right py-1">{{ $astroBegin }}</td>
                        <td class="text-right py-1">{{ $astroEnd }}</td>
                    </tr>
                @endauth
            </table>

            <div class="mt-3 flex items-center">
                {{-- Use pre-rendered moon phase frames (public/images/moon/m0.gif .. m40.gif).
                 Map the phase ratio (0..1) to frame index 0..40 evenly. If the phase
                 is null, fall back to a placeholder SVG. --}}
                @php
                    // Ensure phase is in [0,1]
                    $phaseNorm = is_null($phase) ? 0.0 : max(0.0, min(1.0, (float) $phase));

                    // Prefer mapping by illuminated fraction (0..1) to better match the displayed
                    // illumination percentage. The pre-rendered frames are indexed 0..40 where
                    // 0 = new, 20 = full, 40 = new. We'll compute an index on the half-cycle
                    // (0..20) from the illuminated fraction then mirror it when the moon is waning
                    // (phase > 0.5) so the lit side is on the correct side.
                    $illum = is_null($moon_illuminated) ? 0.0 : max(0.0, min(1.0, (float) $moon_illuminated));
                    // Map illumination to 0..20 (new->full)
                    $idxHalf = (int) round($illum * 20);
                    $idxHalf = max(0, min(20, $idxHalf));

                    if ($phaseNorm <= 0.5) {
                        // Waxing/up to full: use the half-index directly (0..20)
                        $frameIndex = $idxHalf;
                    } else {
                        // Waning: mirror across the full frame (20) so the lit side is correct
                        $frameIndex = 40 - $idxHalf;
                    }

                    $moonImg = asset("images/moon/m{$frameIndex}.gif");
                @endphp

                @if (file_exists(public_path("images/moon/m{$frameIndex}.gif")))
                    <img src="{{ $moonImg }}" alt="{{ __('Moon phase') }}" class="w-20 h-20 object-contain"
                        loading="lazy" data-moon-frame="{{ $frameIndex }}" />
                @else
                    {{-- Fallback: simple circle SVG if frames are missing --}}
                    <svg class="w-20 h-20" viewBox="0 0 60 60" aria-hidden="true" role="img">
                        <circle cx="30" cy="30" r="24" fill="#0b1220" />
                        <g>
                            <circle cx="30" cy="30" r="24" fill="#f6f5f3" opacity="0.8" />
                        </g>
                        <circle cx="30" cy="30" r="24" fill="none" stroke="#24303a" stroke-width="1" />
                    </svg>
                @endif

                <div class="ml-3">
                    <div class="text-sm text-gray-200">
                        @if (!is_null($illumPct))
                            {{ $illumPct }}% {{ __('illum.') }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ __('New moon:') }} {{ $next_new_moon ?? '--' }}</div>
                </div>

            </div>

            {{-- Embed a small hidden payload so the main page's JS can pick up moon-specific values
             (rise/set, illuminated fraction, next new moon) even when the object-ephemerides
             preview does not include them. This mirrors the object-ephemerides hidden payload
             format so the MutationObserver in the main view can decode and apply values. --}}
            @php
                // For the inline aside payload we intentionally avoid exposing the
                // generic `illuminated_fraction` key which the global page
                // MutationObserver recognizes. Instead provide a moon-specific key
                // `moon_illumination` so only moon-specific handlers will read it.
                $asidePayload = [
                    'rising' => $moon_rise ?? null,
                    'transit' => null,
                    'setting' => $moon_set ?? null,
                    'moon_illumination' => $moon_illuminated ?? null,
                    'next_new_moon' => $next_new_moon ?? null,
                    'date' => $date ?? null,
                    '_ts' => \Carbon\Carbon::now()->toIso8601String(),
                ];
                $asideEncoded = base64_encode(json_encode($asidePayload));
            @endphp
            <div style="display:none">
                <div id="dsl-ephem-aside-payload" data-dsl-ephem-payload="{{ $asideEncoded }}" style="display:none">
                </div>
            </div>
            </div>
        </div>
    @else
        <div style="display:none" data-dsl-ephemeris-aside-hidden></div>
    @endif
</div>
