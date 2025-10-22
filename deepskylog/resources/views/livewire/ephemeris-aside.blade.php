<div class="hidden md:block w-64 mr-6">
    <div class="bg-gray-900 p-4 rounded shadow text-gray-100 sticky top-16">
        <h3 class="text-lg font-semibold mb-2 text-white">{{ __('Ephemerides') }}</h3>

        {{-- Use the WireUI datetime picker to match other forms (sketch/session) and bind to Livewire property --}}
        <div class="mb-2">
            <x-datetime-picker
                without-time="true"
                without-timezone="true"
                name="ephem_date"
                label="{{ __('Date') }}"
                wire:model.live="date"
                value="{{ $date }}"
                class="w-full"
            />
        </div>

        <h4 class="text-sm font-semibold mt-2 text-white">{{ __('Moon / Sun') }}</h4>
        <div class="text-xs text-gray-400 mt-1">{{ __('on') }} <strong class="text-gray-200">{{ $date }}</strong></div>

        @php
            // Split the sun_times string ("sunrise / sunset / transit") into parts
            $sunParts = is_string($sun_times) ? array_pad(explode(' / ', $sun_times), 3, '-') : ['-','-','-'];
            [$sunrise, $sunset, $suntransit] = $sunParts;

            // Nautical and astronomical are already "end / begin" strings from Location helpers; display as given
            $nauticalParts = is_string($nautical) ? array_pad(explode(' / ', $nautical), 2, '-') : ['-','-'];
            [$nautEnd, $nautBegin] = $nauticalParts;

            $astroParts = is_string($astronomical) ? array_pad(explode(' / ', $astronomical), 2, '-') : ['-','-'];
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
        </table>

        <div class="mt-3 flex items-center">
            {{-- Simple inline SVG moon phase visualization --}}
            <svg class="w-20 h-20" viewBox="0 0 60 60" aria-hidden="true" role="img">
                <defs>
                    <clipPath id="moon-clip">
                        <circle cx="30" cy="30" r="24" />
                    </clipPath>
                </defs>
                <!-- dark disk (background) -->
                <circle cx="30" cy="30" r="24" fill="#0b1220" />
                <!-- illuminated disk offset horizontally by $offset -->
                <g clip-path="url(#moon-clip)">
                    <circle cx="{{ 30 + $offset }}" cy="30" r="24" fill="#f6f5f3" />
                </g>
                <!-- optional outline -->
                <circle cx="30" cy="30" r="24" fill="none" stroke="#24303a" stroke-width="1" />
            </svg>

            <div class="ml-3">
                <div class="text-sm text-gray-200">@if(!is_null($illumPct)) {{ $illumPct }}% {{ __('illum.') }} @else - @endif</div>
                <div class="text-xs text-gray-400 mt-1">{{ __('New moon:') }} {{ $next_new_moon ?? '--' }}</div>
            </div>
        </div>
    </div>
</div>
