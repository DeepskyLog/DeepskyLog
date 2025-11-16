@php $e = $ephemerides ?? null; @endphp

<tbody wire:key="object-ephemerides-{{ $objectId ?? 'none' }}">
    @if (!$e)
        <tr>
            <td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
            <td class="text-sm text-gray-500">{{ __('Ephemerides not available for this object or location.') }}</td>
        </tr>
        <tr>
            <td class="pr-4 font-medium">{{ __('Best time') }}</td>
            <td class="text-sm text-gray-500">—</td>
        </tr>
        <tr>
            <td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
            <td class="text-sm text-gray-500">—</td>
        </tr>
    @else
        @php
            $r = $e['rising'] ?? null;
            $t = $e['transit'] ?? null;
            $s = $e['setting'] ?? null;
            $showR = $r ?: '—';
            $showT = $t ?: '—';
            $showS = $s ?: '—';
            $max = $e['max_height_at_night'] ?? ($e['max_height'] ?? null);
            $rTitle = '';
            $sTitle = '';
            if (is_null($r) && is_null($s)) {
                if (!is_null($max)) {
                    if ((float) $max < 0.0) {
                        $rTitle = $sTitle = __('Never rises at your location on this date');
                    } else {
                        $rTitle = $sTitle = __('Circumpolar — does not set at your location on this date');
                    }
                } else {
                    $rTitle = $sTitle = __('No rise/set data');
                }
            } else {
                if (is_null($r)) {
                    $rTitle = __('Does not rise at your location on this date');
                }
                if (is_null($s)) {
                    $sTitle = __('Does not set at your location on this date');
                }
            }
        @endphp
        <tr id="ephem-rts-row-live">
            <td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
            <td id="ephem-rts-cell">
                <span class="font-mono"
                    @if ($rTitle) title="{{ $rTitle }}" @endif>{{ $showR }}</span>
                <span class="text-gray-400 px-2">/</span>
                <span class="font-mono">{{ $showT }}</span>
                <span class="text-gray-400 px-2">/</span>
                <span class="font-mono"
                    @if ($sTitle) title="{{ $sTitle }}" @endif>{{ $showS }}</span>
            </td>
        </tr>

        <tr id="ephem-best-row-live">
            <td class="pr-4 font-medium">{{ __('Best time') }}</td>
            <td id="ephem-best-cell">{{ $e['best_time'] ?? '—' }}</td>
        </tr>

        <tr id="ephem-max-row-live">
            <td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
            <td id="ephem-max-cell">
                @if (isset($e['max_height_at_night']) && $e['max_height_at_night'] !== null)
                    {{ $e['max_height_at_night'] }}°
                    <!--
