<div class="bg-gray-800 p-3 rounded shadow text-gray-100">
    <h4 class="font-semibold mb-2 text-white">{{ __('Ephemerides') }}</h4>
    <div class="text-sm space-y-2">
        <div>
            <label class="text-gray-400 text-xs">{{ __('Date') }}</label>
            <div class="mt-1">
                <input id="ephem-date-input" type="date" class="bg-gray-900 text-white px-2 py-1 rounded text-sm w-full" value="{{ isset($ephemerides['date']) ? $ephemerides['date'] : \Carbon\Carbon::now()->toDateString() }}" />
            </div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Moon') }}</div>
            <div class="mt-1">
                <div class="flex items-center gap-2">
                    <div class="w-12 h-12 bg-black rounded flex items-center justify-center text-sm text-gray-300" id="ephem-moon-image">
                        {{-- Placeholder: client can replace with dynamic moon image based on moon_phase_ratio --}}
                        @if(isset($ephemerides['moon_phase_ratio']))
                            @php
                                $ratio = $ephemerides['moon_phase_ratio'];
                                // Map ratio to basic emoji for fallback (0=new, 0.5=full)
                                if ($ratio === null) { $emoji = '—'; }
                                elseif ($ratio < 0.125) { $emoji = '🌑'; }
                                elseif ($ratio < 0.375) { $emoji = '🌓'; }
                                elseif ($ratio < 0.625) { $emoji = '🌕'; }
                                elseif ($ratio < 0.875) { $emoji = '🌗'; }
                                else { $emoji = '🌑'; }
                            @endphp
                            <div class="text-2xl">{!! $emoji !!}</div>
                        @else
                            —
                        @endif
                    </div>
                    <div class="text-sm">
                        <div>{{ __('Illumination') }}: <span class="font-mono">{{ isset($ephemerides['moon_illuminated']) ? ($ephemerides['moon_illuminated'] * 100) . '%' : '—' }}</span></div>
                        <div>{{ __('Next new moon') }}: <span class="font-mono">{{ $ephemerides['next_new_moon'] ?? '—' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Moon rise / set') }}</div>
            <div class="mt-1 font-mono">{{ $ephemerides['rising'] ?? '—' }} / {{ $ephemerides['setting'] ?? '—' }}</div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Sun rise / set') }}</div>
            <div class="mt-1 font-mono">{{ $ephemerides['sunrise'] ?? '—' }} / {{ $ephemerides['sunset'] ?? '—' }}</div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Nautical twilight') }}</div>
            <div class="mt-1 font-mono">{{ $ephemerides['nautical_twilight_begin'] ?? '—' }} / {{ $ephemerides['nautical_twilight_end'] ?? '—' }}</div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Astronomical twilight') }}</div>
            <div class="mt-1 font-mono">{{ $ephemerides['astronomical_twilight_begin'] ?? '—' }} / {{ $ephemerides['astronomical_twilight_end'] ?? '—' }}</div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Best time') }}</div>
            <div class="mt-1 font-mono">{{ $ephemerides['best_time'] ?? '—' }}</div>
        </div>

        <div>
            <div class="text-gray-400 text-xs">{{ __('Maximum altitude') }}</div>
            <div class="mt-1 font-mono">@if(isset($ephemerides['max_height_at_night']) && $ephemerides['max_height_at_night'] !== null) {{ $ephemerides['max_height_at_night'] }}° @elseif(isset($ephemerides['max_height']) && $ephemerides['max_height'] !== null) {{ $ephemerides['max_height'] }}° @else — @endif</div>
        </div>

    </div>
</div>

<script>
    (function(){
        try {
            var inp = document.getElementById('ephem-date-input');
            if (!inp) return;
            inp.addEventListener('change', function(){
                try {
                    var d = inp.value;
                    if (!d) return;
                    var url = new URL(window.location.href);
                    url.searchParams.set('ephem_date', d);
                    // Preserve hash
                    window.location.href = url.toString();
                } catch(e){}
            }, { passive: true });
        } catch(e){}
    })();
</script>
