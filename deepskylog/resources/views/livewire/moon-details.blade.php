<tbody wire:key="moon-details-{{ $objectId ?? 'none' }}">
    <tr id="dsl-moon-ephem-row">
        <td class="pr-4 font-medium">{{ __('Moon (Rise / Set)') }}</td>
        <td id="dsl-moon-ephem-cell">
            <span id="dsl-moon-rise" class="font-mono">{{ $rising ?? '—' }}</span>
            <span class="text-gray-400 px-2">/</span>
            <span id="dsl-moon-set" class="font-mono">{{ $setting ?? '—' }}</span>
        </td>
    </tr>

    <tr>
        <td class="pr-4 font-medium">{{ __('Illumination') }}</td>
        <td id="dsl-main-illum">
            @if (!is_null($illuminated_fraction) && is_numeric($illuminated_fraction))
                {{ floatval($illuminated_fraction) * 100.0 }}%
            @else
                —
            @endif
        </td>
    </tr>

    <tr>
        <td class="pr-4 font-medium">{{ __('Next new moon') }}</td>
        <td id="dsl-next-new-moon">{{ $next_new_moon ?? '—' }}</td>
    </tr>
</tbody>