@elseif(isset($e['max_height']) && $e['max_height'] !== null)
{{ $e['max_height'] }}° -->
                @else
                    —
                @endif
            </td>
        </tr>

        {{-- Inner-planet events (Mercury/Venus): inferior/superior conjunction and greatest elongations --}}
        @php
            $infIso = $e['inferior_conjunction'] ?? null;
            $supIso = $e['superior_conjunction'] ?? null;
            $westIso = $e['greatest_western_elongation'] ?? null;
            $eastIso = $e['greatest_eastern_elongation'] ?? null;
            // determine object/planet key to decide which events are shown
            $objectSlug = $e['objectSlug'] ?? ($e['objectId'] ?? null);
            $planetKey = null;
            if (!empty($objectSlug) && is_string($objectSlug)) {
                $planetKey = preg_replace('/[^a-z]/', '', mb_strtolower($objectSlug));
            }
            $isInnerPlanet = in_array($planetKey, ['mercury', 'venus']);
            $fmtDate = function ($s) {
                if (empty($s)) {
                    return '—';
                }
                try {
                    $c = \Carbon\Carbon::parse($s);
                    // Show only the date (no time)
                    return $c->format('Y-m-d');
                } catch (\Throwable $_) {
                    return (string) $s;
                }
            };
            $showInf = $fmtDate($infIso);
            $showSup = $fmtDate($supIso);
            $showWest = $fmtDate($westIso);
            $showEast = $fmtDate($eastIso);
            $opIso = $e['opposition'] ?? null;
            $conIso = $e['conjunction'] ?? null;
            $periIso = $e['perihelion'] ?? null;
            $aphIso = $e['aphelion'] ?? null;
            $showOpp = $fmtDate($opIso);
            $showCon = $fmtDate($conIso);
            $showPeri = $fmtDate($periIso);
            $showAph = $fmtDate($aphIso);
            // Only show inner-planet specific events for Mercury and Venus
            $hasInnerEvents = $isInnerPlanet && ($infIso || $supIso || $westIso || $eastIso);
            $hasAnyEvent = $hasInnerEvents || $opIso || $conIso || $periIso || $aphIso ? true : false;
        @endphp
        @if ($hasAnyEvent)
            @if ($hasInnerEvents)
                <tr>
                    <td class="pr-4 font-medium">{{ __('Inferior conjunction') }}</td>
                    <td>{{ $showInf }}</td>
                </tr>
                <tr>
                    <td class="pr-4 font-medium">{{ __('Superior conjunction') }}</td>
                    <td>{{ $showSup }}</td>
                </tr>
                <tr>
                    <td class="pr-4 font-medium">{{ __('Greatest western elongation') }}</td>
                    <td>{{ $showWest }}</td>
                </tr>
                <tr>
                    <td class="pr-4 font-medium">{{ __('Greatest eastern elongation') }}</td>
                    <td>{{ $showEast }}</td>
                </tr>
            @endif
            @if ($opIso || $conIso)
                <tr>
                    <td class="pr-4 font-medium">{{ __('Opposition') }}</td>
                    <td>{{ $showOpp }}</td>
                </tr>
                <tr>
                    <td class="pr-4 font-medium">{{ __('Conjunction') }}</td>
                    <td>{{ $showCon }}</td>
                </tr>
            @endif
            @if ($periIso || $aphIso)
                <tr>
                    <td class="pr-4 font-medium">{{ __('Perihelion') }}</td>
                    <td>{{ $showPeri }}</td>
                </tr>
                <tr>
                    <td class="pr-4 font-medium">{{ __('Aphelion') }}</td>
                    <td>{{ $showAph }}</td>
                </tr>
            @endif
        @endif

        @if (!empty($e['altitude_graph']))
            <tr>
                <td colspan="2" class="pt-3">{!! $e['altitude_graph'] !!}</td>
            </tr>
        @endif

        @if (!empty($e['year_graph']))
            <tr>
                <td colspan="2" class="pt-2">{!! $e['year_graph'] !!}</td>
            </tr>
        @endif

        @if (!empty($e['year_magnitude_graph']))
            <tr>
                <td colspan="2" class="pr-4 font-medium pt-4">{{ __('Magnitude during the year') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="pt-2">{!! $e['year_magnitude_graph'] !!}</td>
            </tr>
        @endif
        @if (!empty($e['year_diameter_graph']))
            <tr>
                <td colspan="2" class="pr-4 font-medium pt-4">{{ __('Diameter during the year') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="pt-2">{!! $e['year_diameter_graph'] !!}</td>
            </tr>
        @endif
    @endif
    @if (!empty($e))
        @php
            // format RA (deg -> HhMMmSSs) and Dec (deg -> ±D°MM'SS") for inclusion in the payload
$formatRa = function ($raDeg) {
    if ($raDeg === null || $raDeg === '' || !is_numeric($raDeg)) {
        return null;
    }
    $totalHours = floatval($raDeg) / 15.0;
    if (!is_finite($totalHours)) {
        return null;
    }
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
    return sprintf('%dh%02dm%02ds', $h, $m, $s);
};
$formatDec = function ($decDeg) {
    if ($decDeg === null || $decDeg === '' || !is_numeric($decDeg)) {
        return null;
    }
    $dabs = abs(floatval($decDeg));
    $sign = floatval($decDeg) < 0 ? '-' : '';
    $d = floor($dabs);
    $m = floor(($dabs - $d) * 60);
    $s = round((($dabs - $d) * 60 - $m) * 60);
    if ($s >= 60) {
        $s = 0;
        $m += 1;
    }
    if ($m >= 60) {
        $m = 0;
        $d += 1;
    }
    return sprintf('%s%d°%02d\'%02d"', $sign, $d, $m, $s);
            };

            $inlinePayload = [
                'objectId' => $objectId ?? null,
                'objectSlug' => $objectId ?? null,
                'date' => $e['date'] ?? null,
                'raDeg' => $e['raDeg'] ?? null,
                'decDeg' => $e['decDeg'] ?? null,
                'raHms' => isset($e['raDeg']) && is_numeric($e['raDeg']) ? $formatRa($e['raDeg']) : null,
                'decDms' => isset($e['decDeg']) && is_numeric($e['decDeg']) ? $formatDec($e['decDeg']) : null,
                'constellation' => $e['constellation'] ?? null,
                'constellation_code' => $e['constellation_code'] ?? null,
                'inferior_conjunction' => $e['inferior_conjunction'] ?? null,
                'superior_conjunction' => $e['superior_conjunction'] ?? null,
                'greatest_western_elongation' => $e['greatest_western_elongation'] ?? null,
                'greatest_eastern_elongation' => $e['greatest_eastern_elongation'] ?? null,
                'opposition' => $e['opposition'] ?? null,
                'conjunction' => $e['conjunction'] ?? null,
                'perihelion' => $e['perihelion'] ?? null,
                'aphelion' => $e['aphelion'] ?? null,
                'diam1' => $e['diam1'] ?? null,
                'diam2' => $e['diam2'] ?? null,
                'mag' => $e['mag'] ?? null,
                'illuminated_fraction' => $e['illuminated_fraction'] ?? null,
                'ephemerides' => $e,
                '_ts' => \Carbon\Carbon::now()->toIso8601String(),
            ];
            $encoded = base64_encode(json_encode($inlinePayload));
        @endphp
        <tr id="dsl-ephem-payload-row" style="display:none">
            <td colspan="2">
                <div id="dsl-ephem-payload" data-dsl-ephem-payload="{{ $encoded }}" style="display:none"></div>
            </td>
        </tr>
        <script>
            (function() {
                try {
                    // Immediate fallback: decode the inline Base64 payload (if present)
                    // and update the top-of-page illuminated fraction so guests and
                    // any clients get a fast synchronous update even if other handlers
                    // miss the Livewire event. This runs during initial render.
                    var el = document.getElementById('dsl-ephem-payload');
                    if (!el) return;
                    var raw = el.getAttribute('data-dsl-ephem-payload');
                    if (!raw) return;
                    var obj = null;
                    try {
                        obj = JSON.parse(atob(raw));
                    } catch (e) {
                        try {
                            obj = JSON.parse(raw);
                        } catch (e2) {
                            obj = null;
                        }
                    }
                    if (!obj) return;
                    var illum = (typeof obj.illuminated_fraction !== 'undefined') ? obj.illuminated_fraction : (obj
                        .ephemerides && obj.ephemerides.illuminated_fraction ? obj.ephemerides.illuminated_fraction :
                        null);
                    if (illum !== null && illum !== '' && !isNaN(Number(illum))) {
                        var top = document.getElementById('dsl-top-illum');
                        if (top) {
                            try {
                                top.textContent = (Number(illum) * 100.0).toFixed(1) + '%';
                            } catch (e) {}
                        }
                    }
                } catch (e) {}
            })();
        </script>
    @endif
</tbody>
