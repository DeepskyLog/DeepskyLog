@php
    $mags = is_array($magnitudes ?? null) ? $magnitudes : [];
    $e = $ephemerides ?? null;
@endphp
@php
    // Prepare a display string for maximum altitude to avoid Blade raw-block placeholders
    $maxAltDisplay = null;
    if (!empty($e) && (isset($e['max_height_at_night']) || isset($e['max_height']))) {
        $mVal = $e['max_height_at_night'] ?? $e['max_height'];
        if (is_numeric($mVal)) {
            $maxAltDisplay = number_format($mVal, 1) . '°';
        }
    }
@endphp
<tbody wire:key="comet-details-{{ $objectId ?? 'none' }}">
    @if (!empty($e) && isset($e['raDeg']) && isset($e['decDeg']) && is_numeric($e['raDeg']) && is_numeric($e['decDeg']))
        <tr>
            <td class="pr-4 font-medium">{{ __('Coordinates') }}</td>
            <td>
                @php
                    $raDeg = floatval($e['raDeg']);
                    $decDeg = floatval($e['decDeg']);
                    $totalHours = $raDeg / 15.0;
                    $h = floor($totalHours);
                    $m = floor(($totalHours - $h) * 60);
                    $s = round((($totalHours - $h) * 60 - $m) * 60);
                    if ($s >= 60) {
                        $s = 0;
                        $m += 1;
                    }
                    if ($m >= 60) {
                        $m = 0;
                        $h = ($h + 1) % 24;
                    }
                    $raStr = sprintf('%dh%02dm%02ds', $h, $m, $s);

                    $dabs = abs($decDeg);
                    $sign = $decDeg < 0 ? '-' : '';
                    $d = floor($dabs);
                    $dm = floor(($dabs - $d) * 60);
                    $ds = round((($dabs - $d) * 60 - $dm) * 60);
                    if ($ds >= 60) {
                        $ds = 0;
                        $dm += 1;
                    }
                    if ($dm >= 60) {
                        $dm = 0;
                        $d += 1;
                    }
                    $decStr =
                        $sign .
                        $d .
                        '°' .
                        str_pad($dm, 2, '0', STR_PAD_LEFT) .
                        "'" .
                        str_pad($ds, 2, '0', STR_PAD_LEFT) .
                        '"';
                @endphp
                <span class="font-mono">{{ $raStr }} / {{ $decStr }}</span>
            </td>
        </tr>
    @endif

    @if (!empty($e) && !empty($e['constellation']))
        <tr>
            <td class="pr-4 font-medium">{{ __('Constellation') }}</td>
            <td id="dsl-details-constellation">{{ $e['constellation'] }}</td>
        </tr>
    @endif


    {{-- Magnitude / Altitude row removed per UI request --}}

    @if (!empty($e) && (!empty($e['rising']) || !empty($e['rise']) || !empty($e['transit']) || !empty($e['setting'])))
        <tr>
            <td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
            <td id="dsl-details-rise-transit-set">
                {{ ($e['rising'] ?? ($e['rise'] ?? '—')) . (isset($e['transit']) ? ' / ' . $e['transit'] : '') . (isset($e['setting']) ? ' / ' . $e['setting'] : '') }}
            </td>
        </tr>
    @endif
    @if (!empty($e) && (!empty($e['best_time']) || !empty($e['bestTime'])))
        <tr>
            <td class="pr-4 font-medium">{{ __('Best time') }}</td>
            <td id="dsl-details-best-time">
                @if (!empty($e) && !empty($e['best_time']))
                    {{ $e['best_time'] }}
                @elseif(!empty($e) && !empty($e['bestTime']))
                    {{ $e['bestTime'] }}
                @endif
            </td>
        </tr>
    @endif
    @if ($maxAltDisplay !== null && $maxAltDisplay !== '')
        <tr>
            <td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
            <td id="dsl-details-max-alt">{{ $maxAltDisplay }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="2">
            <div class="mt-4">
                <div class="mt-2">
                    {{-- Magnitude chart moved below the year graph to improve layout --}}
                </div>
                {{-- Altitude and year graphs rendered under the maximum-altitude value --}}
                @if (!empty($e) && is_array($e) && !empty($e['altitude_graph']))
                    <div class="mt-4">{!! $e['altitude_graph'] !!}</div>
                @endif
                @if (!empty($e) && is_array($e) && !empty($e['year_graph']))
                    <div class="mt-2">{!! $e['year_graph'] !!}</div>
                @endif
                {{-- Estimated magnitudes are rendered in the main page after the ephemerides block. --}}
            </div>


        </td>
    </tr>

</tbody>
