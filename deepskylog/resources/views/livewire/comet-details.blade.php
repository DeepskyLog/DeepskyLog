@php
    $e = $ephemerides ?? null;
@endphp
{{-- The page mounts this component twice: `rows` among the object details, `graphs` at the end of the table.
     RA/Dec, rise/transit/set, best time and maximum altitude come from `object-ephemerides`. --}}
<tbody wire:key="comet-details-{{ $part }}-{{ $objectId ?? 'none' }}">
    @if ($part !== 'graphs')
        @if (!empty($e) && !empty($e['constellation']))
            <tr>
                <td class="pr-4 font-medium">{{ __('Constellation') }}</td>
                <td id="dsl-details-constellation">{{ $e['constellation'] }}</td>
            </tr>
        @endif

        @if (!empty($e) && isset($e['mag']) && is_numeric($e['mag']) && abs($e['mag']) < 99)
            <tr>
                <td class="pr-4 font-medium">{{ __('Predicted magnitude') }}</td>
                <td id="dsl-details-predicted-magnitude">{{ number_format((float) $e['mag'], 1) }}</td>
            </tr>
        @endif
    @endif

    @if ($part !== 'rows')
        <tr>
            <td colspan="2">
                <div class="mt-4">
                    @if (!empty($e) && is_array($e) && !empty($e['altitude_graph']))
                        <div class="mt-4">{!! $e['altitude_graph'] !!}</div>
                    @endif
                    @if (!empty($e) && is_array($e) && !empty($e['year_graph']))
                        <div class="mt-2">{!! $e['year_graph'] !!}</div>
                    @endif
                    @if (!empty($lightCurve))
                        <div class="mt-4">
                            <h4 class="text-sm font-semibold mb-2">{{ __('Light curve') }}</h4>
                            {!! $lightCurve !!}
                            <p class="mt-1 text-xs text-gray-400">
                                {{ __('Predicted total magnitude m = H + 5 log Δ + K log r, with H and K from aerith.net or JPL. The brightness of comets often differs from the prediction by a magnitude or more.') }}
                            </p>
                        </div>
                    @endif
                    {{-- Estimated magnitudes are rendered in the main page after the ephemerides block. --}}
                </div>
            </td>
        </tr>
    @endif
</tbody>
