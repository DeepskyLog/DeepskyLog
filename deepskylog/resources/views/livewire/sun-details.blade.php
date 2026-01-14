<tbody wire:key="sun-details-{{ $objectId ?? 'none' }}">
    <tr id="dsl-sun-ephem-row">
        <td class="pr-4 font-medium">{{ __('Sun (Rise / Set / Transit)') }}</td>
        <td id="dsl-sun-ephem-cell">
            <span id="dsl-sun-rise" class="font-mono">{{ $sunrise ?? '—' }}</span>
            <span class="text-gray-400 px-2">/</span>
            <span id="dsl-sun-set" class="font-mono">{{ $sunset ?? '—' }}</span>
            <span class="text-gray-400 px-2">/</span>
            <span id="dsl-sun-transit" class="font-mono">{{ $transit ?? '—' }}</span>
        </td>
    </tr>

    <tr>
        <td class="pr-4 font-medium">{{ __('Nautical twil.') }}</td>
        <td id="dsl-sun-nautical">{{ $nautical_begin ?? '—' }} / {{ $nautical_end ?? '—' }}</td>
    </tr>

    <tr>
        <td class="pr-4 font-medium">{{ __('Astronomical twil.') }}</td>
        <td id="dsl-sun-astronomical">{{ $astronomical_begin ?? '—' }} / {{ $astronomical_end ?? '—' }}</td>
    </tr>
</tbody>
