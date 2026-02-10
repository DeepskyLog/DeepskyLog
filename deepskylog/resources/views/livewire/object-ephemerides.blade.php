@php $e = $ephemerides ?? null; @endphp

<tbody wire:key="object-ephemerides-{{ $objectId ?? 'none' }}">
    @if (!$e)
        @auth
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
        @endauth
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
        {{-- RA/Dec and ephemerides rows rendered by Livewire so guests also receive them when available --}}
        @if (empty($suppressTopRaDec))
            <tr>
                <td class="pr-4 font-medium">{{ __('RA / Dec') }}</td>
                <td id="dsl-top-ra-dec">
                    @if (isset($e['raDeg']) && isset($e['decDeg']) && is_numeric($e['raDeg']) && is_numeric($e['decDeg']))
                        @php
                            // Format RA (degrees) to HhMmSs and Dec to ±D°MM'SS"
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
                        {{ $raStr }} / {{ $decStr }}
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endif

        @if (empty($suppressEphemerides))
            @auth
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
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endauth

            @guest
                {{-- Guests: show ephemerides rows when Livewire has computed them --}}
                <tr id="ephem-rts-row-guest">
                    <td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
                    <td id="ephem-rts-cell-guest">
                        <span class="font-mono"
                            @if ($rTitle) title="{{ $rTitle }}" @endif>{{ $showR }}</span>
                        <span class="text-gray-400 px-2">/</span>
                        <span class="font-mono">{{ $showT }}</span>
                        <span class="text-gray-400 px-2">/</span>
                        <span class="font-mono"
                            @if ($sTitle) title="{{ $sTitle }}" @endif>{{ $showS }}</span>
                    </td>
                </tr>

                <tr id="ephem-best-row-guest">
                    <td class="pr-4 font-medium">{{ __('Best time') }}</td>
                    <td id="ephem-best-cell-guest">{{ $e['best_time'] ?? '—' }}</td>
                </tr>

                <tr id="ephem-max-row-guest">
                    <td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
                    <td id="ephem-max-cell-guest">
                        @if (isset($e['max_height_at_night']) && $e['max_height_at_night'] !== null)
                            {{ $e['max_height_at_night'] }}°
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endguest
        @endif

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

        @auth
            {{-- Do not render heavy graph blobs for the Moon: Moon pages use a Livewire-only flow and avoid inline graphs. --}}
            @if (!empty($e['altitude_graph']) && ($sourceTypeRaw ?? '') !== 'moon')
                <tr>
                    <td colspan="2" class="pt-3">{!! $e['altitude_graph'] !!}</td>
                </tr>
            @endif

            @if (!empty($e['year_graph']) && ($sourceTypeRaw ?? '') !== 'moon')
                <tr>
                    <td colspan="2" class="pt-2">{!! $e['year_graph'] !!}</td>
                </tr>
            @endif
        @endauth

        @php
            // Decide whether this object appears to be a planet.
            $objectSlugLocal = $e['objectSlug'] ?? ($e['objectId'] ?? null);
            $planetKeyLocal = null;
            if (!empty($objectSlugLocal) && is_string($objectSlugLocal)) {
                $planetKeyLocal = preg_replace('/[^a-z]/', '', mb_strtolower($objectSlugLocal));
            }
            $knownPlanets = [
                'mercury',
                'venus',
                'earth',
                'mars',
                'jupiter',
                'saturn',
                'uranus',
                'neptune',
                'pluto',
                'sun',
                'moon',
            ];
            $isPlanetLocal =
                ($sourceTypeRaw ?? '') === 'planet' || ($planetKeyLocal && in_array($planetKeyLocal, $knownPlanets));
        @endphp

        @if ($isPlanetLocal && !empty($e['year_magnitude_graph']))
            <tr>
                <td colspan="2" class="pr-4 font-medium pt-4">{{ __('Magnitude during the year') }}</td>
            </tr>
            <tr>
                <td colspan="2" class="pt-2">{!! $e['year_magnitude_graph'] !!}</td>
            </tr>
        @endif
        @if ($isPlanetLocal && !empty($e['year_diameter_graph']))
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

            // Build a sanitized ephemerides payload for embedding in the page.
            // This must not contain any heavy graph HTML blobs which are
            // unnecessary for preview consumers and can leak into the aside.
            $sanitizedEphem = [];
            if (is_array($e)) {
                $sanitizedEphem = $e;
            } elseif (is_object($e)) {
                $sanitizedEphem = (array) $e;
            } else {
                try {
                    $decoded = @json_decode((string) $e, true);
                    if (is_array($decoded)) {
                        $sanitizedEphem = $decoded;
                    }
                } catch (\Throwable $_) {
                    $sanitizedEphem = [];
                }
            }

            // Normalise payload shapes: some callers may supply an indexed array
            // of ephemerides entries (e.g. [0 => [...], 1 => [...]]) while the
            // view expects a single associative ephemerides object. Prefer the
            // first numeric entry when present to avoid embedding an array-of-arrays
            // into the inline payload which leads to inconsistent client snapshots.
            try {
                if (
                    is_array($sanitizedEphem) &&
                    isset($sanitizedEphem[0]) &&
                    is_array($sanitizedEphem[0]) &&
                    (isset($sanitizedEphem[0]['date']) ||
                        isset($sanitizedEphem[0]['rising']) ||
                        isset($sanitizedEphem[0]['transit']))
                ) {
                    $sanitizedEphem = $sanitizedEphem[0];
                }
            } catch (\Throwable $_) {
                // no-op: keep original shape on error
            }
            foreach (['altitude_graph', 'year_graph', 'year_magnitude_graph', 'year_diameter_graph'] as $gk) {
                if (isset($sanitizedEphem[$gk])) {
                    unset($sanitizedEphem[$gk]);
                }
            }

            $inlinePayload = [
                'objectId' => $objectId ?? null,
                'objectSlug' => $objectSlug ?? ($objectId ?? null),
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
                // Embed sanitized ephemerides (graphs removed)
                'ephemerides' => $sanitizedEphem,
                '_ts' => \Carbon\Carbon::now()->toIso8601String(),
            ];
            $encoded = base64_encode(json_encode($inlinePayload));
        @endphp
        @if (empty($suppressEphemerides))
            <tr id="dsl-ephem-payload-row" style="display:none">
                <td colspan="2">
                    <div id="dsl-ephem-payload" data-dsl-ephem-payload="{{ $encoded }}" style="display:none">
                    </div>
                </td>
            </tr>
            <script>
                (function() {
                    try {
                        // Immediate fallback: decode the inline Base64 payload (if present)
                        // and update the top-of-page illuminated fraction so guests and
                        // any clients get a fast synchronous update even if other handlers
                        // miss the Livewire event. This runs during initial render.
                        // If a Moon-specific Livewire component is present on the page,
                        // prefer that authoritative path and skip the inline payload fallback.
                        if (document.getElementById('dsl-moon-ephem-cell')) return;
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
    @endif
</tbody>
