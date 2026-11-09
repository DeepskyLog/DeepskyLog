{{-- Links from the Moon, a lunar feature and the planets to their pages for tonight --}}
@if (($session->source_type_raw ?? '') === 'lunar_feature' || (($session->source_type_raw ?? '') === 'moon' && strcasecmp((string) ($session->name ?? ''), 'Moon') === 0))
    {{-- The Moon (not the moons of the planets) and its features: the terminator tonight --}}
    <li>
        <a href="{{ route('moon.show') }}" class="text-gray-300 hover:underline">{{ __('The Moon and its terminator') }}</a>
    </li>
@endif
@if (($session->source_type_raw ?? '') === 'planet')
    {{-- The disks of Mars and Venus, the moons of Jupiter and Saturn --}}
    @if (in_array(strtolower((string) ($session->name ?? '')), ['mars', 'venus'], true))
        <li>
            <a href="{{ route('mars-venus.show') }}" class="text-gray-300 hover:underline">{{ __('Mars and Venus tonight') }}</a>
        </li>
    @elseif (in_array(strtolower((string) ($session->name ?? '')), ['jupiter', 'saturn'], true))
        <li>
            <a href="{{ route('planet-moons.show') }}" class="text-gray-300 hover:underline">{{ __('Jupiter and Saturn tonight') }}</a>
        </li>
    @endif
@endif
