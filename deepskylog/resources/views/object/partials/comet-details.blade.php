@php
    // Prepare comet magnitude chart points (moved out of show.blade.php)
    $__dsl_top_raw = is_array($comet_magnitudes ?? null) ? count($comet_magnitudes) : 0;
    $__dsl_top_filtered = 0;
    $__dsl_top_chart_points = [];
    if (!empty($comet_magnitudes) && is_array($comet_magnitudes)) {
        foreach ($comet_magnitudes as $p) {
            $m = $p['mag'] ?? null;
            if ($m === 99.9 || $m === -99.9) {
                continue;
            }
            if (is_numeric($m)) {
                $__dsl_top_chart_points[] = [
                    'date' => $p['date'] ?? null,
                    'mag' => floatval($m),
                ];
            }
        }
        $__dsl_top_filtered = count($__dsl_top_chart_points);
    }
@endphp

@php $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp

@if ($isCometLocal && $__dsl_top_filtered > 0)
    @php
        // Server-side SVG magnitude chart (no JavaScript)
        $svg = null;
        try {
            $points = $__dsl_top_chart_points;
            $w = 600;
            $h = 240;
            $mL = 44;
            $mR = 8;
            $mT = 10;
            $mB = 28;
            $plotW = $w - $mL - $mR;
            $plotH = $h - $mT - $mB;
            $ts = array_map(function ($p) {
                return strtotime($p['date'] ?? '');
            }, $points);
            $mags = array_map(function ($p) {
                return is_numeric($p['mag']) ? floatval($p['mag']) : null;
            }, $points);
            $valid = [];
            for ($i = 0; $i < count($points); $i++) {
                if ($ts[$i] && is_numeric($mags[$i])) {
                    $valid[] = ['ts' => $ts[$i], 'mag' => $mags[$i], 'date' => $points[$i]['date']];
                }
            }
            if (count($valid) > 0) {
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
                $circles = [];
                foreach ($valid as $vp) {
                    $x = $mL + (($vp['ts'] - $minTs) / ($maxTs - $minTs)) * $plotW;
                    // map mag so larger mag (dimmer) is lower on chart
                    $y = $mT + (($vp['mag'] - $minMag) / ($maxMag - $minMag)) * $plotH;
                    $pointsAttr[] = sprintf('%.1f,%.1f', $x, $y);
                    $circles[] = sprintf('<circle cx="%.1f" cy="%.1f" r="3" fill="#3b82f6" />', $x, $y);
                }
                $poly = implode(' ', $pointsAttr);

                // X axis labels: min, mid, max
                $midTs = intval(($minTs + $maxTs) / 2);
                $labelMin = date('Y-m-d', $minTs);
                $labelMid = date('Y-m-d', $midTs);
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
                $svg .= '<style>.dsl-txt{font-family:Arial,Helvetica,sans-serif;font-size:11px;fill:#bbbbbb}</style>';
                // background
                $svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="transparent" />';
                // axes
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

                // y labels
                $svg .=
                    '<text x="6" y="' . ($mT + 10) . '" class="dsl-txt">' . htmlspecialchars($yLabelMin) . '</text>';
                $svg .=
                    '<text x="6" y="' . ($h - $mB) . '" class="dsl-txt">' . htmlspecialchars($yLabelMax) . '</text>';

                // x labels
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

                // polyline
                $svg .=
                    '<polyline points="' .
                    $poly .
                    '" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />';
                // circles
                $svg .= implode('', $circles);

                $svg .= '</svg>';
            }
        } catch (\Throwable $_) {
            $svg = null;
        }
    @endphp

    @if (!empty($svg))
        <div class="mt-4">{!! $svg !!}</div>
    @endif
@endif
@php
    $__dsl_top_raw = is_array($comet_magnitudes ?? null) ? count($comet_magnitudes) : 0;
    $__dsl_top_filtered = 0;
    $__dsl_top_chart_points = [];
    if (!empty($comet_magnitudes) && is_array($comet_magnitudes)) {
        foreach ($comet_magnitudes as $p) {
            $m = $p['mag'] ?? null;
            if ($m === 99.9 || $m === -99.9) {
                continue;
            }
            if (is_numeric($m)) {
                $__dsl_top_chart_points[] = [
                    'date' => $p['date'] ?? null,
                    'mag' => floatval($m),
                ];
            }
        }
        $__dsl_top_filtered = count($__dsl_top_chart_points);
    }
@endphp

{{-- Comet details: delegate coordinates, magnitude and ephemerides graphs to Livewire component --}}
@php $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp
@if ($isCometLocal)
    @php
        $cdOptions = [
            'objectId' => (string) ($session->id ?? ''),
            'initial' => [
                'magnitudes' => $__dsl_top_chart_points ?? [],
                'ephemerides' => $ephemerides ?? null,
                'sourceTypeRaw' => $session->source_type_raw ?? null,
            ],
        ];
    @endphp
    @unless ($suppressLivewire ?? false)
        @livewire('comet-details', $cdOptions)
    @endunless
@endif
