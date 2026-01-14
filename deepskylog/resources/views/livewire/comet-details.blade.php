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
    <tr>
        <td class="pr-4 font-medium">{{ __('Coordinates') }}</td>
        <td>
            @if (!empty($e) && isset($e['raDeg']) && isset($e['decDeg']) && is_numeric($e['raDeg']) && is_numeric($e['decDeg']))
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
            @else
                —
            @endif
        </td>
    </tr>

    <tr>
        <td class="pr-4 font-medium">{{ __('Constellation') }}</td>
        <td id="dsl-details-constellation">
            @if (!empty($e) && !empty($e['constellation']))
                {{ $e['constellation'] }}@else—
            @endif
        </td>
    </tr>


    {{-- Magnitude / Altitude row removed per UI request --}}

    <tr>
        <td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
        <td id="dsl-details-rise-transit-set">
            @if (!empty($e))
                {{ ($e['rising'] ?? ($e['rise'] ?? '—')) . (isset($e['transit']) ? ' / ' . $e['transit'] : '') . (isset($e['setting']) ? ' / ' . $e['setting'] : '') }}@else—
            @endif
        </td>
    </tr>
    <tr>
        <td class="pr-4 font-medium">{{ __('Best time') }}</td>
        <td id="dsl-details-best-time">
            @if (!empty($e) && !empty($e['best_time']))
                {{ $e['best_time'] }}
            @elseif(!empty($e) && !empty($e['bestTime']))
                {{ $e['bestTime'] }}@else—
            @endif
        </td>
    </tr>
    <tr>
        <td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
        <td id="dsl-details-max-alt">{{ $maxAltDisplay ?? '—' }}</td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="mt-4">
                <div class="mt-2">
                    @php
                        // Server-side SVG comet magnitude chart (no JS)
                        $svg = null;
                        try {
                            $pts = is_array($mags ?? null) ? $mags : [];
                            $valid = [];
                            foreach ($pts as $p) {
                                $d = $p['date'] ?? null;
                                $mg = $p['mag'] ?? null;
                                if ($d && is_numeric($mg) && floatval($mg) !== 99.9 && floatval($mg) !== -99.9) {
                                    $valid[] = ['ts' => strtotime($d), 'mag' => floatval($mg), 'date' => $d];
                                }
                            }
                            if (count($valid) > 0) {
                                $w = 640;
                                $h = 260;
                                $mL = 44;
                                $mR = 10;
                                $mT = 12;
                                $mB = 30;
                                $plotW = $w - $mL - $mR;
                                $plotH = $h - $mT - $mB;
                                $minTs = min(array_column($valid, 'ts'));
                                $maxTs = max(array_column($valid, 'ts'));
                                if ($minTs == $maxTs) {
                                    $maxTs = $minTs + 86400;
                                }
                                $minMag = min(array_column($valid, 'mag'));
                                $maxMag = max(array_column($valid, 'mag'));
                                if ($minMag == $maxMag) {
                                    $minMag -= 0.5;
                                    $maxMag += 0.5;
                                }
                                $pointsAttr = [];
                                $dots = [];
                                foreach ($valid as $vp) {
                                    $x = $mL + (($vp['ts'] - $minTs) / ($maxTs - $minTs)) * $plotW;
                                    $y = $mT + (($vp['mag'] - $minMag) / ($maxMag - $minMag)) * $plotH;
                                    $pointsAttr[] = sprintf('%.1f,%.1f', $x, $y);
                                    $dots[] = sprintf('<circle cx="%.1f" cy="%.1f" r="3" fill="#3b82f6" />', $x, $y);
                                }
                                $poly = implode(' ', $pointsAttr);
                                $labelMin = date('Y-m-d', $minTs);
                                $labelMid = date('Y-m-d', intval(($minTs + $maxTs) / 2));
                                $labelMax = date('Y-m-d', $maxTs);
                                $yLabelMin = number_format($minMag, 1);
                                $yLabelMax = number_format($maxMag, 1);
                                $svg =
                                    '<svg xmlns="http://www.w3.org/2000/svg" width="' .
                                    $w .
                                    '" height="' .
                                    $h .
                                    '" viewBox="0 0 ' .
                                    $w .
                                    ' ' .
                                    $h .
                                    '">';
                                $svg .=
                                    '<style>.dsl-txt{font-family:Arial,Helvetica,sans-serif;font-size:11px;fill:#bbbbbb}</style>';
                                $svg .=
                                    '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="transparent" />';
                                $svg .=
                                    '<line x1="' .
                                    $mL .
                                    '" y1="' .
                                    $mT .
                                    '" x2="' .
                                    $mL .
                                    '" y2="' .
                                    ($h - $mB) .
                                    '" stroke="#444" stroke-width="1"/>';
                                $svg .=
                                    '<line x1="' .
                                    $mL .
                                    '" y1="' .
                                    ($h - $mB) .
                                    '" x2="' .
                                    ($w - $mR) .
                                    '" y2="' .
                                    ($h - $mB) .
                                    '" stroke="#444" stroke-width="1"/>';
                                $svg .=
                                    '<text x="8" y="' .
                                    ($mT + 10) .
                                    '" class="dsl-txt">' .
                                    htmlspecialchars($yLabelMin) .
                                    '</text>';
                                $svg .=
                                    '<text x="8" y="' .
                                    ($h - $mB) .
                                    '" class="dsl-txt">' .
                                    htmlspecialchars($yLabelMax) .
                                    '</text>';
                                $svg .=
                                    '<text x="' .
                                    $mL .
                                    '" y="' .
                                    ($h - 6) .
                                    '" class="dsl-txt">' .
                                    htmlspecialchars($labelMin) .
                                    '</text>';
                                $svg .=
                                    '<text x="' .
                                    ($mL + $plotW / 2 - 30) .
                                    '" y="' .
                                    ($h - 6) .
                                    '" class="dsl-txt">' .
                                    htmlspecialchars($labelMid) .
                                    '</text>';
                                $svg .=
                                    '<text x="' .
                                    ($w - $mR - 60) .
                                    '" y="' .
                                    ($h - 6) .
                                    '" class="dsl-txt">' .
                                    htmlspecialchars($labelMax) .
                                    '</text>';
                                $svg .=
                                    '<polyline points="' .
                                    $poly .
                                    '" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />';
                                $svg .= implode('', $dots);
                                $svg .= '</svg>';
                            }
                        } catch (\Throwable $_) {
                            $svg = null;
                        }
                    @endphp
                    @if (!empty($svg))
                        {!! $svg !!}
                    @endif
                </div>
                {{-- Altitude and year graphs rendered under the maximum-altitude value --}}
                @if (!empty($e) && is_array($e) && !empty($e['altitude_graph']))
                    <div class="mt-4">{!! $e['altitude_graph'] !!}</div>
                @endif
                @if (!empty($e) && is_array($e) && !empty($e['year_graph']))
                    <div class="mt-2">{!! $e['year_graph'] !!}</div>
                @endif
            </div>


        </td>
    </tr>

</tbody>
