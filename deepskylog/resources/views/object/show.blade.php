<x-app-layout>
    <div>
        <!-- Use a wider container so the object details area can take more horizontal space.
           Switched from max-w-7xl to max-w-screen-xl which uses more of the viewport on large screens. -->
        <!-- wider container: default to screen-xl, but use an even wider max at xl and above -->
        <!-- Allow full width at xl so the main area can expand; keep comfortable padding -->
        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-6">
                <h1 class="text-3xl font-extrabold">
                    {{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>

                {{-- Summary row: object type, constellation and quick stats (observations/drawings, user-specific counts) --}}
                @php
                    $objectName = $session->name ?? '';
                    // Prefer controller-provided totals when available, otherwise query legacy observations table
                    $totalObs =
                        isset($totalObservations) && $totalObservations !== null
                            ? $totalObservations
                            : \App\Models\ObservationsOld::getObservationsCountForObject($objectName);
                    $drawingsCount =
                        isset($drawings) && is_countable($drawings)
                            ? count($drawings)
                            : \App\Models\ObservationsOld::getDrawingsCountForObject($objectName);
                    $yourObs = auth()->check()
                        ? \App\Models\ObservationsOld::getObservationsCountForUser(auth()->user(), $objectName)
                        : 0;
                    $yourDrawings = auth()->check()
                        ? \App\Models\ObservationsOld::getDrawingsCountForUser(auth()->user(), $objectName)
                        : 0;
                    $lastObs = auth()->check()
                        ? \App\Models\ObservationsOld::getLastObservationDateForUser(auth()->user(), $objectName)
                        : null;
                    $lastDrawing = auth()->check()
                        ? \App\Models\ObservationsOld::getLastDrawingDateForUser(auth()->user(), $objectName)
                        : null;
                @endphp

                <div
                    class="grid grid-cols-1 sm:inline-grid sm:grid-flow-col sm:auto-cols-max gap-y-0 sm:gap-x-6 mt-1 text-sm text-gray-300">
                    <div>
                        <div class="flex items-center gap-0">
                            <span class="text-gray-400">{{ __('Object type') }}</span>
                            <span class="text-white font-medium ml-2">{{ $session->source_type ?? __('Unknown') }}</span>
                        </div>

                        <div class="mt-1 space-y-0">
                            @php
                                $objSlug =
                                    $canonicalSlug ??
                                    ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
                            @endphp

                            <div class="flex items-center justify-start gap-0">
                                <span class="text-gray-400">{{ __('Observations') }}</span>
                                <a href="{{ url('/observations/' . $objSlug) }}"
                                    class="text-white font-medium ml-2 hover:underline">{{ $totalObs ?? 0 }}</a>
                            </div>

                            @auth
                                <div class="flex items-center justify-start gap-0">
                                    <span class="text-gray-400">{{ __('Your observations') }}</span>
                                    <a href="{{ url('/observations/' . auth()->user()->slug . '/' . $objSlug) }}"
                                        class="text-white font-medium ml-2 hover:underline">{{ $yourObs ?? 0 }}</a>
                                </div>

                                <div class="flex items-center justify-start gap-0">
                                    <span class="text-gray-400">{{ __('Your drawings') }}</span>
                                    <a href="{{ url('/observations/drawings/' . auth()->user()->slug . '/' . $objSlug) }}"
                                        class="text-white font-medium ml-2 hover:underline">{{ $yourDrawings ?? 0 }}</a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-0">
                            <span class="text-gray-400">{{ __('Constellation:') }}</span>
                            <span
                                class="text-white font-medium ml-2">{{ $session->constellation ?? __('Unknown') }}</span>
                        </div>

                        <div class="mt-1 space-y-0">
                            @php
                                $objSlug =
                                    $canonicalSlug ??
                                    ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
                            @endphp

                            <div class="flex items-center justify-start gap-0">
                                <span class="text-gray-400">{{ __('Drawings') }}</span>
                                <a href="{{ url('/observations/drawings/' . $objSlug) }}"
                                    class="text-white font-medium ml-2 hover:underline">{{ $drawingsCount ?? 0 }}</a>
                            </div>

                            @auth
                                <div class="flex items-center justify-start gap-0"><span
                                        class="text-gray-400">{{ __('Last observed by you') }}</span><span
                                        class="text-white font-medium ml-2">{{ $lastObs ? $lastObs->translatedFormat('j M Y') : __('Never') }}</span>
                                </div>
                                <div class="flex items-center justify-start gap-0"><span
                                        class="text-gray-400">{{ __('Last drawing by you') }}</span><span
                                        class="text-white font-medium ml-2">{{ $lastDrawing ? $lastDrawing->translatedFormat('j M Y') : __('Never') }}</span>
                                </div>
                            @else
                                {{-- If not authenticated, show last observed/drawing as non-user-specific info only if available (keep existing behaviour) --}}
                                <div class="flex items-center justify-start gap-0"><span class="text-gray-400">&nbsp;</span>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="text-sm text-gray-300 mt-1">
                    {{-- Nearby objects rendered live by the PowerGrid component below. --}}

                    <!-- Use a responsive layout: default to single-column flow, switch to a 6-column grid at xl (>=1280px) -->
                    <!-- For widths <1280px the aside should stack below the main content (no side-by-side at md) -->
                    <div class="flex flex-col gap-4 mt-3 xl:grid xl:grid-cols-6 xl:gap-6">
                        <article class="w-full md:flex-1 xl:col-span-4">
                            <div class="mb-4 text-gray-100">
                                <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                                <table class="table-auto w-full text-sm text-gray-100">
                                    <td class="pr-4 font-medium">{{ __('Also known as') }}</td>
                                    <td>
                                        @php
                                            $altLinks = [];
                                            foreach ($alternatives as $alt) {
                                                $altSlug = \Illuminate\Support\Str::slug($alt, '-');
                                                $url = route('object.show', ['slug' => $altSlug]);
                                                $altLinks[] =
                                                    '<a href="' .
                                                    e($url) .
                                                    '" class="text-gray-300 hover:underline">' .
                                                    e($alt) .
                                                    '</a>';
                                            }
                                        @endphp
                                        {!! implode(', ', $altLinks) !!}
                                    </td>
                                    </tr>
                                    @if (isset($session->ra) && isset($session->decl))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('RA / Dec') }}</td>
                                            <td>{{ $session->ra }} / {{ $session->decl }}</td>
                                        </tr>
                                        @if (!empty($atlasName) || !empty($atlasPage))
                                            <tr>
                                                <td class="pr-4 font-medium">
                                                    @if (!empty($atlasName))
                                                        {{ $atlasName }}
                                                    @endif
                                                    @if (!empty($atlasPage))
                                                        @if (!empty($atlasName))
                                                            <!-- aladin event enrichment bridge moved to main script block -->
                                                        @endif
                                                        {{ __('page:') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (!empty($atlasPage))
                                                        {{ $atlasPage }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endif
                                    @if (isset($session->diam1) || isset($session->diam2))
                                        @if ($session->diam1 > 0.0)
                                            <tr>
                                                <td class="pr-4 font-medium">{{ __('Size') }}</td>
                                                <td>
                                                    @php
                                                        $d1 = $session->diam1 ?? '';
                                                        $d2 = $session->diam2 ?? '';
                                                    @endphp
                                                    {{ $d1 }}' @if (!empty($d1) && !empty($d2))
                                                        x
                                                    @endif {{ $d2 }}'
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                    @if (isset($session->pa) && $session->pa !== null)
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Position angle') }}</td>
                                            <td>{{ strval($session->pa) === '999' ? '-' : $session->pa }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="pr-4 font-medium">{{ __('Description') }}</td>
                                        <td>{!! nl2br(e($session->comments ?? '')) !!}</td>
                                    </tr>
                                    @if (!empty($session->mag))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Magnitude') }}</td>
                                            <td>
                                                @php
                                                    $mag = $session->mag;
                                                    // Some catalogs use 99.9 as a sentinel for unknown magnitude
                                                    $magDisplay =
                                                        is_numeric($mag) && floatval($mag) == 99.9
                                                            ? __('Unknown')
                                                            : $mag;
                                                @endphp
                                                {{ $magDisplay }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($session->subr))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Surface brightness') }}</td>
                                            <td>
                                                @php
                                                    $sb = $session->subr;
                                                    // 99.9 is used in some datasets to mean unknown
                                                    $sbDisplay =
                                                        is_numeric($sb) && floatval($sb) == 99.9 ? __('Unknown') : $sb;
                                                @endphp
                                                {{ $sbDisplay }}
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- (Observation/drawing stats moved to header) --}}

                                    {{-- Ephemerides: date, rise/transit/set, best time, maximum altitude, altitude graph provided by astronomy library --}}
                                    {{-- Date selector moved to global aside Livewire component --}}
                                    {{-- Ephemerides rows are rendered by a Livewire component so they can update live when the aside date changes --}}
                                    @auth
                                        @livewire('object-ephemerides', ['objectId' => (string) ($session->id ?? '')])

                                        {{-- Live-updating contrast reserve and optimum detection magnification via Livewire --}}
                                        {{-- Embed the Livewire component directly so it can render <tr> rows inside this table --}}
                                        @php
                                            // Prefer per-user cached metrics when available so the preview
                                            // shows the persisted Contrast Reserve for the signed-in user's
// standard instrument/location. Fall back to session-provided
// values when no cache record exists or on errors.
$initialCR = $session->contrast_reserve ?? null;
$initialCRCat = $session->contrast_reserve_category ?? null;
$initialCRLoc = $session->contrast_used_location ?? null;
$initialCRInstr = $session->contrast_used_instrument ?? null;
$initialOptMag = $session->optimum_detection_magnification ?? null;
$initialOptEps = $session->optimum_eyepieces ?? [];
try {
    if (auth()->check()) {
        $u = auth()->user();
        $instr = $u->standardInstrument ?? null;
        $loc = $u->standardLocation ?? null;
        if ($instr && $loc && !empty($session->name)) {
            $uom = \App\Models\UserObjectMetric::where('user_id', $u->id)
                ->where('instrument_id', $instr->id)
                ->where('location_id', $loc->id)
                ->where('object_name', $session->name)
                                                            ->first();
                                                        if ($uom) {
                                                            if (is_numeric($uom->contrast_reserve)) {
                                                                $initialCR = $uom->contrast_reserve;
                                                            }
                                                            $initialCRCat =
                                                                $uom->contrast_reserve_category ?? $initialCRCat;
                                                            $initialOptMag =
                                                                $uom->optimum_detection_magnification ?? $initialOptMag;
                                                            $initialOptEps = $uom->optimum_eyepieces
                                                                ? json_decode(
                                                                    json_encode($uom->optimum_eyepieces),
                                                                    true,
                                                                )
                                                                : $initialOptEps;
                                                            // record which instrument/location produced the cached value
                                                            $initialCRLoc = $initialCRLoc ?? ($loc->name ?? null);
                                                            $initialCRInstr = $initialCRInstr ?? ($instr->name ?? null);
                                                        }
                                                    }
                                                }
                                            } catch (\Throwable $_) {
                                                // Fail silently and keep session-provided values
                                            }
                                        @endphp

                                        @livewire('aladin-preview-info', [
                                            'objectId' => (string) ($session->id ?? ''),
                                            'initial' => [
                                                'contrast_reserve' => $initialCR,
                                                'contrast_reserve_category' => $initialCRCat,
                                                'contrast_used_location' => $initialCRLoc,
                                                'contrast_used_instrument' => $initialCRInstr,
                                                'optimum_detection_magnification' => $initialOptMag,
                                                'optimum_eyepieces' => $initialOptEps,
                                            ],
                                        ])
                                    @endauth

                                </table>

                                {{-- Altitude graph now rendered by the Livewire `object-ephemerides` component to avoid duplication --}}

                            </div>

                            {{-- Nearby objects (powergrid) --}}
                            @if (isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl))
                                <div class="mt-6">
                                    <h2 class="text-xl font-semibold text-white">{{ __('Nearby objects') }}</h2>
                                    <div id="nearby-objects-wrapper"
                                        class="mt-2 bg-gray-800 p-3 rounded shadow text-gray-100">
                                        @php
                                            // Determine initial nearby radius and per-page to display.
                                            // Prefer the per-user persisted setting if available.
                                            $nearbyRadiusSelected = 30;
                                            $nearbyPerPageSelected = 25;
                                            try {
                                                if (auth()->check()) {
                                                    $uts = \App\Models\UserTableSetting::where(
                                                        'user_id',
                                                        auth()->user()->id,
                                                    )
                                                        ->where('table_name', 'nearby-objects-table')
                                                        ->first();
                                                    if ($uts && is_array($uts->settings)) {
                                                        if (isset($uts->settings['radiusArcMin'])) {
                                                            $nearbyRadiusSelected = intval(
                                                                $uts->settings['radiusArcMin'],
                                                            );
                                                        }
                                                        if (isset($uts->settings['perPage'])) {
                                                            $nearbyPerPageSelected = intval($uts->settings['perPage']);
                                                        }
                                                    }
                                                }
                                            } catch (\Throwable $_) {
                                                // fallback to defaults
                                            }
                                        @endphp
                                        <div class="flex items-center gap-3 mb-3">
                                            <label class="text-sm text-gray-300">{{ __('Radius:') }}</label>
                                            <select
                                                onchange="(function(v){try{v=parseInt(v,10); if(window.Livewire && typeof Livewire.dispatch==='function'){ Livewire.dispatch('nearbyRadiusChanged', [v]); } else if(window.Livewire && typeof Livewire.dispatchTo==='function'){ Livewire.dispatchTo('nearby-objects-table','nearbyRadiusChanged', [v]); } else if(window.Livewire && typeof Livewire.emit==='function'){ Livewire.emit('nearbyRadiusChanged', [v]); } }catch(e){} })(this.value)"
                                                class="text-sm rounded bg-gray-700 text-gray-100 p-1">
                                                <option value="5"
                                                    {{ $nearbyRadiusSelected === 5 ? 'selected' : '' }}>5'</option>
                                                <option value="10"
                                                    {{ $nearbyRadiusSelected === 10 ? 'selected' : '' }}>10'</option>
                                                <option value="15"
                                                    {{ $nearbyRadiusSelected === 15 ? 'selected' : '' }}>15'</option>
                                                <option value="30"
                                                    {{ $nearbyRadiusSelected === 30 ? 'selected' : '' }}>30'</option>
                                                <option value="60"
                                                    {{ $nearbyRadiusSelected === 60 ? 'selected' : '' }}>1°</option>
                                                <option value="120"
                                                    {{ $nearbyRadiusSelected === 120 ? 'selected' : '' }}>2°</option>
                                            </select>
                                            <div class="text-xs text-gray-400 ml-2">
                                                {{ __('Choose radius to search nearby objects.') }}</div>
                                            <!-- Export names (PDF) button: dispatches to the nearby-objects-table Livewire component -->
                                            <div class="ml-auto">
                                                @php
                                                    // Build a safe URL for the export route using server-known coordinates
                                                    $exportUrl =
                                                        route('object.nearby.names.pdf', [
                                                            'slug' => $canonicalSlug ?? ($session->slug ?? ''),
                                                        ]) . '?';
                                                    $exportUrl .= 'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                    $exportUrl .= '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                    $exportUrl .=
                                                        '&radius=' . rawurlencode($nearbyRadiusSelected ?? 30);
                                                @endphp
                                                <a href="{{ $exportUrl }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center justify-center text-sm font-medium px-3 py-1.5 rounded-md bg-blue-600 text-white hover:bg-blue-700 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"
                                                    aria-label="{{ __('Export names (PDF)') }}">
                                                    {{ __('Export names (PDF)') }}
                                                </a>
                                            </div>
                                        </div>

                                        @php
                                            // Quick server-side nearby debug disabled to avoid high memory
                                            // usage during normal page renders. If you need to run the
                                            // diagnostic locally, re-enable the chunking logic with
                                            // a conservative limit.
                                            $nearbyDebug = [];
                                        @endphp

                                        {{-- Server-side nearby debug output removed — use Livewire/PowerGrid output below. --}}

                                        @php
                                            // Try to provide numeric RA/Dec (degrees) to the Livewire component.
                                            // Controller populates $aladinDefaults with raw and possibly converted values
                                            // (ra_raw, dec_raw, ra_deg, dec_deg). Prefer ra_deg/dec_deg when present.
                                            $nearbyRaDeg = null;
                                            $nearbyDecDeg = null;
                                            try {
                                                if (!empty($aladinDefaults['ra_deg'])) {
                                                    $nearbyRaDeg = (float) $aladinDefaults['ra_deg'];
                                                } elseif (!empty($aladinDefaults['ra_raw'])) {
                                                    // try to parse simple numeric or HH MM SS style RA strings
                                                    $r = trim($aladinDefaults['ra_raw']);
                                                    if (is_numeric($r)) {
                                                        $nearbyRaDeg = (float) $r;
                                                    } else {
                                                        // parse h m s (accept h/m/s or space-separated)
                                                        if (
                                                            preg_match(
                                                                '/^(\d{1,2})[:h\s](\d{1,2})[:m\s](\d+(?:\.\d+)?)/',
                                                                $r,
                                                                $m,
                                                            )
                                                        ) {
                                                            $h = floatval($m[1]);
                                                            $min = floatval($m[2]);
                                                            $sec = floatval($m[3]);
                                                            $nearbyRaDeg = ($h + $min / 60.0 + $sec / 3600.0) * 15.0;
                                                        }
                                                    }
                                                }

                                                if (!empty($aladinDefaults['dec_deg'])) {
                                                    $nearbyDecDeg = (float) $aladinDefaults['dec_deg'];
                                                } elseif (!empty($aladinDefaults['dec_raw'])) {
                                                    $d = trim($aladinDefaults['dec_raw']);
                                                    if (is_numeric($d)) {
                                                        $nearbyDecDeg = (float) $d;
                                                    } else {
                                                        // parse deg min sec like 41°16'06.0" or 41 16 06
            if (
                preg_match(
                    '/^([\+\-]?\d{1,3})[^\d\-\+]*(\d{1,2})[^\d\-\+]*(\d+(?:\.\d+)?)/',
                    $d,
                    $n,
                )
            ) {
                $sign = strpos($d, '-') !== false ? -1 : 1;
                                                            $deg = floatval($n[1]);
                                                            $min = floatval($n[2]);
                                                            $sec = floatval($n[3]);
                                                            $nearbyDecDeg =
                                                                $sign * ($deg + $min / 60.0 + $sec / 3600.0);
                                                        }
                                                    }
                                                }
                                            } catch (\Throwable $_) {
                                                $nearbyRaDeg = null;
                                                $nearbyDecDeg = null;
                                            }
                                        @endphp

                                        @livewire('nearby-objects-table', [
                                            'objectId' => (int) ($session->id ?? 0),
                                            'objectName' => $session->name ?? null,
                                            'ra' => $nearbyRaDeg,
                                            'decl' => $nearbyDecDeg,
                                            'radiusArcMin' => $nearbyRadiusSelected,
                                            'perPage' => $nearbyPerPageSelected,
                                        ])
                                        {{-- Ensure selects inside the nearby objects area use a dark background and light text.
                                            PowerGrid renders its own <select> for per-page; browsers limit styling of <option>,
                                            but we force the select box to match the site's dark theme. --}}
                                        <style>
                                            /* Target selects inside the nearby objects wrapper */
                                            #nearby-objects-wrapper select {
                                                background-color: #374151 !important;
                                                /* tailwind bg-gray-700 */
                                                color: #f3f4f6 !important;
                                                /* tailwind text-gray-100 */
                                                border-color: rgba(255, 255, 255, 0.06) !important;
                                            }

                                            /* Try to style dropdown options where the browser allows it */
                                            #nearby-objects-wrapper select option {
                                                background-color: #374151 !important;
                                                color: #f3f4f6 !important;
                                            }

                                            /* Allow the PowerGrid atlas header to wrap onto multiple lines
                                               when the title is long. The header class is added server-side
                                               by the NearbyObjectsTable component (headerAttribute).
                                               Target broadly to override PowerGrid's internal header markup. */
                                            .atlas-header,
                                            .atlas-header *,
                                            #nearby-objects-wrapper .atlas-header,
                                            #nearby-objects-wrapper .atlas-header * {
                                                white-space: normal !important;
                                                max-width: 7rem !important;
                                                word-break: break-word !important;
                                                overflow-wrap: break-word !important;
                                                line-height: 1.05 !important;
                                                text-align: center !important;
                                            }

                                            /* Ensure the clickable header label can wrap too */
                                            .atlas-header>div,
                                            .atlas-header>span,
                                            .atlas-header>button {
                                                display: inline-block !important;
                                                white-space: normal !important;
                                            }

                                            /* Allow the PowerGrid toggle dropdown to escape clipping. We'll
                                               move the dropdown into document.body and position it there via JS
                                               (portal pattern) to avoid clipping/transform/stacking-context issues. */
                                            #nearby-objects-wrapper {
                                                overflow: visible !important;
                                            }

                                            /* keep a default high z-index when moved; left/top set by JS */
                                            .toggle-columns-base {
                                                position: fixed !important;
                                                z-index: 99999 !important;
                                                transform: none !important;
                                            }
                                        </style>
                                        <script>
                                            (function() {
                                                // Safer dropdown positioning that keeps the dropdown inside its
                                                // original DOM (so Alpine's x-data/x-show scope continues to work).
                                                function isVisible(el) {
                                                    try {
                                                        if (!el) return false;
                                                        var cs = window.getComputedStyle(el);
                                                        return cs && cs.display !== 'none' && cs.visibility !== 'hidden' && el.offsetWidth > 0 && el
                                                            .offsetHeight > 0;
                                                    } catch (e) {
                                                        return false;
                                                    }
                                                }

                                                function positionDropdown(btn, dd) {
                                                    try {
                                                        if (!btn || !dd) return;
                                                        var br = btn.getBoundingClientRect();
                                                        var left = Math.round(br.left);
                                                        var top = Math.round(br.bottom + 6);
                                                        dd.style.position = 'fixed';
                                                        dd.style.left = left + 'px';
                                                        dd.style.top = top + 'px';
                                                        dd.style.zIndex = '99999';

                                                        // clamp horizontally
                                                        var rect = dd.getBoundingClientRect();
                                                        if (rect.right > window.innerWidth - 8) {
                                                            var shift = rect.right - (window.innerWidth - 8);
                                                            dd.style.left = Math.max(8, left - shift) + 'px';
                                                        }
                                                        // flip above if needed
                                                        rect = dd.getBoundingClientRect();
                                                        if (rect.bottom > window.innerHeight - 8) {
                                                            dd.style.top = Math.max(8, Math.round(br.top - rect.height - 6)) + 'px';
                                                        }
                                                    } catch (e) {}
                                                }

                                                // When the toggle button is clicked, wait a short time for Alpine
                                                // to toggle x-show, then find the dropdown and position it.
                                                document.addEventListener('click', function(ev) {
                                                    try {
                                                        var target = ev.target;
                                                        while (target && target !== document.documentElement) {
                                                            try {
                                                                if (target.getAttribute) {
                                                                    var dc = target.getAttribute('data-cy');
                                                                    if (dc && dc.indexOf('toggle-columns-') === 0) {
                                                                        // debug: which toggle button was clicked
                                                                        try {
                                                                            if (console && typeof console.debug === 'function') console.debug(
                                                                                '[dsl] toggle-columns clicked', {
                                                                                    dataCy: dc
                                                                                });
                                                                        } catch (e) {}
                                                                        // wait for Alpine to update DOM
                                                                        setTimeout(function() {
                                                                            try {
                                                                                var dd = document.querySelector('.toggle-columns-base');
                                                                                if (!dd) {
                                                                                    try {
                                                                                        if (console && typeof console.debug === 'function')
                                                                                            console.debug(
                                                                                                '[dsl] toggle-columns: no dropdown element found yet'
                                                                                            );
                                                                                    } catch (e) {}
                                                                                    return;
                                                                                }
                                                                                // debug: dump bounding boxes before positioning
                                                                                try {
                                                                                    var btnRectDebug = target.getBoundingClientRect();
                                                                                    var ddRectDebug = dd.getBoundingClientRect();
                                                                                    if (console && typeof console.debug === 'function')
                                                                                        console.debug(
                                                                                            '[dsl] toggle-columns: found dropdown', {
                                                                                                btnRect: btnRectDebug,
                                                                                                ddRect: ddRectDebug
                                                                                            });
                                                                                } catch (e) {}
                                                                                // If dd is not visible yet, watch for it becoming visible (Alpine/x-transition may delay)
                                                                                if (!isVisible(dd)) {
                                                                                    try {
                                                                                        if (console && typeof console.debug === 'function')
                                                                                            console.debug(
                                                                                                '[dsl] dropdown not visible yet - installing observer+poll'
                                                                                            );
                                                                                    } catch (e) {}
                                                                                    var mo = null;
                                                                                    var poll = null;
                                                                                    var attempts = 0;
                                                                                    var maxAttempts = 33; // ~2s @ 60ms
                                                                                    try {
                                                                                        mo = new MutationObserver(function() {
                                                                                            try {
                                                                                                // Re-query the dropdown node in case Alpine recreated it
                                                                                                dd = document.querySelector(
                                                                                                        '.toggle-columns-base') ||
                                                                                                    dd;
                                                                                                if (isVisible(dd)) {
                                                                                                    try {
                                                                                                        if (console &&
                                                                                                            typeof console.debug ===
                                                                                                            'function') console
                                                                                                            .debug(
                                                                                                                '[dsl] dropdown became visible (observer)'
                                                                                                            );
                                                                                                    } catch (e) {}
                                                                                                    positionDropdown(target, dd);
                                                                                                    try {
                                                                                                        if (mo) mo.disconnect();
                                                                                                    } catch (e) {}
                                                                                                    try {
                                                                                                        if (poll) clearInterval(
                                                                                                            poll);
                                                                                                    } catch (e) {}
                                                                                                }
                                                                                            } catch (e) {}
                                                                                        });
                                                                                        // Observe the parent to catch cases where Alpine recreates the element
                                                                                        var parent = dd && dd.parentNode ? dd.parentNode :
                                                                                            document.body;
                                                                                        mo.observe(parent, {
                                                                                            childList: true,
                                                                                            subtree: true,
                                                                                            attributes: true,
                                                                                            attributeFilter: ['style', 'class',
                                                                                                'aria-hidden'
                                                                                            ]
                                                                                        });

                                                                                        // Polling fallback for environments where MutationObserver doesn't fire or element is recreated
                                                                                        poll = setInterval(function() {
                                                                                            try {
                                                                                                attempts++;
                                                                                                var current = document
                                                                                                    .querySelector(
                                                                                                        '.toggle-columns-base');
                                                                                                if (current && isVisible(current)) {
                                                                                                    try {
                                                                                                        if (console &&
                                                                                                            typeof console.debug ===
                                                                                                            'function') console
                                                                                                            .debug(
                                                                                                                '[dsl] dropdown became visible (poll) after',
                                                                                                                attempts, 'attempts'
                                                                                                            );
                                                                                                    } catch (e) {}
                                                                                                    positionDropdown(target,
                                                                                                        current);
                                                                                                    try {
                                                                                                        if (mo) mo.disconnect();
                                                                                                    } catch (e) {}
                                                                                                    clearInterval(poll);
                                                                                                } else if (attempts >=
                                                                                                    maxAttempts) {
                                                                                                    try {
                                                                                                        if (console &&
                                                                                                            typeof console.debug ===
                                                                                                            'function') console
                                                                                                            .debug(
                                                                                                                '[dsl] dropdown poll timed out'
                                                                                                            );
                                                                                                    } catch (e) {}
                                                                                                    try {
                                                                                                        if (mo) mo.disconnect();
                                                                                                    } catch (e) {}
                                                                                                    clearInterval(poll);
                                                                                                    // Last-resort: force the dropdown visible for debugging so we can inspect it
                                                                                                    try {
                                                                                                        var final = document
                                                                                                            .querySelector(
                                                                                                                '.toggle-columns-base'
                                                                                                            );
                                                                                                        if (final) {
                                                                                                            try {
                                                                                                                final.style
                                                                                                                    .display =
                                                                                                                    'block';
                                                                                                                final.style
                                                                                                                    .visibility =
                                                                                                                    'visible';
                                                                                                                final.style
                                                                                                                    .opacity = '1';
                                                                                                                final.style
                                                                                                                    .position =
                                                                                                                    'fixed';
                                                                                                                final.style.zIndex =
                                                                                                                    '99999';
                                                                                                            } catch (e) {}
                                                                                                            try {
                                                                                                                if (console &&
                                                                                                                    typeof console
                                                                                                                    .debug ===
                                                                                                                    'function')
                                                                                                                    console.debug(
                                                                                                                        '[dsl] forced dropdown visible for debug', {
                                                                                                                            ddRect: final
                                                                                                                                .getBoundingClientRect(),
                                                                                                                            computed: window
                                                                                                                                .getComputedStyle(
                                                                                                                                    final
                                                                                                                                )
                                                                                                                        });
                                                                                                            } catch (e) {}
                                                                                                            try {
                                                                                                                positionDropdown(
                                                                                                                    target,
                                                                                                                    final);
                                                                                                            } catch (e) {}
                                                                                                        }
                                                                                                    } catch (e) {}
                                                                                                }
                                                                                            } catch (e) {}
                                                                                        }, 60);
                                                                                    } catch (e) {
                                                                                        // fallback: try one delayed attempt
                                                                                        setTimeout(function() {
                                                                                            try {
                                                                                                var cur = document.querySelector(
                                                                                                    '.toggle-columns-base');
                                                                                                if (cur && isVisible(cur))
                                                                                                    positionDropdown(target, cur);
                                                                                            } catch (e) {}
                                                                                        }, 220);
                                                                                    }
                                                                                    return;
                                                                                }
                                                                                positionDropdown(target, dd);
                                                                            } catch (e) {}
                                                                        }, 80);
                                                                        return;
                                                                    }
                                                                }
                                                            } catch (e) {}
                                                            target = target.parentElement;
                                                        }
                                                    } catch (e) {}
                                                }, true);

                                                // reposition on resize/scroll while the dropdown is visible
                                                var reposition = function() {
                                                    try {
                                                        var dd = document.querySelector('.toggle-columns-base');
                                                        if (!dd || !isVisible(dd)) return;
                                                        // try to find nearest toggle button
                                                        var buttons = Array.from(document.querySelectorAll('[data-cy]')).filter(function(b) {
                                                            try {
                                                                return b.getAttribute && b.getAttribute('data-cy') && b.getAttribute('data-cy')
                                                                    .indexOf('toggle-columns-') === 0;
                                                            } catch (e) {
                                                                return false;
                                                            }
                                                        });
                                                        if (!buttons.length) return;
                                                        // pick the closest by horizontal distance
                                                        var rect = dd.getBoundingClientRect();
                                                        var best = buttons.reduce(function(acc, b) {
                                                            try {
                                                                var br = b.getBoundingClientRect();
                                                                var dist = Math.abs(br.left - rect.left) + Math.abs(br.top - rect.top);
                                                                if (acc === null || dist < acc.dist) return {
                                                                    btn: b,
                                                                    dist: dist
                                                                };
                                                                return acc;
                                                            } catch (e) {
                                                                return acc;
                                                            }
                                                        }, null);
                                                        if (best && best.btn) {
                                                            try {
                                                                if (console && typeof console.debug === 'function') console.debug(
                                                                    '[dsl] repositioning dropdown', {
                                                                        bestBtnDataCy: best.btn.getAttribute && best.btn.getAttribute('data-cy'),
                                                                        ddRect: dd.getBoundingClientRect()
                                                                    });
                                                            } catch (e) {}
                                                            positionDropdown(best.btn, dd);
                                                        }
                                                    } catch (e) {}
                                                };
                                                window.addEventListener('resize', reposition, {
                                                    passive: true
                                                });
                                                window.addEventListener('scroll', reposition, {
                                                    passive: true
                                                });
                                            })();
                                        </script>
                                        <script>
                                            // Debug-forwarder: intercept PowerGrid toggleColumn events and
                                            // forward a correctly-shaped message to Livewire while emitting
                                            // helpful console debug logs. Also install a tiny XHR observer
                                            // to log outgoing Livewire requests for the nearby table.
                                            (function() {
                                                try {
                                                    var evName = 'pg:toggleColumn-' + 'nearby-objects-table';

                                                    // Lightweight XHR observer: logs outgoing requests to the nearby table endpoint
                                                    (function() {
                                                        try {
                                                            if (window.__dsl_xhr_obs_installed) return;
                                                            window.__dsl_xhr_obs_installed = true;
                                                            // XHR observer
                                                            var origOpen = XMLHttpRequest.prototype.open;
                                                            var origSend = XMLHttpRequest.prototype.send;
                                                            XMLHttpRequest.prototype.open = function(method, url) {
                                                                try {
                                                                    this.__dsl_url = url;
                                                                } catch (e) {}
                                                                return origOpen.apply(this, arguments);
                                                            };
                                                            XMLHttpRequest.prototype.send = function(body) {
                                                                try {
                                                                    if (this.__dsl_url && String(this.__dsl_url).indexOf(
                                                                            '/livewire/message/nearby-objects-table') !== -1) {
                                                                        try {
                                                                            console.debug('[dsl][xhr] Livewire nearby-table XHR request:', this
                                                                                .__dsl_url, body);
                                                                        } catch (e) {}
                                                                        this.addEventListener('load', function() {
                                                                            try {
                                                                                console.debug(
                                                                                    '[dsl][xhr] Livewire nearby-table XHR response status:',
                                                                                    this.status);
                                                                            } catch (e) {}
                                                                        });
                                                                    }
                                                                } catch (e) {}
                                                                return origSend.apply(this, arguments);
                                                            };

                                                            // fetch() observer
                                                            try {
                                                                if (window.fetch && !window.__dsl_fetch_obs_installed) {
                                                                    window.__dsl_fetch_obs_installed = true;
                                                                    var origFetch = window.fetch.bind(window);
                                                                    window.fetch = function(input, init) {
                                                                        try {
                                                                            var url = (typeof input === 'string') ? input : (input && input.url) ||
                                                                                '';
                                                                            if (String(url).indexOf('/livewire/message/nearby-objects-table') !== -
                                                                                1) {
                                                                                try {
                                                                                    console.debug(
                                                                                        '[dsl][fetch] Livewire nearby-table fetch request:',
                                                                                        url, init || input);
                                                                                } catch (e) {}
                                                                                return origFetch(input, init).then(function(res) {
                                                                                    try {
                                                                                        console.debug(
                                                                                            '[dsl][fetch] Livewire nearby-table fetch response status:',
                                                                                            res.status);
                                                                                    } catch (e) {}
                                                                                    return res;
                                                                                });
                                                                            }
                                                                        } catch (e) {}
                                                                        return origFetch(input, init);
                                                                    };
                                                                }
                                                            } catch (e) {}
                                                        } catch (e) {}
                                                    })();

                                                    document.addEventListener(evName, function(e) {
                                                        try {
                                                            if (window.__dsl_forwarding_in_progress) {
                                                                console.debug('[dsl] forwarding already in progress, ignoring event');
                                                                return;
                                                            }

                                                            // Extract requested field
                                                            var field = null;
                                                            try {
                                                                if (Array.isArray(e && e.detail) && e.detail.length) {
                                                                    field = e.detail[0];
                                                                } else if (e && e.detail && typeof e.detail === 'object' && 'field' in e.detail) {
                                                                    field = e.detail.field;
                                                                } else if (typeof e.detail === 'string') {
                                                                    field = e.detail;
                                                                }
                                                            } catch (er) {
                                                                console.debug('[dsl] field extraction failed', er);
                                                            }

                                                            console.debug('[dsl] pg:toggleColumn received', {
                                                                field: field,
                                                                detail: e && e.detail
                                                            });
                                                            if (!field) return;

                                                            // Do NOT stop propagation or prevent default: allow PowerGrid/Alpine
                                                            // built-in handlers to run and produce the Livewire network request.
                                                            // Previously we intercepted and stopped the event which prevented
                                                            // the request from being sent.

                                                            window.__dsl_forwarding_in_progress = true;
                                                            try {
                                                                if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                                                    console.debug(
                                                                        '[dsl] dispatching via Livewire.dispatchTo to nearby-objects-table',
                                                                        field);
                                                                    Livewire.dispatchTo('nearby-objects-table', '__dispatch', [
                                                                        'pg:toggleColumn-nearby-objects-table', [field]
                                                                    ]);
                                                                } else if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                                                    console.debug('[dsl] dispatching via Livewire.dispatch', field);
                                                                    Livewire.dispatch('pg:toggleColumn-nearby-objects-table', [field]);
                                                                } else if (window.Livewire && typeof Livewire.emit === 'function') {
                                                                    console.debug('[dsl] dispatching via Livewire.emit', field);
                                                                    Livewire.emit('pg:toggleColumn-nearby-objects-table', [field]);
                                                                } else {
                                                                    console.debug(
                                                                        '[dsl] no Livewire API found; skipping manual dispatch (allowing native handlers)'
                                                                    );
                                                                }
                                                            } catch (err) {
                                                                console.debug('[dsl] dispatch error', err);
                                                            } finally {
                                                                setTimeout(function() {
                                                                    try {
                                                                        window.__dsl_forwarding_in_progress = false;
                                                                    } catch (e) {}
                                                                }, 50);
                                                            }

                                                            // Close the dropdown by clicking the toggle button which flips
                                                            // the Alpine `open` state. Delay slightly so the Livewire calls
                                                            // can be dispatched first.
                                                            try {
                                                                var btn = document.querySelector('[data-cy="toggle-columns-nearby-objects-table"]');
                                                                if (btn) setTimeout(function() {
                                                                    try {
                                                                        btn.click();
                                                                    } catch (e) {}
                                                                }, 20);
                                                            } catch (err) {}
                                                        } catch (err) {
                                                            console.debug('[dsl] forwarder outer error', err);
                                                        }
                                                    }, {
                                                        passive: false,
                                                        capture: true
                                                    });
                                                } catch (err) {
                                                    console.debug('[dsl] forwarder init error', err);
                                                }
                                            })();
                                        </script>
                                    </div>
                                </div>
                            @endif

                            {{-- Sketches that were DeepskyLog sketch(s) of the week for this object --}}
                            @php
                                // The observations legacy table lives on the mysqlOld connection.
                                // Build a list of observation ids from the legacy DB matching this object's name
$objectSketches = collect();
try {
    $objName = $session->name ?? '';
    if (!empty($objName)) {
        $obsIds = \Illuminate\Support\Facades\DB::connection('mysqlOld')
            ->table('observations')
            ->where('objectname', $objName)
            ->pluck('id')
            ->toArray();

        if (!empty($obsIds)) {
            $objectSketches = \App\Models\SketchOfTheWeek::whereIn(
                'observation_id',
                $obsIds,
            )
                ->orderByDesc('date')
                ->get();
        }
    }
} catch (\Throwable $_) {
    // Fail silently: keep $objectSketches empty so the section simply doesn't render
                                    $objectSketches = collect();
                                }
                            @endphp

                            @if ($objectSketches->isNotEmpty())
                                <div class="mt-6">
                                    <h2 class="text-xl font-semibold text-white">{{ __('Sketch of the Week') }}</h2>
                                    <div class="mt-2">
                                        <x-card>
                                            <div class="flex flex-wrap px-5">
                                                @foreach ($objectSketches as $sketch)
                                                    <x-sketch :sketch="$sketch" />
                                                @endforeach
                                            </div>
                                            {{-- If there are many sketches we could paginate here, but usually this is small --}}
                                        </x-card>
                                    </div>
                                </div>
                            @endif

                        </article>

                        <aside class="w-full xl:col-span-2 xl:w-auto">
                            <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                                <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
                                <ul class="space-y-2 text-sm">
                                    <li>
                                        @if (!empty($canonicalSlug))
                                            <a href="{{ url('/observations/' . $canonicalSlug) }}"
                                                class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                                        @else
                                            <a href="{{ route('observations.index') }}"
                                                class="text-gray-300 hover:underline">{{ __('All observations') }}</a>
                                        @endif
                                    </li>
                                    <li>
                                        @if (!empty($canonicalSlug))
                                            <a href="{{ route('observations.drawings.show', ['slug' => $canonicalSlug]) }}"
                                                class="text-gray-300 hover:underline">{{ __('All drawings') }}</a>
                                        @else
                                            <a href="{{ route('drawings.index') }}"
                                                class="text-gray-300 hover:underline">{{ __('All drawings') }}</a>
                                        @endif
                                    </li>
                                    @auth
                                        @if (!empty($canonicalSlug) && auth()->user()->slug)
                                            <li><a href="{{ route('observations.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                                                    class="text-gray-300 hover:underline">{{ __('My observations') }}</a>
                                            </li>
                                            <li><a href="{{ route('observations.drawings.user.object', ['observer' => auth()->user()->slug, 'object' => $canonicalSlug]) }}"
                                                    class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                                        @else
                                            <li><a href="{{ route('observations.show', ['observer' => auth()->user()->slug]) }}"
                                                    class="text-gray-300 hover:underline">{{ __('My observations') }}</a>
                                            </li>
                                            <li><a href="{{ route('drawings.show', ['observer' => auth()->user()->slug]) }}"
                                                    class="text-gray-300 hover:underline">{{ __('My drawings') }}</a></li>
                                        @endif
                                    @endauth
                                    @php
                                        // Prepare name and coordinates for external links
                                        $objectName = $session->name ?? null;
                                        $hasCoords =
                                            isset($session->ra) &&
                                            isset($session->decl) &&
                                            !empty($session->ra) &&
                                            !empty($session->decl);
                                        // SIMBAD: prefer name search, otherwise use coordinates (format: %2B12+34+56+%2B12+34+56 not necessary here, use basic coords)
                                        $simbadUrl = null;
                                        $nedUrl = null;
                                        $wikipediaUrl = null;
                                        $aladinUrl = null;

                                        if ($objectName) {
                                            $encName = rawurlencode($objectName);
                                            $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?Ident=$encName";
                                            $nedUrl = "https://ned.ipac.caltech.edu/byname?objname=$encName";
                                            $wikipediaUrl = "https://en.wikipedia.org/wiki/Special:Search?search=$encName";
                                            // Add Aladin Lite pointing to the object name when available
                                            $aladinUrl = "https://aladin.u-strasbg.fr/AladinLite/?target=$encName";
                                        } elseif ($hasCoords) {
                                            // Fallback: point Aladin Lite to coordinates when no name is available
                                            $raParam = rawurlencode($session->ra ?? '');
                                            $decParam = rawurlencode($session->decl ?? '');
                                            if (!empty($raParam) && !empty($decParam)) {
                                                $aladinUrl = "https://aladin.u-strasbg.fr/AladinLite/?ra={$raParam}&dec={$decParam}";
                                            }
                                        }

                                    @endphp

                                    @if ($simbadUrl || $nedUrl || $wikipediaUrl || $aladinUrl)
                                        <li class="pt-2 border-t border-gray-700 text-xs text-gray-400">
                                            {{ __('External databases') }}</li>
                                        @if ($simbadUrl)
                                            <li>
                                                <a href="{{ $simbadUrl }}" target="_blank" rel="noopener noreferrer"
                                                    class="flex items-center gap-2 text-gray-300 hover:text-white">
                                                    <!-- SIMBAD icon (simple star) -->
                                                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg"
                                                        aria-hidden="true">
                                                        <path
                                                            d="M12 2l2.39 4.85L19 8.27l-3.5 3.41L16.18 19 12 16.27 7.82 19l.68-7.32L4.999 8.27l4.61-.42L12 2z"
                                                            fill="currentColor" />
                                                    </svg>
                                                    <span>SIMBAD</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($nedUrl)
                                            <li>
                                                <a href="{{ $nedUrl }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="flex items-center gap-2 text-gray-300 hover:text-white">
                                                    <!-- NED icon (globe) -->
                                                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg"
                                                        aria-hidden="true">
                                                        <path
                                                            d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 2.06v2.04a6.002 6.002 0 013.364 3.364H17A8 8 0 0013 4.06zM6.636 7.48A6.002 6.002 0 0111 4.1V2.06A8 8 0 006.636 7.48zM4.06 11H6.1a6.002 6.002 0 010 2H4.06A8 8 0 004.06 11zM6.636 16.52A8 8 0 0011 21.94v-2.04a6.002 6.002 0 01-4.364-3.38zM13 19.94v-2.04a6.002 6.002 0 01-3.364-3.364H11a8 8 0 002 5.404z"
                                                            fill="currentColor" />
                                                    </svg>
                                                    <span>NED</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($wikipediaUrl)
                                            <li>
                                                <a href="{{ $wikipediaUrl }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="flex items-center gap-2 text-gray-300 hover:text-white">
                                                    <!-- Wikipedia icon (W) -->
                                                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg"
                                                        aria-hidden="true">
                                                        <path
                                                            d="M12 2l2.5 4.9L19 8l-4 3.6L16 19 12 16.2 8 19l1-7.4L5 8l4.5-.9L12 2z"
                                                            fill="currentColor" />
                                                    </svg>
                                                    <span>Wikipedia</span>
                                                </a>
                                            </li>
                                        @endif
                                        @if ($aladinUrl)
                                            <li>
                                                <a href="{{ $aladinUrl }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="flex items-center gap-2 text-gray-300 hover:text-white">
                                                    <!-- Aladin icon (map pin / telescope simplified) -->
                                                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg"
                                                        aria-hidden="true">
                                                        <path
                                                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z"
                                                            fill="currentColor" />
                                                    </svg>
                                                    <span>{{ __('aladin.lite') }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            </div>
                            {{-- Aladin Lite preview --}}
                            @if (isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl))
                                <div class="mt-4 bg-gray-800 p-3 rounded shadow text-gray-100">
                                    <h4 class="font-semibold mb-2 text-white">{{ __('Sky preview') }}</h4>
                                    {{-- Altitude graph (from astronomy library) shown above Aladin preview when available --}}
                                    {{-- altitude graph moved to main display (under Optimum detection magnification) --}}
                                    @php
                                        $dslText = [
                                            'saving' => __('Saving...'),
                                            'save' => __('Save'),
                                            'saved' => __('Saved'),
                                            'save_failed' => __('Save failed'),
                                            'fov_label' => __('FoV'),
                                            'fov_object_size' => __('(object size)'),
                                            'fov_eyepiece' => __('(eyepiece)'),
                                            'fov_instrument' => __('(instrument)'),
                                            'none_label' => __('(none)'),
                                        ];
                                    @endphp
                                    <div id="aladin-lite-container" class="w-full h-64 rounded bg-black"
                                        style="min-height:240px;"
                                        data-aladin="{{ base64_encode(json_encode($aladinDefaults ?? [])) }}"
                                        data-ra="{{ e($session->ra ?? '') }}"
                                        data-dec="{{ e($session->decl ?? '') }}"
                                        data-name="{{ e($session->name ?? '') }}"
                                        data-save-url="{{ url('/api/user/aladin-defaults') }}"
                                        data-dsl-text="{{ base64_encode(json_encode($dslText)) }}"
                                        data-available="{{ base64_encode(json_encode(['instruments' => $availableInstruments ?? [], 'eyepieces' => $availableEyepieces ?? [], 'lenses' => $availableLenses ?? []])) }}"
                                        data-object-id="{{ $session->id ?? '' }}"
                                        data-slug="{{ $session->slug ?? '' }}" {{-- Server-provided initial selections encoded as safe data attributes to avoid inline Blade @json in JS --}}
                                        data-selected-instrument="{{ $selectedInstrumentId ?? '' }}"
                                        data-selected-eyepiece="{{ $selectedEyepieceId ?? '' }}"
                                        data-selected-lens="{{ $selectedLensId ?? '' }}">
                                        {{-- Aladin will render into this container --}}
                                    </div>
                                    <div id="aladin-legend"
                                        class="mt-2 text-sm text-gray-300 flex items-center gap-3">
                                        <div id="aladin-fov-label" class="text-xs text-gray-400">{{ __('FoV:') }}
                                        </div>
                                        <div id="aladin-fov" class="font-medium">—</div>
                                        <div class="text-xs text-gray-400">{{ __('Magnification:') }}</div>
                                        <div id="aladin-mag" class="font-medium">—</div>
                                    </div>
                                    <div class="mt-2">
                                        @auth
                                            <div>
                                                @php $stdSet = auth()->user()?->stdinstrumentset ?? null; @endphp
                                                @livewire('aladin-selects', ['instrument' => $selectedInstrumentId ?? null, 'eyepiece' => $selectedEyepieceId ?? null, 'lens' => $selectedLensId ?? null, 'instrumentSet' => $stdSet, 'objectId' => (string) ($session->id ?? '')])
                                                <input type="hidden" id="aladin-instrument-hidden"
                                                    value="{{ $selectedInstrumentId ?? '' }}" />
                                                <input type="hidden" id="aladin-eyepiece-hidden"
                                                    value="{{ $selectedEyepieceId ?? '' }}" />
                                                <input type="hidden" id="aladin-lens-hidden"
                                                    value="{{ $selectedLensId ?? '' }}" />
                                                <!-- Selected labels removed per UI preference -->
                                                <div class="mt-2">
                                                    <button id="aladin-save-btn" type="button"
                                                        class="inline-flex items-center justify-center text-sm font-medium px-3 py-1.5 rounded-md bg-green-600 text-white hover:bg-green-700 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-400 transition">
                                                        {{ __('Save') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endauth
                                        <!-- selects are rendered by the Livewire AladinSelects component above -->
                                        <script>
                                            (function() {
                                                try {
                                                    // Ensure we have the server-provided id available
                                                    var __embeddedOid = {!! json_encode((string) ($session->id ?? '')) !!} || (window.__dsl_embedded_objectId || null);
                                                    // Listen for the selects emitting dsl-aladin-updated and ensure Livewire receives an enriched payload
                                                    // Prefer Livewire.dispatchTo (v3) to call the specific component method directly. Fall back to the
                                                    // central emitter (__dsl_emitAladinUpdated) which implements queueing/enrichment, or to Livewire.dispatch.
                                                    window.addEventListener('dsl-aladin-updated', function(ev) {
                                                        try {
                                                            var detail = ev && ev.detail ? ev.detail : {};
                                                            // normalize missing/empty objectId using the embedded server-provided id
                                                            if (!detail.objectId || String(detail.objectId).trim() === '') {
                                                                if (__embeddedOid) detail.objectId = __embeddedOid;
                                                            }

                                                            // Prefer direct component invocation when available
                                                            try {
                                                                if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                                                    try {
                                                                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', detail);
                                                                        return;
                                                                    } catch (e) {}
                                                                }
                                                            } catch (e) {}

                                                            // Fallback to centralized emitter (handles queueing/enrichment)
                                                            try {
                                                                if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                                                    window.__dsl_emitAladinUpdated(detail);
                                                                    return;
                                                                }
                                                            } catch (e) {}

                                                            // Older fallback: broadcast Livewire event
                                                            try {
                                                                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                                                    Livewire.dispatch('aladinUpdated', detail);
                                                                    return;
                                                                }
                                                            } catch (e) {}

                                                            // If nothing else is available, do nothing; avoid re-dispatching the same DOM event to prevent loops.
                                                        } catch (e) {}
                                                    }, {
                                                        passive: true
                                                    });
                                                } catch (e) {}
                                            })();
                                        </script>
                                        {{-- One-time server-side initial sync: ensure hidden inputs match server-selected ids immediately on first render. This avoids relying on client heuristics to populate hidden fields. --}}
                                        <script>
                                            (function() {
                                                try {
                                                    // Only run once and only when DOMContentLoaded has already fired or will fire soon
                                                    function runInitSync() {
                                                        try {
                                                            var instHidden = document.getElementById('aladin-instrument-hidden');
                                                            var epHidden = document.getElementById('aladin-eyepiece-hidden');
                                                            var lnHidden = document.getElementById('aladin-lens-hidden');
                                                            // Server-provided values (blade variables) — encoded safely into attributes to avoid inline php printing issues
                                                            try {
                                                                if (typeof window.__dsl_server_selected === 'undefined') {
                                                                    window.__dsl_server_selected = {};
                                                                }
                                                            } catch (e) {}
                                                            try {
                                                                if (typeof window.__dsl_server_selected.instrument === 'undefined') {
                                                                    var _alc_sel = document.getElementById('aladin-lite-container');
                                                                    if (_alc_sel) {
                                                                        window.__dsl_server_selected.instrument = _alc_sel.getAttribute(
                                                                            'data-selected-instrument') || '';
                                                                    } else {
                                                                        window.__dsl_server_selected.instrument = '';
                                                                    }
                                                                }
                                                            } catch (e) {}
                                                            try {
                                                                if (typeof window.__dsl_server_selected.eyepiece === 'undefined') {
                                                                    var _alc_sel2 = document.getElementById('aladin-lite-container');
                                                                    if (_alc_sel2) {
                                                                        window.__dsl_server_selected.eyepiece = _alc_sel2.getAttribute(
                                                                            'data-selected-eyepiece') || '';
                                                                    } else {
                                                                        window.__dsl_server_selected.eyepiece = '';
                                                                    }
                                                                }
                                                            } catch (e) {}
                                                            try {
                                                                if (typeof window.__dsl_server_selected.lens === 'undefined') {
                                                                    var _alc_sel3 = document.getElementById('aladin-lite-container');
                                                                    if (_alc_sel3) {
                                                                        window.__dsl_server_selected.lens = _alc_sel3.getAttribute('data-selected-lens') ||
                                                                            '';
                                                                    } else {
                                                                        window.__dsl_server_selected.lens = '';
                                                                    }
                                                                }
                                                            } catch (e) {}

                                                            if (instHidden && typeof window.__dsl_server_selected.instrument !== 'undefined') {

                                                                // Hide FoV overlay when any dropdown/menu is open (detect common patterns like aria-expanded)
                                                                (function() {
                                                                    try {
                                                                        function isElementVisible(el) {
                                                                            try {
                                                                                return !!(el && el.offsetParent !== null && (el.offsetWidth || el
                                                                                    .offsetHeight));
                                                                            } catch (e) {
                                                                                return false;
                                                                            }
                                                                        }

                                                                        function shouldHideOverlayDueToMenu() {
                                                                            try {
                                                                                // Prefer explicit aria-expanded toggles used by Alpine/Tailwind components
                                                                                var expanded = document.querySelectorAll('[aria-expanded="true"]');
                                                                                for (var i = 0; i < expanded.length; i++) {
                                                                                    try {
                                                                                        var el = expanded[i];
                                                                                        // If the expanded control (or any ancestor) is inside a no-overlay-hide container, ignore it
                                                                                        if (el && typeof el.closest === 'function' && el.closest(
                                                                                                '[data-dsl-no-overlay-hide]')) continue;
                                                                                        // Also ignore if the expanded control controls a popup that itself is inside a no-overlay-hide container
                                                                                        try {
                                                                                            var ariaControls = el.getAttribute ? el.getAttribute(
                                                                                                'aria-controls') : null;
                                                                                            if (ariaControls) {
                                                                                                var ctrl = document.getElementById(ariaControls);
                                                                                                if (ctrl && typeof ctrl.closest === 'function' && ctrl
                                                                                                    .closest('[data-dsl-no-overlay-hide]')) continue;
                                                                                            }
                                                                                        } catch (e) {}
                                                                                    } catch (e) {}
                                                                                    if (isElementVisible(expanded[i])) return true;
                                                                                }
                                                                                // Fallback: common classes used by frameworks for visible dropdowns
                                                                                var selectors = ['.show', '.open', '[data-dropdown-open="true"]'];
                                                                                for (var s = 0; s < selectors.length; s++) {
                                                                                    var els = document.querySelectorAll(selectors[s]);
                                                                                    for (var j = 0; j < els.length; j++) {
                                                                                        try {
                                                                                            var jel = els[j];
                                                                                            // If this element or any of its interactive children live inside a flagged popup, ignore
                                                                                            if (jel && typeof jel.closest === 'function' && jel.closest(
                                                                                                    '[data-dsl-no-overlay-hide]')) continue;
                                                                                            // If element contains any child that is inside the flagged popup, skip
                                                                                            try {
                                                                                                var children = jel.querySelectorAll ? jel
                                                                                                    .querySelectorAll('[data-dsl-no-overlay-hide]') :
                                                                                                    null;
                                                                                                if (children && children.length) continue;
                                                                                            } catch (e) {}
                                                                                        } catch (e) {}
                                                                                        if (isElementVisible(els[j])) return true;
                                                                                    }
                                                                                }
                                                                            } catch (e) {}
                                                                            return false;
                                                                        }

                                                                        function setOverlayHiddenByMenu(hide) {
                                                                            try {
                                                                                var ids = ['aladin-fov-dom', 'aladin-live-fov-badge',
                                                                                    'dsl-aladin-minimal-controls'
                                                                                ];
                                                                                ids.forEach(function(id) {
                                                                                    try {
                                                                                        var el = document.getElementById(id);
                                                                                        if (!el) return;
                                                                                        // remember original inline visibility/pointerEvents once
                                                                                        if (typeof el.__dslOrigVisibility === 'undefined') el
                                                                                            .__dslOrigVisibility = el.style.visibility || '';
                                                                                        if (typeof el.__dslOrigPointer === 'undefined') el
                                                                                            .__dslOrigPointer = el.style.pointerEvents || '';
                                                                                        if (hide) {
                                                                                            el.style.visibility = 'hidden';
                                                                                            el.style.pointerEvents = 'none';
                                                                                            el.__dslHiddenByMenu = true;
                                                                                        } else {
                                                                                            if (el.__dslHiddenByMenu) {
                                                                                                try {
                                                                                                    el.style.visibility = el
                                                                                                        .__dslOrigVisibility || '';
                                                                                                } catch (e) {}
                                                                                                try {
                                                                                                    el.style.pointerEvents = el
                                                                                                        .__dslOrigPointer || '';
                                                                                                } catch (e) {}
                                                                                                el.__dslHiddenByMenu = false;
                                                                                            }
                                                                                        }
                                                                                    } catch (e) {}
                                                                                });
                                                                            } catch (e) {}
                                                                        }

                                                                        var __dslMenuObserverTimer = null;

                                                                        function checkMenuAndUpdateOverlay() {
                                                                            try {
                                                                                var hide = shouldHideOverlayDueToMenu();
                                                                                setOverlayHiddenByMenu(hide);
                                                                            } catch (e) {}
                                                                        }

                                                                        // Observe attribute and subtree changes to detect menus opening/closing.
                                                                        var mo = null;
                                                                        try {
                                                                            mo = new MutationObserver(function() {
                                                                                try {
                                                                                    if (__dslMenuObserverTimer) clearTimeout(
                                                                                        __dslMenuObserverTimer);
                                                                                    __dslMenuObserverTimer = setTimeout(
                                                                                        checkMenuAndUpdateOverlay, 40);
                                                                                } catch (e) {}
                                                                            });
                                                                            mo.observe(document.documentElement || document.body, {
                                                                                attributes: true,
                                                                                subtree: true,
                                                                                attributeFilter: ['aria-expanded', 'class', 'style']
                                                                            });
                                                                        } catch (e) {}

                                                                        // Also run checks on common interactions
                                                                        window.addEventListener('resize', checkMenuAndUpdateOverlay, {
                                                                            passive: true
                                                                        });
                                                                        document.addEventListener('click', function() {
                                                                            setTimeout(checkMenuAndUpdateOverlay, 10);
                                                                        }, true);
                                                                        // initial run
                                                                        try {
                                                                            checkMenuAndUpdateOverlay();
                                                                        } catch (e) {}
                                                                    } catch (e) {}
                                                                })();
                                                                instHidden.value = window.__dsl_server_selected.instrument || '';
                                                            }
                                                            if (epHidden && typeof window.__dsl_server_selected.eyepiece !== 'undefined') {
                                                                epHidden.value = window.__dsl_server_selected.eyepiece || '';
                                                            }
                                                            if (lnHidden && typeof window.__dsl_server_selected.lens !== 'undefined') {
                                                                lnHidden.value = window.__dsl_server_selected.lens || '';
                                                            }
                                                            // If the visible select widget already has a value (async data), ensure
                                                            // hidden inputs match the visible selects on first load. This is a
                                                            // conservative one-time sync only; user interactions remain driven
                                                            // by x-on:selected handlers.
                                                            try {
                                                                try {
                                                                    if (instHidden) {
                                                                        var wrapper = document.querySelector('[data-dsl-field="instrument"]') || (instHidden
                                                                            .parentElement || null);
                                                                        if (wrapper) {
                                                                            var s = wrapper.querySelector('select');
                                                                            if (s && s.value && (!instHidden.value || instHidden.value !== s.value)) {
                                                                                instHidden.value = s.value;
                                                                            }
                                                                        }
                                                                    }
                                                                } catch (e) {}
                                                                try {
                                                                    if (epHidden) {
                                                                        var wrapper2 = document.querySelector('[data-dsl-field="eyepiece"]') || (epHidden
                                                                            .parentElement || null);
                                                                        if (wrapper2) {
                                                                            var s2 = wrapper2.querySelector('select');
                                                                            if (s2 && s2.value && (!epHidden.value || epHidden.value !== s2.value)) {
                                                                                epHidden.value = s2.value;
                                                                            }
                                                                        }
                                                                    }
                                                                } catch (e) {}
                                                                try {
                                                                    if (lnHidden) {
                                                                        var wrapper3 = document.querySelector('[data-dsl-field="lens"]') || (lnHidden
                                                                            .parentElement || null);
                                                                        if (wrapper3) {
                                                                            var s3 = wrapper3.querySelector('select');
                                                                            if (s3 && s3.value && (!lnHidden.value || lnHidden.value !== s3.value)) {
                                                                                lnHidden.value = s3.value;
                                                                            }
                                                                        }
                                                                    }
                                                                } catch (e) {}
                                                            } catch (e) {}
                                                            // Install a capture-level pointerdown listener so real user interactions
                                                            // update a timestamp we use to distinguish init-time events from user events.
                                                            try {
                                                                if (typeof window.__dsl_last_user_interaction_ts === 'undefined') {
                                                                    window.__dsl_last_user_interaction_ts = 0;
                                                                    document.addEventListener('pointerdown', function() {
                                                                        try {
                                                                            window.__dsl_last_user_interaction_ts = Date.now();
                                                                        } catch (e) {}
                                                                    }, {
                                                                        passive: true,
                                                                        capture: true
                                                                    });
                                                                    // also support touchstart for older devices
                                                                    document.addEventListener('touchstart', function() {
                                                                        try {
                                                                            window.__dsl_last_user_interaction_ts = Date.now();
                                                                        } catch (e) {}
                                                                    }, {
                                                                        passive: true,
                                                                        capture: true
                                                                    });
                                                                }
                                                            } catch (e) {}
                                                            try {
                                                                if (typeof updateSelectedLabels === 'function') updateSelectedLabels();
                                                            } catch (e) {}
                                                            try {
                                                                if (typeof scheduleApplyAladinSelectsUpdate === 'function')
                                                                    scheduleApplyAladinSelectsUpdate();
                                                            } catch (e) {}
                                                        } catch (e) {}
                                                    }
                                                    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', runInitSync);
                                                    else runInitSync();
                                                } catch (e) {}
                                            })();
                                        </script>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">
                                        {{ __('Aladin Lite preview (uses default eyepiece/instrument if available)') }}
                                    </div>
                                </div>
                            @endif
                        </aside>
                    </div>
                </div>
        </div>
</x-app-layout>

<!-- Aladin Lite assets (inline so layout stack is not required) -->
<link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css">
<!-- Tom Select removed: using native selects / WireUI styling instead -->

<script>
    (function() {
        // Server-provided values will be read from the aladin container data- attributes
        var aladinDefaults = null;
        var sessionRa = null;
        var sessionDec = null;
        var sessionName = null;
        var DSL_AVAILABLE = {
            instruments: [],
            eyepieces: [],
            lenses: []
        };
        // Global handles so select changes can update the active Aladin instance
        var __dslCurrentAladin = null;
        var __dslCenterRaDeg = null;
        var __dslCenterDecDeg = null;
        var __dslAladinUpdateTimer = null;
        // Debug logging removed for production.
        // parse embedded data attributes early
        try {
            var _alc = document.getElementById('aladin-lite-container');
            if (_alc) {
                try {
                    var dab = _alc.getAttribute('data-available');
                    if (dab) DSL_AVAILABLE = JSON.parse(atob(dab));
                } catch (e) {
                    /* ignore */
                }
                try {
                    var dslb = _alc.getAttribute('data-dsl-text');
                    if (dslb) DSL_TEXT = JSON.parse(atob(dslb));
                } catch (e) {
                    /* ignore */
                }
            }
        } catch (e) {}

        // DSL_TEXT will be parsed from data attribute on the aladin container at init time
        var DSL_TEXT = {};

        // --- Ensure server-provided objectId is available at runtime ---
        try {
            if (typeof window.__dsl_server_selected === 'undefined') window.__dsl_server_selected = {};
        } catch (e) {}
        try {
            var __dsl_server_objectId_from_server = "{{ $session->id ?? '' }}";
            if (typeof window.__dsl_server_selected.objectId === 'undefined') {
                var _alc_obj = document.getElementById('aladin-lite-container');
                if (_alc_obj) {
                    try {
                        window.__dsl_server_selected.objectId = _alc_obj.getAttribute('data-object-id') ||
                            __dsl_server_objectId_from_server || '';
                    } catch (e) {
                        window.__dsl_server_selected.objectId = __dsl_server_objectId_from_server || '';
                    }
                } else {
                    window.__dsl_server_selected.objectId = __dsl_server_objectId_from_server || '';
                }
            }
        } catch (e) {}

        try {
            // Ensure the DOM element has the data-object-id attribute set so client code
            // which reads it will receive a value even if the attribute was initially
            // empty in the rendered HTML.
            var __alc_el = document.getElementById('aladin-lite-container');
            if (__alc_el) {
                var cur = __alc_el.getAttribute('data-object-id');
                if (!cur || cur === '') {
                    try {
                        __alc_el.setAttribute('data-object-id', window.__dsl_server_selected && window
                            .__dsl_server_selected.objectId ? window.__dsl_server_selected.objectId :
                            __dsl_server_objectId_from_server);
                    } catch (e) {}
                }
            }
        } catch (e) {}

        // NOTE: page-level enrichment and Livewire.emit shims were removed here.
        // The centralized emitter and enrichment live in the `aladin-selects` Livewire
        // Blade (window.__dsl_emitAladinUpdated and capture-phase listener). Keep
        // server-provided object id available above; other event normalization is
        // handled by the selects component to avoid duplicate dispatch and races.

        // Utility: try to parse "HH MM SS" RA or decimal into degrees
        function parseRaToDegrees(ra) {
            if (!ra) return null;
            if (!isNaN(Number(ra))) return Number(ra);
            var parts = ra.toString().trim().replace(/:/g, ' ').split(/\s+/);
            if (parts.length === 3) {
                var h = Number(parts[0]);
                var m = Number(parts[1]);
                var s = Number(parts[2]);
                if (!isNaN(h) && !isNaN(m) && !isNaN(s)) {
                    return (h + m / 60 + s / 3600) * 15.0;
                }
            }
            return null;
        }

        // Listen for ephemerides updates dispatched by Livewire's AladinPreviewInfo
        // Also install a lightweight always-forwarding listener that forwards the
        // event to the nearby table regardless of whether ephemerides are present.
        try {
            try {
                /* debug removed */
            } catch (e) {}

            // Always-forward listener: ensures instrument/eyepiece/lens changes
            // are propagated to the nearby table even when ephemerides are not included
            // in the event payload.
            try {
                window.addEventListener('aladin-preview-info-updated', function(ev) {
                    try {
                        var _detail = ev && ev.detail ? ev.detail : {};
                        try {
                            /* debug removed */
                        } catch (e) {}

                        // Attempt to forward to Livewire: prefer emitTo, then Livewire.find fallback
                        try {
                            // Prefer a global broadcast-style emit which delivers an event to
                            // any component listening for it. This avoids calling component
                            // methods directly which can produce MethodNotFound exceptions
                            // if we accidentally target the wrong component instance.
                            if (window.livewire && typeof window.livewire.emit === 'function') {
                                window.livewire.emit('aladinPreviewUpdated', _detail);
                                return;
                            }
                            if (window.Livewire && typeof window.Livewire.emit === 'function') {
                                window.Livewire.emit('aladinPreviewUpdated', _detail);
                                return;
                            }

                            // If emitTo exists prefer targeting the specific component by name
                            if (window.livewire && typeof window.livewire.emitTo === 'function') {
                                window.livewire.emitTo('nearby-objects-table', 'aladinPreviewUpdated',
                                    _detail);
                                return;
                            }
                            if (window.Livewire && typeof window.Livewire.emitTo === 'function') {
                                window.Livewire.emitTo('nearby-objects-table', 'aladinPreviewUpdated',
                                    _detail);
                                return;
                            }

                            // Last resort: attempt to find mounted components and emit the
                            // event on each instance. Do NOT call arbitrary public methods
                            // (comp.call) since that causes the MethodNotFound exception when
                            // the method doesn't exist on the targeted component.
                            // No per-instance fallback: prefer global emit/emitTo only. Livewire
                            // instance methods (comp.emit / comp.call) differ between Livewire
                            // versions and can cause MethodNotFound exceptions when invoked on
                            // unrelated components. Avoid calling them here.
                            if (window.Livewire && typeof window.Livewire.find === 'function') {
                                // Livewire.find is present but we intentionally avoid calling per-instance
                                // methods (comp.emit/comp.call) because that can trigger server-side
                                // MethodNotFound exceptions when the wrong component instance is targeted.
                                // Log available component ids for debugging instead and rely on global
                                // emit / emitTo paths above which are safe across Livewire versions.
                                try {
                                    var els = Array.from(document.querySelectorAll(
                                        '[wire\\:id],[data-wired-id]'));
                                    for (var i = 0; i < els.length; i++) {
                                        try {
                                            var id = els[i].getAttribute('wire:id') || els[i].getAttribute(
                                                'data-wired-id');
                                            if (!id) continue;
                                            // debug log removed
                                        } catch (e) {}
                                    }
                                } catch (e) {}
                            }
                            // debug log removed
                        } catch (e) {
                            /* debug removed */
                        }
                    } catch (e) {}
                });
            } catch (e) {}

            // Global debug listener: always log when the browser receives the
            // dispatched 'aladin-preview-info-updated' event. This helps verify
            // whether Livewire's dispatchBrowserEvent reaches the client even when
            // the more elaborate forwarder code fails to run.
            try {
                window.addEventListener('aladin-preview-info-updated', function(ev) {
                    try {
                        /* debug removed */
                    } catch (e) {}
                });
            } catch (e) {}

            window.addEventListener('aladin-preview-info-updated', function(ev) {
                try {
                    var d = ev && ev.detail ? ev.detail : {};
                    var eph = d.ephemerides || null;
                    if (!eph) return;

                    // Update date
                    var dateCell = document.getElementById('ephem-date-cell');
                    if (dateCell) dateCell.textContent = eph.date || '—';

                    // Forward the Aladin preview update into Livewire so other components (eg. nearby table)
                    // can react to instrument / lens / eyepiece changes. Emit directly from this handler
                    // rather than installing an inner listener to avoid duplicate/malformed client calls.
                    try {
                        var _detail = ev && ev.detail ? ev.detail : {};
                        // Debug: log that we received the browser event and what detail it contains
                        try {
                            /* debug removed */
                        } catch (e) {}

                        // Prefer the modern client API if present (window.livewire), otherwise fall back
                        // to the global Livewire object. Use emitTo to target the specific component.
                        try {
                            if (window.livewire && typeof window.livewire.emit === 'function') {
                                /* debug removed */
                                window.livewire.emit('aladinPreviewUpdated', _detail);
                            } else if (window.Livewire && typeof window.Livewire.emit === 'function') {
                                /* debug removed */
                                window.Livewire.emit('aladinPreviewUpdated', _detail);
                            } else if (window.livewire && typeof window.livewire.emitTo === 'function') {
                                /* debug removed */
                                window.livewire.emitTo('nearby-objects-table', 'aladinPreviewUpdated',
                                    _detail);
                            } else if (window.Livewire && typeof window.Livewire.emitTo === 'function') {
                                /* debug removed */
                                window.Livewire.emitTo('nearby-objects-table', 'aladinPreviewUpdated',
                                    _detail);
                            } else {
                                /* debug removed */
                                // If Livewire.find exists, list component ids for debugging but avoid per-instance calls
                                try {
                                    if (window.Livewire && typeof window.Livewire.find === 'function') {
                                        var _els = Array.from(document.querySelectorAll(
                                            '[wire\\:id],[data-wired-id]'));
                                        for (var _i = 0; _i < _els.length; _i++) {
                                            try {
                                                var _id = _els[_i].getAttribute('wire:id') || _els[_i]
                                                    .getAttribute('data-wired-id');
                                                if (!_id) continue;
                                                // debug removed
                                            } catch (e) {}
                                        }
                                    }
                                } catch (e) {}
                            }
                        } catch (e) {
                            /* debug removed */
                        }
                    } catch (e) {}
                    // Update rise/transit/set
                    var rtsCell = document.getElementById('ephem-rts-cell');
                    if (rtsCell) {
                        var r = eph.rising || '—';
                        var t = eph.transit || '—';
                        var s = eph.setting || '—';
                        // Determine titles for missing rise/set similar to server-side logic
                        var maxh = (typeof eph.max_height_at_night !== 'undefined' && eph
                            .max_height_at_night !== null) ? eph.max_height_at_night : (typeof eph
                            .max_height !== 'undefined' ? eph.max_height : null);
                        var rTitle = '';
                        var sTitle = '';
                        if ((eph.rising === null || typeof eph.rising === 'undefined') && (eph.setting ===
                                null || typeof eph.setting === 'undefined')) {
                            if (maxh !== null && !isNaN(Number(maxh))) {
                                if (Number(maxh) < 0.0) {
                                    rTitle = sTitle =
                                        '{{ addslashes(__('Never rises at your location on this date')) }}';
                                } else {
                                    rTitle = sTitle =
                                        '{{ addslashes(__('Circumpolar — does not set at your location on this date')) }}';
                                }
                            } else {
                                rTitle = sTitle = '{{ addslashes(__('No rise/set data')) }}';
                            }
                        } else {
                            if (eph.rising === null || typeof eph.rising === 'undefined') rTitle =
                                '{{ addslashes(__('Does not rise at your location on this date')) }}';
                            if (eph.setting === null || typeof eph.setting === 'undefined') sTitle =
                                '{{ addslashes(__('Does not set at your location on this date')) }}';
                        }

                        // Build inner HTML with optional title attributes
                        var rSpan = '<span class="font-mono"' + (rTitle ? ' title="' + rTitle + '"' : '') +
                            '>' + r + '</span>';
                        var tSpan = '<span class="font-mono">' + t + '</span>';
                        var sSpan = '<span class="font-mono"' + (sTitle ? ' title="' + sTitle + '"' : '') +
                            '>' + s + '</span>';
                        rtsCell.innerHTML = rSpan + ' <span class="text-gray-400 px-2">/</span> ' + tSpan +
                            ' <span class="text-gray-400 px-2">/</span> ' + sSpan;
                    }

                    // Best time
                    var bestCell = document.getElementById('ephem-best-cell');
                    if (bestCell) bestCell.textContent = eph.best_time || '—';

                    // Max altitude
                    var maxCell = document.getElementById('ephem-max-cell');
                    if (maxCell) {
                        if (typeof eph.max_height_at_night !== 'undefined' && eph.max_height_at_night !==
                            null) maxCell.textContent = eph.max_height_at_night + '°';
                        else if (typeof eph.max_height !== 'undefined' && eph.max_height !== null) maxCell
                            .textContent = eph.max_height + '°';
                        else maxCell.textContent = '—';
                    }

                    // Altitude graph (and year graph below it)
                    try {
                        if (eph.altitude_graph) {
                            var ag = document.getElementById('dsl-altitude-graph');
                            if (!ag) {
                                var alc = document.getElementById('aladin-lite-container');
                                if (alc && alc.parentNode) {
                                    ag = document.createElement('div');
                                    ag.id = 'dsl-altitude-graph';
                                    ag.className = 'mb-3';
                                    alc.parentNode.insertBefore(ag, alc);
                                }
                            }
                            if (ag) ag.innerHTML = eph.altitude_graph;

                            // year graph: show directly under the altitude graph when present
                            try {
                                if (eph.year_graph) {
                                    var yg = document.getElementById('dsl-year-graph');
                                    if (!yg) {
                                        // create container and place it right after altitude graph
                                        yg = document.createElement('div');
                                        yg.id = 'dsl-year-graph';
                                        yg.className = 'mb-3';
                                        if (ag && ag.parentNode) {
                                            if (ag.nextSibling) ag.parentNode.insertBefore(yg, ag
                                                .nextSibling);
                                            else ag.parentNode.appendChild(yg);
                                        } else {
                                            // fallback: insert before the aladin container
                                            var alc2 = document.getElementById('aladin-lite-container');
                                            if (alc2 && alc2.parentNode) alc2.parentNode.insertBefore(yg,
                                                alc2);
                                        }
                                    }
                                    if (yg) yg.innerHTML = eph.year_graph;
                                } else {
                                    // If no year_graph provided, remove any old year graph element
                                    try {
                                        var _old = document.getElementById('dsl-year-graph');
                                        if (_old && _old.parentNode) _old.parentNode.removeChild(_old);
                                    } catch (e) {}
                                }
                            } catch (e) {}
                        }
                    } catch (e) {}

                } catch (e) {}
            }, {
                passive: true
            });
        } catch (e) {}

        // Send a recalc request to the Livewire AladinPreviewInfo component with a date and current selects
        function sendAladinRecalcWithDate(dateStr) {
            try {
                var payload = {
                    objectId: (window.__dsl_server_selected && window.__dsl_server_selected.objectId) || (
                            document.getElementById('aladin-lite-container') && document.getElementById(
                                'aladin-lite-container').getAttribute('data-object-id')) ||
                        '{{ $session->id ?? '' }}',
                    date: dateStr
                };
                try {
                    // include current selects if present
                    var inst = document.getElementById('aladin-instrument-hidden');
                    var ep = document.getElementById('aladin-eyepiece-hidden');
                    var ln = document.getElementById('aladin-lens-hidden');
                    if (inst) payload.instrument = inst.value || null;
                    if (ep) payload.eyepiece = ep.value || null;
                    if (ln) payload.lens = ln.value || null;
                } catch (e) {}

                // Prefer direct dispatchTo to the Livewire component, fall back to dispatch
                try {
                    if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                        return;
                    }
                } catch (e) {}
                try {
                    if (window.Livewire && typeof Livewire.dispatch === 'function') {
                        Livewire.dispatch('aladinUpdated', payload);
                        return;
                    }
                } catch (e) {}
                try {
                    if (typeof window.__dsl_emitAladinUpdated === 'function') {
                        window.__dsl_emitAladinUpdated(payload);
                        return;
                    }
                } catch (e) {}
            } catch (e) {}
        }

        // Hook up the date input: initial call and change events
        try {
            function initEphemDateInput() {
                try {
                    var input = document.getElementById('ephem-date-input');
                    if (!input) return;
                    // initial call with current value
                    sendAladinRecalcWithDate(input.value);
                    // debounce change to avoid rapid calls
                    var tmr = null;
                    input.addEventListener('change', function(ev) {
                        try {
                            if (tmr) clearTimeout(tmr);
                            tmr = setTimeout(function() {
                                sendAladinRecalcWithDate(input.value);
                            }, 150);
                        } catch (e) {}
                    }, {
                        passive: true
                    });
                } catch (e) {}
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',
                initEphemDateInput);
            else initEphemDateInput();
        } catch (e) {}

        function parseDecToDegrees(dec) {
            if (!dec) return null;
            if (!isNaN(Number(dec))) return Number(dec);
            var parts = dec.toString().trim().replace(/:/g, ' ').replace(/\+/g, '').split(/\s+/);
            if (parts.length === 3) {
                var d = Number(parts[0]);
                var m = Number(parts[1]);
                var s = Number(parts[2]);
                if (!isNaN(d) && !isNaN(m) && !isNaN(s)) {
                    var sign = (dec.trim().charAt(0) === '-') ? -1 : 1;
                    return sign * (Math.abs(d) + m / 60 + s / 3600);
                }
            }
            return null;
        }

        // Flag to indicate computeFovDegFromDefaults used object diameter fallback
        var __dslFovUsedObjectDiameter = false;

        function computeFovDegFromDefaults(defaults) {
            __dslFovUsedObjectDiameter = false;
            try {
                if (defaults) {
                    var inst = defaults.instrument || null;
                    var ep = defaults.eyepiece || null;
                    var ln = defaults.lens || null;
                    if (ep && inst && inst.focal_length_mm && ep.apparent_fov_deg) {
                        var mag = inst.fixedMagnification || (inst.focal_length_mm && ep.focal_length_mm ? inst
                            .focal_length_mm / ep.focal_length_mm : null);
                        // Apply lens factor if present (e.g., barlow or reducer stored as factor)
                        try {
                            if (ln && ln.factor) {
                                mag = mag ? (Number(mag) * Number(ln.factor)) : null;
                            }
                        } catch (e) {}
                        if (mag) {
                            return Math.max(0.01, Number(ep.apparent_fov_deg) / mag);
                        }
                    }
                    if (ep && inst && inst.focal_length_mm && ep.focal_length_mm) {
                        var mag2 = inst.focal_length_mm / ep.focal_length_mm;
                        try {
                            if (defaults.lens && defaults.lens.factor) {
                                mag2 = Number(mag2) * Number(defaults.lens.factor);
                            }
                        } catch (e) {}
                        if (mag2 && mag2 > 0) {
                            return Math.max(0.01, 50.0 / mag2);
                        }
                    }
                    // If no instrument/eyepiece defaults available, try using the object's diameter (arcminutes)
                    if (defaults.object_diam_arcmin && !isNaN(Number(defaults.object_diam_arcmin)) && Number(
                            defaults.object_diam_arcmin) > 0) {
                        __dslFovUsedObjectDiameter = true;
                        // convert arcminutes to degrees
                        var deg = Number(defaults.object_diam_arcmin) / 60.0;
                        // ensure a tiny minimum and return
                        return Math.max(0.01, deg);
                    }
                }
            } catch (e) {}
            // Legacy fallback: previously 30' (0.5°). Keep small default of 0.5° when nothing else available.
            return 0.5;
        }

        // Helper: compute and set an Aladin horizontal FOV so the vertical (declination) FOV
        // equals the object's angular size (eyeFovDeg). This uses the container pixel
        // width/height to derive the needed horizontal FOV and applies a small padding.
        function setDisplayFovForVertical(aladinInstance, eyeFovDeg, paddingPx) {
            try {
                if (!aladinInstance || typeof eyeFovDeg !== 'number' || eyeFovDeg <= 0) return;
                paddingPx = typeof paddingPx === 'number' ? paddingPx : 24;
                var container = document.getElementById('aladin-lite-container');
                if (!container) return;
                var cw = container.clientWidth || container.offsetWidth || 300;
                var ch = container.clientHeight || container.offsetHeight || 300;
                if (cw <= 0 || ch <= paddingPx + 4) return;
                // desired horizontal display FOV so that vertical FOV (H * ch / cw) equals
                // the object's angular size after accounting for pixel padding.
                var desiredDisplay = eyeFovDeg * (cw / Math.max(1, (ch - paddingPx)));
                // clamp to reasonable bounds
                desiredDisplay = Math.max(0.01, Math.min(180, desiredDisplay));
                try {
                    if (typeof aladinInstance.setFov === 'function') aladinInstance.setFov(desiredDisplay);
                } catch (e) {
                    /* ignore setFov failures */
                }
            } catch (e) {
                /* ignore */
            }
        }

        // Helper wrapper: call setDisplayFovForVertical multiple times (with delays) to survive
        // Aladin's internal initialization which may overwrite FOV shortly after creation.
        function callSetDisplayFovRepeated(aladinInstance, eyeFovDeg, paddingPx, attempts, delayMs) {
            try {
                attempts = typeof attempts === 'number' ? attempts : 4;
                delayMs = typeof delayMs === 'number' ? delayMs : 250;
                paddingPx = typeof paddingPx === 'number' ? paddingPx : 24;
                // call immediately
                setDisplayFovForVertical(aladinInstance, eyeFovDeg, paddingPx);
                // schedule repeated calls
                for (var i = 1; i < attempts; i++) {
                    (function(idx) {
                        setTimeout(function() {
                            try {
                                setDisplayFovForVertical(aladinInstance, eyeFovDeg, paddingPx);
                            } catch (e) {}
                        }, idx * delayMs);
                    })(i);
                }
            } catch (e) {}
        }

        // Final correction: after Aladin has fully initialized, read its reported FOV
        // and scale the horizontal FOV so the vertical (dec) FOV equals eyeFovDeg.
        function finalAdjustFovToMatchDec(aladinInstance, eyeFovDeg) {
            try {
                if (!aladinInstance || typeof eyeFovDeg !== 'number' || eyeFovDeg <= 0) return;
                setTimeout(function() {
                    try {
                        var f = aladinInstance.getFov && aladinInstance.getFov();
                        if (!f) return;
                        var vals = Array.isArray(f) ? f.map(function(v) {
                            return Number(v);
                        }).filter(function(n) {
                            return !isNaN(n);
                        }) : [Number(f)];
                        if (!vals || vals.length === 0) return;
                        var curH = vals[0];
                        var curV = (vals.length >= 2) ? vals[1] : vals[0];
                        if (!curV || isNaN(curV) || curV <= 0) return;
                        // scale horizontal display so vertical equals eyeFovDeg
                        var desiredDisplay = (eyeFovDeg / curV) * curH;
                        // clamp change to avoid absurd jumps
                        var ratio = desiredDisplay / curH;
                        ratio = Math.max(0.25, Math.min(4.0, ratio));
                        desiredDisplay = curH * ratio;
                        try {
                            if (typeof aladinInstance.setFov === 'function') aladinInstance.setFov(
                                desiredDisplay);
                        } catch (e) {}
                        // one more quick re-check after the change
                        setTimeout(function() {
                            try {
                                var f2 = aladinInstance.getFov && aladinInstance.getFov();

                            } catch (e) {}
                        }, 300);
                    } catch (e) {}
                }, 250);
            } catch (e) {}
        }

        function formatFovLabel(deg) {
            var arcmin = deg * 60;
            return (Math.round(arcmin * 10) / 10) + "' (" + (Math.round(deg * 100) / 100) + "°)";
        }

        // NOTE: previous attempts to globally patch EventTarget.addEventListener to
        // force passive:true for wheel/touch listeners caused runtime errors like
        // "Unable to preventDefault inside passive event listener invocation" when
        // libraries (Aladin/jQuery) expect to call preventDefault(). The global
        // shim has been removed because it changes semantics for third-party
        // libraries and leads to the console errors reported. If needed, a
        // local, safer approach can be applied (attach passive:true only to
        // non-conflicting listeners inside the Aladin container), but that
        // should be implemented by adjusting the library usage or upstream
        // Aladin code rather than a global prototype override.

        // Helper: remove Aladin's built-in controls and inject a minimal control bar
        // with zoom in/out, fullscreen and a 'save/open full Aladin' action. Pass
        // the Aladin instance so the controls can call its API.
        function pruneAladinControls(containerEl, aladinInstance, opts) {
            try {
                if (!containerEl) return;
                opts = opts || {};
                // Keep the Aladin internal DOM intact (do not hide children). We still
                // want to add a minimal, non-invasive control bar for zoom/fullscreen/save.
                var doPrune = false;
                // Determine the main viewport element (canvas/img/background) to keep
                var viewport = null;
                try {
                    // Prefer canvas or img inside container
                    viewport = containerEl.querySelector('canvas, img');
                    if (!viewport) {
                        // fallback: pick the largest child element (likely the sky area)
                        var maxArea = 0;
                        Array.prototype.slice.call(containerEl.children).forEach(function(ch) {
                            try {
                                var r = ch.getBoundingClientRect();
                                var area = (r.width || 0) * (r.height || 0);
                                if (area > maxArea) {
                                    maxArea = area;
                                    viewport = ch;
                                }
                            } catch (e) {}
                        });
                    }
                } catch (e) {
                    viewport = null;
                }

                // Targeted pruning: hide only toolbar-like elements that sit above the viewport
                // Keep the main viewport and our overlays intact so panning and interactions remain functional.
                try {
                    Array.prototype.slice.call(containerEl.children).forEach(function(ch) {
                        try {
                            // preserve our overlays and control container
                            if (!ch) return;
                            if (ch.id === 'aladin-fov-dom' || ch.id === 'dsl-aladin-minimal-controls' || ch
                                .id === 'aladin-live-fov-badge') return;
                            // preserve viewport (canvas/img) and anything that contains or is the viewport
                            if (viewport && (ch === viewport || ch.contains(viewport) || viewport.contains(
                                    ch))) return;
                            // Heuristic: hide elements that are absolutely/fixed positioned or have high z-index
                            var hide = false;
                            try {
                                var cs = window.getComputedStyle(ch);
                                var pos = (cs && cs.position) ? cs.position : '';
                                var z = (cs && cs.zIndex) ? parseInt(cs.zIndex, 10) : 0;
                                if (pos === 'absolute' || pos === 'fixed' || (!isNaN(z) && z >= 10)) hide =
                                    true;
                            } catch (e) {
                                /* ignore */
                            }
                            // Additionally, hide if element contains toolbar-like controls (buttons/inputs) but is not the viewport
                            try {
                                if (!hide && ch.querySelector && (ch.querySelector(
                                        'button, input, select, [role="toolbar"]'))) hide = true;
                            } catch (e) {}
                            if (hide) {
                                try {
                                    // Make the element visually hidden but non-intercepting so pointer events reach the viewport
                                    ch.style.visibility = 'hidden';
                                    ch.style.pointerEvents = 'none';
                                    // keep the element in the flow to avoid layout shifts
                                } catch (e) {}
                            }
                        } catch (e) {}
                    });
                } catch (e) {}

                // Add a minimal control container if not already present. Place it outside the
                // Aladin internal DOM by appending it to the Aladin container's parent (or body)
                // so it never obscures or intercepts events intended for the viewport.
                var ctrlId = 'dsl-aladin-minimal-controls';
                var existing = document.getElementById(ctrlId);
                if (existing) return; // already installed

                var ctrl = document.createElement('div');
                ctrl.id = ctrlId;
                // We'll position the controls absolutely relative to the container's parent
                ctrl.style.position = 'absolute';
                ctrl.style.right = '8px';
                ctrl.style.top = '50%';
                ctrl.style.transform = 'translateY(-50%)';
                ctrl.style.zIndex = 1000; // high so it sits above the preview
                ctrl.style.display = 'flex';
                ctrl.style.flexDirection = 'column';
                ctrl.style.gap = '6px';

                function makeBtn(text, title) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.title = title || text;
                    b.innerText = text;
                    b.style.width = '34px';
                    b.style.height = '34px';
                    b.style.borderRadius = '6px';
                    b.style.border = '1px solid rgba(200,200,200,0.2)';
                    b.style.background = 'rgba(10,10,10,0.6)';
                    b.style.color = 'white';
                    b.style.cursor = 'pointer';
                    b.style.fontSize = '18px';
                    // Ensure the button itself can receive pointer events even if its container is non-intercepting
                    b.style.pointerEvents = 'auto';
                    return b;
                }

                var btnZoomIn = makeBtn('+', 'Zoom in');
                var btnZoomOut = makeBtn('\u2212', 'Zoom out');
                var btnFullscreen = makeBtn('\u21f2', 'Fullscreen');
                var btnSave = makeBtn('\u2b73', 'Open Aladin in new tab');

                // Bind actions with fallbacks depending on available Aladin API
                btnZoomIn.addEventListener('click', function() {
                    try {
                        if (aladinInstance && typeof aladinInstance.getZoom === 'function' &&
                            typeof aladinInstance.setZoom === 'function') {
                            try {
                                aladinInstance.setZoom(aladinInstance.getZoom() + 1);
                                return;
                            } catch (e) {}
                        }
                        if (aladinInstance && typeof aladinInstance.zoomIn === 'function') {
                            aladinInstance.zoomIn();
                            return;
                        }
                        if (aladinInstance && typeof aladinInstance.getFov === 'function' &&
                            typeof aladinInstance.setFov === 'function') {
                            var f = aladinInstance.getFov();
                            if (f && f.length) f = Number(f[0]);
                            else f = Number(f) || 1.0;
                            aladinInstance.setFov(Math.max(0.01, f / 1.4));
                            return;
                        }
                    } catch (e) {
                        console.error('Zoom in error', e);
                    }
                });
                btnZoomOut.addEventListener('click', function() {
                    try {
                        if (aladinInstance && typeof aladinInstance.getZoom === 'function' &&
                            typeof aladinInstance.setZoom === 'function') {
                            try {
                                aladinInstance.setZoom(Math.max(0, aladinInstance.getZoom() - 1));
                                return;
                            } catch (e) {}
                        }
                        if (aladinInstance && typeof aladinInstance.zoomOut === 'function') {
                            aladinInstance.zoomOut();
                            return;
                        }
                        if (aladinInstance && typeof aladinInstance.getFov === 'function' &&
                            typeof aladinInstance.setFov === 'function') {
                            var f = aladinInstance.getFov();
                            if (f && f.length) f = Number(f[0]);
                            else f = Number(f) || 1.0;
                            aladinInstance.setFov(Math.min(180, f * 1.4));
                            return;
                        }
                    } catch (e) {
                        console.error('Zoom out error', e);
                    }
                });
                btnFullscreen.addEventListener('click', function() {
                    try {
                        var el = containerEl;
                        if (!el) return;
                        if (document.fullscreenElement) {
                            document.exitFullscreen();
                        } else if (el.requestFullscreen) {
                            el.requestFullscreen();
                        } else if (el.webkitRequestFullscreen) {
                            el.webkitRequestFullscreen();
                        }
                    } catch (e) {
                        console.error('Fullscreen toggle failed', e);
                    }
                });

                btnSave.addEventListener('click', function() {
                    try {
                        // First: try to find a canvas inside the container and download its data
                        var container = containerEl || document.getElementById('aladin-lite-container');
                        var filenameBase = (sessionName || 'aladin').toString().replace(/[^a-z0-9-_\.]/ig,
                            '_');
                        var filename = filenameBase + '.png';
                        var done = false;
                        try {
                            var cvs = container.querySelector('canvas');
                            if (cvs && typeof cvs.toDataURL === 'function') {
                                try {
                                    var dataUrl = cvs.toDataURL('image/png');
                                    var link = document.createElement('a');
                                    link.href = dataUrl;
                                    link.download = filename;
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                    done = true;
                                    return;
                                } catch (e) {
                                    /* continue to next method */
                                }
                            }
                        } catch (e) {
                            /* continue to next method */
                        }

                        // Second: try to find an image element inside the container and fetch it
                        try {
                            var img = container.querySelector('img');
                            if (img && img.src) {
                                var src = img.src;
                                if (src.indexOf('data:') === 0) {
                                    try {
                                        var link2 = document.createElement('a');
                                        link2.href = src;
                                        link2.download = filename;
                                        document.body.appendChild(link2);
                                        link2.click();
                                        document.body.removeChild(link2);
                                        done = true;
                                        return;
                                    } catch (e) {
                                        /* fallback to fetch below */
                                    }
                                }
                                // Otherwise fetch the resource as blob then download. 
                                fetch(src, {
                                    mode: 'cors'
                                }).then(function(resp) {
                                    if (!resp.ok) throw new Error('Fetch failed');
                                    return resp.blob();
                                }).then(function(blob) {
                                    var url = URL.createObjectURL(blob);
                                    var l = document.createElement('a');
                                    l.href = url;
                                    l.download = filename;
                                    document.body.appendChild(l);
                                    l.click();
                                    document.body.removeChild(l);
                                    setTimeout(function() {
                                        URL.revokeObjectURL(url);
                                    }, 2000);
                                    return;
                                }).catch(function(err) {
                                    // fallback to opening full aladin
                                    try {
                                        openFullAladin();
                                    } catch (e) {}
                                });
                                return;
                            }
                        } catch (e) {
                            /* continue */
                        }

                        // Final fallback: open full Aladin page for manual save
                        function openFullAladin() {
                            var targetUrl = null;
                            if (sessionName) {
                                targetUrl = 'https://aladin.u-strasbg.fr/AladinLite/?target=' +
                                    encodeURIComponent(sessionName);
                            } else if (typeof centerRaDeg !== 'undefined' && centerRaDeg !== null &&
                                typeof centerDecDeg !== 'undefined' && centerDecDeg !== null) {
                                targetUrl = 'https://aladin.u-strasbg.fr/AladinLite/?target=' +
                                    encodeURIComponent((centerRaDeg / 15).toString() + ' ' + centerDecDeg
                                        .toString());
                            } else {
                                targetUrl = 'https://aladin.u-strasbg.fr/AladinLite/';
                            }
                            window.open(targetUrl, '_blank');
                        }
                        openFullAladin();
                    } catch (e) {
                        console.error('Save/Open Aladin failed', e);
                    }
                });

                // Append buttons in a minimal, non-invasive control container
                ctrl.appendChild(btnZoomIn);
                ctrl.appendChild(btnZoomOut);
                ctrl.appendChild(btnFullscreen);
                ctrl.appendChild(btnSave);

                // Place the control container outside the Aladin internal DOM. We'll append to
                // the container's parent so the controls can overlay the preview without being
                // children of the Aladin root (avoids intercepting internal pointer handlers).
                try {
                    var parentForControls = (containerEl && containerEl.parentElement) ? containerEl.parentElement :
                        document.body;
                    // Ensure parent is positioned so absolutely positioned controls align
                    parentForControls.style.position = parentForControls.style.position || 'relative';
                    // Style the control container to be transparent and small
                    try {
                        ctrl.style.background = 'transparent';
                        ctrl.style.padding = '4px';
                        ctrl.style.borderRadius = '6px';
                    } catch (e) {}
                    // Temporarily set pointer-events to none on the container; we'll enable it on buttons
                    try {
                        ctrl.style.pointerEvents = 'none';
                    } catch (e) {}
                    // Append to parent (outside Aladin internals)
                    parentForControls.appendChild(ctrl);
                    // Position the control container to visually overlay the Aladin container
                    try {
                        var alcRect = containerEl.getBoundingClientRect();
                        var parentRect = parentForControls.getBoundingClientRect();
                        // Compute offset relative to parent
                        var offsetTop = alcRect.top - parentRect.top;
                        var offsetLeft = alcRect.left - parentRect.left;
                        // Place controls near the right center of the Aladin container
                        ctrl.style.position = 'absolute';
                        ctrl.style.left = (offsetLeft + alcRect.width - 48) + 'px';
                        ctrl.style.top = (offsetTop + (alcRect.height / 2) - 34) + 'px';
                        // Ensure buttons receive pointer events
                        Array.prototype.slice.call(ctrl.querySelectorAll('button')).forEach(function(b) {
                            try {
                                b.style.pointerEvents = 'auto';
                            } catch (e) {}
                        });
                    } catch (e) {
                        // fallback: rely on css positioning already set
                        try {
                            Array.prototype.slice.call(ctrl.querySelectorAll('button')).forEach(function(b) {
                                try {
                                    b.style.pointerEvents = 'auto';
                                } catch (e) {}
                            });
                        } catch (e) {}
                    }
                } catch (e) {}
                // Ensure only the viewport element inside the Aladin container can receive pointer events.
                // This makes sure any remaining overlays or UI elements won't intercept drag/pan.
                try {
                    Array.prototype.slice.call(containerEl.children).forEach(function(ch) {
                        try {
                            if (!ch) return;
                            // Allow the viewport element itself to keep pointer events
                            if (viewport && (ch === viewport || ch.contains(viewport) || viewport.contains(
                                    ch))) {
                                try {
                                    ch.style.pointerEvents = 'auto';
                                } catch (e) {}
                                return;
                            }
                            // Keep our DOM overlay non-intercepting
                            if (ch.id === 'aladin-fov-dom' || ch.id === 'aladin-live-fov-badge') {
                                try {
                                    ch.style.pointerEvents = 'none';
                                } catch (e) {}
                                return;
                            }
                            // Disable pointer events on any other child so pointer interactions reach the viewport
                            try {
                                ch.style.pointerEvents = 'none';
                            } catch (e) {}
                        } catch (e) {}
                    });
                } catch (e) {}
            } catch (e) {
                console.error('pruneAladinControls error', e);
            }
        }

        function initAladin() {
            var container = document.getElementById('aladin-lite-container');
            if (!container) return;

            // Parse localized DSL_TEXT from container to avoid Blade inside JS literals
            try {
                var raw = container.getAttribute('data-dsl-text');
                if (raw) {
                    DSL_TEXT = JSON.parse(atob(raw));
                }
            } catch (e) {
                DSL_TEXT = {};
            }

            try {
                var raw = container.getAttribute('data-aladin');
                if (raw) {
                    var decoded = atob(raw);
                    aladinDefaults = JSON.parse(decoded || '{}');
                }
            } catch (e) {
                aladinDefaults = null;
            }
            sessionRa = container.getAttribute('data-ra') || null;
            sessionDec = container.getAttribute('data-dec') || null;
            sessionName = container.getAttribute('data-name') || null;

            // Merge any hidden select values into aladinDefaults so initial FOV reflects them
            try {
                // If hidden inputs are empty but server-provided aladinDefaults contains
                // instrument/eyepiece/lens hints (without ids), try to find matching
                // available items and populate the hidden inputs so selects show them.
                try {
                    var instHiddenEl = document.getElementById('aladin-instrument-hidden');
                    var epHiddenEl = document.getElementById('aladin-eyepiece-hidden');
                    var lnHiddenEl = document.getElementById('aladin-lens-hidden');
                    if (aladinDefaults && typeof DSL_AVAILABLE !== 'undefined') {
                        try {
                            if (aladinDefaults.instrument && !aladinDefaults.instrument.id && Array.isArray(
                                    DSL_AVAILABLE.instruments)) {
                                var match = DSL_AVAILABLE.instruments.find(function(i) {
                                    try {
                                        var f1 = Number(i.focal_length_mm || i.focal_length_mm_mm || 0);
                                        var f2 = Number(aladinDefaults.instrument.focal_length_mm || 0);
                                        var a1 = Number(i.aperture_mm || 0);
                                        var a2 = Number(aladinDefaults.instrument.aperture_mm || 0);
                                        if (f2 && Math.abs(f1 - f2) <= 1) return true;
                                        if (a2 && Math.abs(a1 - a2) <= 1) return true;
                                    } catch (e) {}
                                    return false;
                                });
                                if (match && instHiddenEl) {
                                    instHiddenEl.value = match.id;
                                }
                            }
                            if (aladinDefaults.eyepiece && !aladinDefaults.eyepiece.id && Array.isArray(
                                    DSL_AVAILABLE.eyepieces)) {
                                var matchEp = DSL_AVAILABLE.eyepieces.find(function(e) {
                                    try {
                                        var ef = Number(e.focal_length_mm || 0);
                                        var rf = Number(aladinDefaults.eyepiece.focal_length_mm || 0);
                                        var ap = Number(e.apparent_fov_deg || 0);
                                        var rap = Number(aladinDefaults.eyepiece.apparent_fov_deg || 0);
                                        if (rf && Math.abs(ef - rf) <= 0.5) return true;
                                        if (rap && Math.abs(ap - rap) <= 0.5) return true;
                                    } catch (e) {}
                                    return false;
                                });
                                if (matchEp && epHiddenEl) {
                                    epHiddenEl.value = matchEp.id;
                                }
                            }
                            if (aladinDefaults.lens && !aladinDefaults.lens.id && Array.isArray(DSL_AVAILABLE
                                    .lenses)) {
                                var matchLn = DSL_AVAILABLE.lenses.find(function(l) {
                                    try {
                                        return Number(l.factor || 0) === Number(aladinDefaults.lens
                                            .factor || 0);
                                    } catch (e) {
                                        return false;
                                    }
                                });
                                if (matchLn && lnHiddenEl) {
                                    lnHiddenEl.value = matchLn.id;
                                }
                            }
                        } catch (e) {
                            /* ignore match errors */
                        }
                    }
                } catch (e) {}
                readSelectsIntoDefaults();
            } catch (e) {}
            var centerRaDeg = null;
            var centerDecDeg = null;
            if (aladinDefaults && aladinDefaults.ra_deg && aladinDefaults.dec_deg) {
                centerRaDeg = aladinDefaults.ra_deg;
                centerDecDeg = aladinDefaults.dec_deg;
            }
            try {
                function parseBool(v) {
                    if (typeof v === 'undefined' || v === null || v === '') return false;
                    try {
                        var s = String(v).toLowerCase().trim();
                        return (s === '1' || s === 'true' || s === 'yes' || s === 'on');
                    } catch (e) {
                        return false;
                    }
                }
                try {
                    var selInst = null;
                    // aladinDefaults.instrument may be an object with id, or aladinDefaults may be null
                    if (aladinDefaults && aladinDefaults.instrument && (aladinDefaults.instrument.id ||
                            aladinDefaults.instrument.id === 0)) {
                        var instId = aladinDefaults.instrument.id;
                        if (DSL_AVAILABLE && Array.isArray(DSL_AVAILABLE.instruments)) {
                            selInst = DSL_AVAILABLE.instruments.find(function(i) {
                                return String(i.id) === String(instId);
                            }) || null;
                        }
                    }
                    // Also allow for a directly selected instrument id from hidden input
                    if (!selInst) {
                        var hid = document.getElementById('aladin-instrument-hidden');
                        if (hid && hid.value) {
                            var hidId = hid.value;
                            if (DSL_AVAILABLE && Array.isArray(DSL_AVAILABLE.instruments)) {
                                selInst = DSL_AVAILABLE.instruments.find(function(i) {
                                    return String(i.id) === String(hidId);
                                }) || null;
                            }
                        }
                    }
                } catch (e) {
                    selInst = null;
                }


                // Retries a few times if no canvas/iframe is present yet (Aladin may create it asynchronously)
                // Helper: sample the center pixel of a canvas when readable. Returns [r,g,b,a] or null.
                function dslSampleCanvasCenter(canvasEl) {
                    try {
                        if (!canvasEl) return null;
                        try {
                            var ctx2d = canvasEl.getContext('2d');
                        } catch (e) {
                            ctx2d = null;
                        }
                        if (ctx2d && typeof ctx2d.getImageData === 'function') {
                            var cx = Math.max(0, Math.floor((canvasEl.width || canvasEl.clientWidth || 1) / 2));
                            var cy = Math.max(0, Math.floor((canvasEl.height || canvasEl.clientHeight || 1) / 2));
                            try {
                                var d = ctx2d.getImageData(cx, cy, 1, 1).data;
                                return [d[0], d[1], d[2], d[3]];
                            } catch (e) {
                                return null;
                            }
                        }
                        try {
                            var gl = canvasEl.getContext('webgl') || canvasEl.getContext('experimental-webgl') ||
                                canvasEl.getContext('webgl2');
                            if (gl && typeof gl.readPixels === 'function') {
                                var w = canvasEl.width || canvasEl.clientWidth || 1;
                                var h = canvasEl.height || canvasEl.clientHeight || 1;
                                var x = Math.max(0, Math.floor(w / 2));
                                var y = Math.max(0, Math.floor(h / 2));
                                var buf = new Uint8Array(4);
                                try {
                                    gl.readPixels(x, h - y - 1, 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, buf);
                                    return [buf[0], buf[1], buf[2], buf[3]];
                                } catch (e) {
                                    return null;
                                }
                            }
                        } catch (e) {}
                        return null;
                    } catch (e) {
                        return null;
                    }
                }

                try {
                    // MutationObserver for hidden input value changes
                    var hidInst = document.getElementById('aladin-instrument-hidden');
                    if (hidInst) {
                        mo.observe(hidInst, {
                            attributes: true
                        });
                    }

                    // Also listen for change events on any select within the instrument wrapper
                    var wrapper = document.querySelector('[data-dsl-field="instrument"]') || null;

                    // Prefer attaching to Aladin's event hooks so we react only when Aladin
                    // reports activity (render/zoom/move). This is more reliable than
                    // DOM-only watching because Aladin knows when its internal rendering
                    // cycle finished. We still keep a DOM-stability fallback when no
                    // Aladin event API is available.
                    try {
                        function computeCanvasSignature(root) {
                            try {
                                var canvases = Array.prototype.slice.call((root.querySelectorAll ? root
                                    .querySelectorAll('canvas') : []));
                                var imgs = Array.prototype.slice.call((root.querySelectorAll ? root
                                    .querySelectorAll('iframe, img, [role="img"]') : []));
                                var items = canvases.concat(imgs);
                                var sigParts = items.map(function(el) {
                                    try {
                                        if (!el || el.nodeType !== 1) return '';
                                        if (el.hasAttribute && el.hasAttribute('data-dsl-overlay-for'))
                                            return '';
                                        var id = el.id || el.getAttribute('data-dsl-canvas-id') || '';
                                        var w = (el.width || el.clientWidth || el.offsetWidth || 0);
                                        var h = (el.height || el.clientHeight || el.offsetHeight || 0);
                                        var style = '';
                                        try {
                                            style = (window.getComputedStyle && window.getComputedStyle(el)
                                                .transform) || (el.style && el.style.transform) || '';
                                        } catch (e) {
                                            style = (el.style && el.style.transform) || '';
                                        }
                                        return [(el.tagName || '').toLowerCase(), id, w, h, style].join(
                                            '|');
                                    } catch (e) {
                                        return '';
                                    }
                                }).filter(function(s) {
                                    return !!s;
                                });
                                sigParts.sort();
                                return sigParts.join('::');
                            } catch (e) {
                                return '';
                            }
                        }

                        function monitorAladinDomStability(root, onStable, opts) {
                            opts = opts || {};
                            var stableMs = typeof opts.stableMs === 'number' ? opts.stableMs : 180;
                            var maxWaitMs = typeof opts.maxWaitMs === 'number' ? opts.maxWaitMs : 3000;
                            var lastSig = computeCanvasSignature(root);
                            var lastChangeTs = Date.now();
                            var timeoutId = null;
                            var startTs = Date.now();

                            function scheduleCheck() {
                                if (timeoutId) clearTimeout(timeoutId);
                                timeoutId = setTimeout(function() {
                                    try {
                                        var sig = computeCanvasSignature(root);
                                        if (sig === lastSig && (Date.now() - lastChangeTs) >= stableMs) {
                                            cleanup();
                                            try {
                                                onStable();
                                            } catch (e) {}
                                            return;
                                        }
                                        lastSig = sig;
                                        lastChangeTs = Date.now();
                                        if ((Date.now() - startTs) >= maxWaitMs) {
                                            cleanup();
                                            try {
                                                onStable();
                                            } catch (e) {}
                                            return;
                                        }
                                        scheduleCheck();
                                    } catch (e) {
                                        cleanup();
                                        try {
                                            onStable();
                                        } catch (_) {}
                                    }
                                }, stableMs + 20);
                            }

                            var mo = new MutationObserver(function(muts) {
                                try {
                                    var sawRelevant = false;
                                    muts.forEach(function(m) {
                                        try {
                                            var tgt = m.target || null;
                                            if (tgt && tgt.nodeType === 1) {
                                                if (tgt.hasAttribute && tgt.hasAttribute(
                                                        'data-dsl-overlay-for')) return;
                                                try {
                                                    if (tgt.closest && tgt.closest(
                                                            '[data-dsl-overlay-for]')) return;
                                                } catch (e) {}
                                            }
                                            if (m.type === 'childList') {
                                                Array.prototype.forEach.call(m.addedNodes || [],
                                                    function(n) {
                                                        try {
                                                            if (n && n.nodeType === 1) {
                                                                var tag = (n.tagName || '')
                                                                    .toLowerCase();
                                                                if (tag === 'canvas' || tag ===
                                                                    'iframe' || tag === 'img' ||
                                                                    (n.querySelector && n
                                                                        .querySelector('canvas')
                                                                    )) sawRelevant = true;
                                                            }
                                                        } catch (e) {}
                                                    });
                                                Array.prototype.forEach.call(m.removedNodes || [],
                                                    function(n) {
                                                        try {
                                                            if (n && n.nodeType === 1 && (n
                                                                    .tagName || '')
                                                                .toLowerCase() === 'canvas')
                                                                sawRelevant = true;
                                                        } catch (e) {}
                                                    });
                                            } else if (m.type === 'attributes') {
                                                try {
                                                    var mt = m.target;
                                                    if (mt && mt.nodeType === 1) {
                                                        var tag = (mt.tagName || '').toLowerCase();
                                                        if (tag === 'canvas' || tag === 'img' ||
                                                            tag === 'iframe') sawRelevant = true;
                                                    }
                                                } catch (e) {}
                                            }
                                        } catch (e) {}
                                    });
                                    if (sawRelevant) {
                                        lastSig = computeCanvasSignature(root);
                                        lastChangeTs = Date.now();
                                        scheduleCheck();
                                    }
                                } catch (e) {}
                            });

                            try {
                                mo.observe(root, {
                                    childList: true,
                                    subtree: true,
                                    attributes: true,
                                    attributeFilter: ['style', 'class']
                                });
                            } catch (e) {
                                setTimeout(function() {
                                    try {
                                        onStable();
                                    } catch (e) {}
                                }, 300);
                            }

                            function cleanup() {
                                try {
                                    if (mo) mo.disconnect();
                                } catch (e) {}
                                try {
                                    if (timeoutId) clearTimeout(timeoutId);
                                } catch (e) {}
                            }

                            return {
                                disconnect: cleanup
                            };
                        }

                        function attachAladinEventWatcher(root, aladinInstance, onStable) {
                            try {
                                if (!aladinInstance) {
                                    // No instance yet; use DOM fallback
                                    return monitorAladinDomStability(root, onStable, {
                                        stableMs: 180,
                                        maxWaitMs: 3000
                                    });
                                }

                                // Debounce events so rapid internal updates coalesce into a single final call
                                var debounceTimer = null;

                                function scheduleStableCall() {
                                    try {
                                        if (debounceTimer) clearTimeout(debounceTimer);
                                        debounceTimer = setTimeout(function() {
                                            debounceTimer = null;
                                            try {
                                                onStable();
                                            } catch (e) {}
                                        }, 160);
                                    } catch (e) {
                                        try {
                                            onStable();
                                        } catch (e) {}
                                    }
                                }

                                var attached = [];
                                // common Aladin event names (best-effort); many builds expose .on or addListener
                                var events = ['redraw', 'render', 'zoom', 'move', 'viewChanged', 'positionChanged',
                                    'update', 'change'
                                ];
                                var hooked = false;
                                try {
                                    if (typeof aladinInstance.on === 'function') {
                                        events.forEach(function(ev) {
                                            try {
                                                aladinInstance.on(ev, scheduleStableCall);
                                                attached.push({
                                                    api: 'on',
                                                    ev: ev
                                                });
                                                hooked = true;
                                            } catch (e) {}
                                        });
                                    }
                                } catch (e) {}
                                try {
                                    if (!hooked && typeof aladinInstance.addListener === 'function') {
                                        events.forEach(function(ev) {
                                            try {
                                                aladinInstance.addListener(ev, scheduleStableCall);
                                                attached.push({
                                                    api: 'addListener',
                                                    ev: ev
                                                });
                                                hooked = true;
                                            } catch (e) {}
                                        });
                                    }
                                } catch (e) {}
                                try {
                                    if (!hooked && typeof aladinInstance.addEventListener === 'function') {
                                        events.forEach(function(ev) {
                                            try {
                                                aladinInstance.addEventListener(ev, scheduleStableCall);
                                                attached.push({
                                                    api: 'addEventListener',
                                                    ev: ev
                                                });
                                                hooked = true;
                                            } catch (e) {}
                                        });
                                    }
                                } catch (e) {}

                                if (hooked) {
                                    // Return a small object that can be used to detach listeners if needed
                                    return {
                                        disconnect: function() {
                                            try {
                                                attached.forEach(function(a) {
                                                    try {
                                                        if (a.api === 'on' && typeof aladinInstance
                                                            .off === 'function') aladinInstance.off(a
                                                            .ev, scheduleStableCall);
                                                        else if (a.api === 'addListener' &&
                                                            typeof aladinInstance.removeListener ===
                                                            'function') aladinInstance.removeListener(a
                                                            .ev, scheduleStableCall);
                                                        else if (a.api === 'addEventListener' &&
                                                            typeof aladinInstance
                                                            .removeEventListener === 'function')
                                                            aladinInstance.removeEventListener(a.ev,
                                                                scheduleStableCall);
                                                    } catch (e) {}
                                                });
                                            } catch (e) {}
                                        }
                                    };
                                }

                                // If we couldn't hook into Aladin's event system, fall back to DOM-stability watcher
                                return monitorAladinDomStability(root, onStable, {
                                    stableMs: 180,
                                    maxWaitMs: 3000
                                });
                            } catch (e) {
                                try {
                                    return monitorAladinDomStability(root, onStable, {
                                        stableMs: 180,
                                        maxWaitMs: 3000
                                    });
                                } catch (e) {
                                    return {
                                        disconnect: function() {}
                                    };
                                }
                            }
                        }

                        // Install event-driven watcher on container/aladin when available
                        try {
                            if (container.__dslAladinEventMonitor && typeof container.__dslAladinEventMonitor
                                .disconnect === 'function') {
                                try {
                                    container.__dslAladinEventMonitor.disconnect();
                                } catch (e) {}
                                container.__dslAladinEventMonitor = null;
                            }
                            // prefer hooking to the global current Aladin instance if present
                            var targetAladin = (typeof __dslCurrentAladin !== 'undefined' && __dslCurrentAladin) ?
                                __dslCurrentAladin : null;
                            container.__dslAladinEventMonitor = attachAladinEventWatcher(container, targetAladin,
                                function() {
                                    try {
                                        if (typeof __dslCurrentAladin !== 'undefined' && __dslCurrentAladin) {
                                            updateDomFovOverlay(__dslCurrentAladin, computeFovDegFromDefaults(
                                                aladinDefaults));
                                        }
                                    } catch (e) {}
                                });
                        } catch (e) {}
                    } catch (e) {}
                } catch (e) {
                    /* non-fatal */
                }
            } catch (e) {}
            // expose center to global so dropdown updates can re-center overlays
            __dslCenterRaDeg = centerRaDeg;
            __dslCenterDecDeg = centerDecDeg;
            if (!centerRaDeg && sessionRa) {
                centerRaDeg = parseRaToDegrees(sessionRa);
            }
            if (!centerDecDeg && sessionDec) {
                centerDecDeg = parseDecToDegrees(sessionDec);
            }

            // Force a quick sync from visible select widgets (TomSelect/native) into the
            // server-hidden inputs so aladinDefaults reflects the user's current selection
            // (useful when Livewire/x-select populated the visible control but the hidden
            // inputs were left empty on server render). This avoids computing FoV from
            // stale defaults.
            try {
                ['instrument', 'eyepiece', 'lens'].forEach(function(k) {
                    try {
                        var hidden = document.getElementById('aladin-' + k + '-hidden');
                        if (!hidden) return;
                        var wrapper = document.querySelector('[data-dsl-field="' + k + '"]') || hidden
                            .parentElement || null;
                        var sel = wrapper ? wrapper.querySelector('select') : null;
                        var v = '';
                        if (sel) {
                            try {
                                if (sel.tom && typeof sel.tom.getValue === 'function') v = sel.tom
                                    .getValue();
                            } catch (e) {}
                            if (!v) v = sel.value || (sel.dataset && sel.dataset.tsValue) || '';
                        }
                        if (!v && wrapper) {
                            var control = wrapper.querySelector(
                                'input[type="text"], [role="combobox"], input');
                            if (control) try {
                                v = control.value || '';
                            } catch (e) {}
                        }
                        if (v && hidden.value !== v) hidden.value = v;
                    } catch (e) {}
                });
            } catch (e) {}

            var fovUsedDeg = computeFovDegFromDefaults(aladinDefaults);
            // Compute a slightly larger default display FoV so the full FoV circle is visible
            var displayFovDeg = Math.max(fovUsedDeg * 1.6, fovUsedDeg + 0.5);
            // If the object is small, try to reduce the display FOV so the FoV circle just fits the preview.
            // We read the container size and compute a displayFov that will make the object's diameter
            // (eyeFovDeg) fill the preview area minus a small pixel padding. This yields a tighter zoom
            // for small objects so the circular overlay is clearly visible and nearly fills the frame.
            try {
                var tmpContainer = document.getElementById('aladin-lite-container');
                if (tmpContainer && fovUsedDeg > 0) {
                    var cw = tmpContainer.clientWidth || tmpContainer.offsetWidth || 300;
                    var ch = tmpContainer.clientHeight || tmpContainer.offsetHeight || 300;
                    // Use container height so the declination (vertical) FOV matches the object's size.
                    // padding in pixels to leave a small margin around the circle
                    var paddingPx = 24;
                    // Only apply this tighter-fit logic for reasonably small objects (avoid extreme behavior for wide fields)
                    if (ch > paddingPx + 10 && fovUsedDeg < 2.5 && cw > 0) {
                        // We want the declination (vertical) FOV reported by Aladin to equal the object's angular size.
                        // Aladin's "fov" parameter sets the horizontal/RA FOV; the vertical/Dec FOV becomes
                        // displayFovDeg * (containerHeight / containerWidth). To make Dec FOV equal eyeFovDeg
                        // we solve for displayFovDeg:
                        //   decFov = displayFovDeg * (ch / cw)
                        //   set decFov = eyeFovDeg * (ch / (ch - paddingPx))  // allow pixel padding
                        // => displayFovDeg = decFov * (cw / ch) = eyeFovDeg * (cw / (ch - paddingPx))
                        var displayFit = fovUsedDeg * (cw / Math.max(1, (ch - paddingPx)));
                        // If displayFit is smaller (tighter) than our default displayFovDeg, use it so the object fills
                        // the preview vertically. Never choose a value smaller than the object FoV itself.
                        displayFovDeg = Math.max(0.01, Math.max(displayFovDeg, Math.max(displayFit, fovUsedDeg +
                            0.01)));
                    }
                }
            } catch (e) {
                /* non-fatal; keep displayFovDeg as computed above */
            }
            var magUsed = null;
            try {
                if (aladinDefaults) {
                    var inst = aladinDefaults.instrument || null;
                    var ep = aladinDefaults.eyepiece || null;
                    if (inst && ep) {
                        magUsed = inst.fixedMagnification ? Number(inst.fixedMagnification) : (inst
                            .focal_length_mm && ep.focal_length_mm ? Number(inst.focal_length_mm) / Number(ep
                                .focal_length_mm) : null);
                        if (magUsed && ep.apparent_fov_deg) {
                            fovUsedDeg = Math.max(0.01, Number(ep.apparent_fov_deg) / magUsed);
                        } else if (magUsed) {
                            fovUsedDeg = Math.max(0.01, 50.0 / magUsed);
                        }
                    }
                }
            } catch (e) {
                magUsed = null;
            }

            // Always update legend if present; show both the used/source FoV and the live Aladin FoV when available
            var fovEl = document.getElementById('aladin-fov');
            var magEl = document.getElementById('aladin-mag');
            var fovLabelEl = document.getElementById('aladin-fov-label');
            var baseLabel = (DSL_TEXT && DSL_TEXT.fov_label) ? DSL_TEXT.fov_label : 'FoV';
            var sourceSuffix = '';
            try {
                if (typeof __dslFovUsedObjectDiameter !== 'undefined' && __dslFovUsedObjectDiameter) {
                    sourceSuffix = DSL_TEXT.fov_object_size || '(object size)';
                } else if (aladinDefaults && aladinDefaults.eyepiece && aladinDefaults.instrument) {
                    sourceSuffix = DSL_TEXT.fov_eyepiece || '(eyepiece)';
                } else if (aladinDefaults && aladinDefaults.instrument) {
                    sourceSuffix = DSL_TEXT.fov_instrument || '(instrument)';
                }
            } catch (e) {
                sourceSuffix = '';
            }
            if (fovLabelEl) {
                fovLabelEl.textContent = baseLabel + (sourceSuffix ? ' ' + sourceSuffix : '') + ':';
            }
            if (fovEl) {
                fovEl.textContent = (typeof fovUsedDeg === 'number' ? formatFovLabel(fovUsedDeg) : '—');
            }
            if (magEl) {
                magEl.textContent = magUsed ? Math.round(magUsed) + 'x' : '—';
            }
            // Helper: wait for Aladin to be available (A.aladin) before calling it
            function waitForAladinAndRun(cb, attemptsLeft) {
                attemptsLeft = typeof attemptsLeft === 'number' ? attemptsLeft : 20;
                if (window.A && typeof window.A.aladin === 'function') {
                    try {
                        cb(window.A);
                    } catch (e) {
                        console.error('Aladin callback error', e);
                    }
                    return;
                }
                if (attemptsLeft <= 0) {
                    console.error('Aladin did not become available in time.');
                    return;
                }
                // poll after a short delay
                setTimeout(function() {
                    waitForAladinAndRun(cb, attemptsLeft - 1);
                }, 200);
            }

            waitForAladinAndRun(function(Alib) {

                // Helper: update the DOM FoV overlay size based on current Aladin fov
                function updateDomFovOverlay(aladinInstance, eyeFovDeg) {
                    try {
                        var containerEl = document.getElementById('aladin-lite-container');
                        if (!containerEl) return;
                        var domId = 'aladin-fov-dom';
                        var existing = document.getElementById(domId);
                        var w = containerEl.clientWidth || containerEl.offsetWidth || 300;
                        var h = containerEl.clientHeight || containerEl.offsetHeight || 300;
                        var minDim = Math.min(w, h);
                        var currentDisplay = null;
                        try {
                            var f = aladinInstance.getFov && aladinInstance.getFov();
                            if (f && f.length) {
                                // Prefer the largest reported FOV value (some Aladin builds report multiple values)
                                var numeric = f.map(function(v) {
                                    return Number(v);
                                }).filter(function(n) {
                                    return !isNaN(n);
                                });
                                if (numeric.length) currentDisplay = Math.min.apply(null, numeric);
                            }
                        } catch (e) {
                            currentDisplay = null;
                        }
                        if (!currentDisplay || isNaN(currentDisplay)) currentDisplay = (
                            typeof displayFovDeg === 'number' ? displayFovDeg : null);

                        // Compute radius in px. Prefer using reported FOV but always apply zoom-based scaling
                        var radiusPx = 0;
                        try {
                            var zoom = (typeof aladinInstance.getZoom === 'function') ? aladinInstance
                                .getZoom() : null;
                            if (typeof aladinInstance.__dslBaseZoom === 'undefined' && typeof zoom !==
                                'undefined' && zoom !== null) {
                                // Establish a base zoom reference on first run
                                aladinInstance.__dslBaseZoom = zoom;
                            }
                            if (currentDisplay && eyeFovDeg) {
                                // preferred: compute directly from FOV (pixel radius). Rely on Aladin's reported FOV to reflect zoom.
                                var baseRadius = (Number(eyeFovDeg) / Number(currentDisplay)) * (minDim /
                                    2);
                                radiusPx = baseRadius;
                                // remember this radius for future adjustments/fallbacks
                                aladinInstance.__dslPrevRadiusPx = radiusPx;
                                // update baseZoom reference so fallback zoom-only scaling has a sensible baseline
                                if (typeof zoom !== 'undefined' && zoom !== null) {
                                    aladinInstance.__dslBaseZoom = zoom;
                                }
                            } else if (typeof zoom !== 'undefined' && zoom !== null && typeof aladinInstance
                                .__dslPrevRadiusPx !== 'undefined' && typeof aladinInstance
                                .__dslLastZoom !== 'undefined' && aladinInstance.__dslLastZoom > 0) {
                                // incremental scaling: scale previous radius by zoom ratio when FOV not available
                                var lastZ = aladinInstance.__dslLastZoom || zoom;
                                var ratio = (zoom / lastZ) || 1;
                                radiusPx = (aladinInstance.__dslPrevRadiusPx || Math.max(40, Math.min(
                                    minDim / 2 - 10, 100))) * ratio;
                                // store updated prev radius
                                aladinInstance.__dslPrevRadiusPx = radiusPx;
                            } else {
                                // fallback: base pixel radius from container
                                radiusPx = Math.max(40, Math.min(minDim / 2 - 10, 100));
                                aladinInstance.__dslPrevRadiusPx = radiusPx;
                            }
                            // update lastZoom for next iteration
                            if (typeof zoom !== 'undefined' && zoom !== null) aladinInstance.__dslLastZoom =
                                zoom;
                        } catch (e) {
                            radiusPx = Math.max(40, Math.min(minDim / 2 - 10, 100));
                            aladinInstance.__dslPrevRadiusPx = radiusPx;
                        }
                        if (!existing) {
                            existing = document.createElement('div');
                            existing.id = domId;
                            existing.style.position = 'absolute';
                            existing.style.pointerEvents = 'none';
                            existing.style.border = '1px solid rgba(0,255,255,0.9)';
                            existing.style.borderRadius = '50%';
                            existing.style.boxSizing = 'border-box';
                            // Keep the overlay below typical UI dropdowns (which use Tailwind z-50)
                            // so menus appear above the FoV circle. Use a modest z-index.
                            existing.style.zIndex = 5;
                            existing.style.left = '50%';
                            existing.style.top = '50%';
                            existing.style.transform = 'translate(-50%,-50%)';
                            containerEl.style.position = containerEl.style.position || 'relative';
                            // ensure the overlay is clipped to the preview container
                            containerEl.style.overflow = 'hidden';
                            containerEl.appendChild(existing);
                        }
                        // Allow the overlay to exceed the visible container so it can be partially clipped.
                        // Apply a soft cap to avoid absurd sizes but don't force full containment.
                        var softMax = Math.round(Math.max(minDim * 1.5, 400));
                        var size = Math.max(8, Math.min(softMax, Math.round(radiusPx * 2)));
                        // If the overlay is substantially larger than the container, hide it entirely to avoid stray arcs showing.
                        var hideThreshold =
                            1.05; // hide when diameter exceeds container min-dimension by 5%
                        if (size > Math.round(minDim * hideThreshold)) {
                            existing.style.display = 'none';
                        } else {
                            existing.style.display = 'block';
                            existing.style.width = size + 'px';
                            existing.style.height = size + 'px';
                            // do not force maxWidth/Height to container; allow overflow to be clipped by container's overflow:hidden
                            existing.style.maxWidth = (softMax) + 'px';
                            existing.style.maxHeight = (softMax) + 'px';
                        }
                        existing.style.transformOrigin = 'center center';
                        existing.style.opacity = '0.9';
                        // scale border thickness slightly with size for better visibility
                        // thinner border: scale down relative to size but keep at least 1px
                        var borderPx = Math.max(1, Math.round(Math.min(4, size / 80)));
                        existing.style.border = borderPx + 'px solid rgba(0,255,255,0.9)';
                    } catch (e) {
                        // ignore
                    }
                }

                // Helper: watch Aladin fov/zoom changes and update the DOM overlay
                function setFovOverlayWatcher(aladinInstance, eyeFovDeg) {
                    try {
                        if (!aladinInstance) return;
                        if (aladinInstance.__dslFovInterval) {
                            clearInterval(aladinInstance.__dslFovInterval);
                            aladinInstance.__dslFovInterval = null;
                        }
                        // Track last known fov and container size to avoid redundant DOM writes
                        aladinInstance.__dslLastFov = null;
                        aladinInstance.__dslLastContainerW = null;
                        aladinInstance.__dslLastContainerH = null;
                        // store the eyeFov on the instance so future watchers/readers prefer the most
                        // recent value (applyAladinSelectsUpdate will update this when selects change)
                        try {
                            aladinInstance.__dslEyeFov = (typeof eyeFovDeg === 'number') ? eyeFovDeg : (
                                aladinInstance.__dslEyeFov || null);
                        } catch (e) {}

                        // First, try to attach event-driven updates: ResizeObserver for container and Aladin event hooks if available
                        var containerEl = document.getElementById('aladin-lite-container');
                        // Cleanup previous observers/listeners if present
                        try {
                            if (aladinInstance.__dslResizeObserver && typeof aladinInstance
                                .__dslResizeObserver.disconnect === 'function') {
                                aladinInstance.__dslResizeObserver.disconnect();
                                aladinInstance.__dslResizeObserver = null;
                            }
                        } catch (e) {}
                        try {
                            if (aladinInstance.__dslAladinListener && typeof aladinInstance
                                .removeListener === 'function') {
                                // best-effort: attempt to remove any previously attached listener
                                try {
                                    aladinInstance.removeListener('zoom', aladinInstance
                                        .__dslAladinListener);
                                } catch (e) {}
                                aladinInstance.__dslAladinListener = null;
                            }
                        } catch (e) {}

                        // Attach ResizeObserver if available
                        if (typeof window.ResizeObserver === 'function' && containerEl) {
                            try {
                                aladinInstance.__dslResizeObserver = new ResizeObserver(function(entries) {
                                    try {
                                        updateDomFovOverlay(aladinInstance, eyeFovDeg);
                                    } catch (e) {}
                                });
                                aladinInstance.__dslResizeObserver.observe(containerEl);
                            } catch (e) {
                                aladinInstance.__dslResizeObserver = null;
                            }
                        }

                        // If Aladin exposes an event or listener system, try to hook into zoom changes
                        try {
                            if (typeof aladinInstance.on === 'function') {
                                // many libs use .on(event, handler)
                                aladinInstance.__dslAladinListener = function() {
                                    updateDomFovOverlay(aladinInstance, eyeFovDeg);
                                };
                                try {
                                    aladinInstance.on('zoom', aladinInstance.__dslAladinListener);
                                } catch (e) {
                                    /* ignore */
                                }
                            } else if (typeof aladinInstance.addListener === 'function') {
                                aladinInstance.__dslAladinListener = function() {
                                    updateDomFovOverlay(aladinInstance, eyeFovDeg);
                                };
                                try {
                                    aladinInstance.addListener('zoom', aladinInstance.__dslAladinListener);
                                } catch (e) {
                                    /* ignore */
                                }
                            }
                        } catch (e) {}

                        // Always keep a lightweight polling fallback for environments where events aren't available
                        aladinInstance.__dslFovInterval = setInterval(function() {
                            try {
                                var f = aladinInstance.getFov && aladinInstance.getFov();
                                var cur = null;
                                var curRa = null;
                                var curDec = null;
                                if (f && f.length) {
                                    var numeric = f.map(function(v) {
                                        return Number(v);
                                    }).filter(function(n) {
                                        return !isNaN(n);
                                    });
                                    if (numeric.length === 1) {
                                        curRa = numeric[0];
                                        curDec = numeric[0];
                                        cur = numeric[0];
                                    } else if (numeric.length >= 2) {
                                        curRa = numeric[0];
                                        curDec = numeric[1];
                                        cur = Math.max.apply(null, [curRa, curDec]);
                                    }
                                }
                                if (cur === null) cur = (typeof displayFovDeg === 'number' ?
                                    displayFovDeg : null);
                                if (curRa === null) curRa = cur;
                                if (curDec === null) curDec = cur;
                                // Update visible legend with source FoV and update an in-preview live-FoV badge
                                try {
                                    var legendFovEl = document.getElementById('aladin-fov');
                                    var badge = document.getElementById('aladin-live-fov-badge');
                                    // Prefer the eyeFov stored on the instance so updates from selects are shown
                                    var currentEyeFov = (typeof aladinInstance.__dslEyeFov ===
                                        'number') ? aladinInstance.__dslEyeFov : ((
                                        typeof eyeFovDeg === 'number') ? eyeFovDeg : null);
                                    var sourceText = (typeof currentEyeFov === 'number' ?
                                        formatFovLabel(currentEyeFov) : '—');
                                    var liveText = '—';
                                    try {
                                        if (curRa !== null && curDec !== null && !isNaN(Number(
                                                curRa)) && !isNaN(Number(curDec))) {
                                            // Show arcminutes only in the live badge (no degrees conversion)
                                            var raArcmin = Math.round(Number(curRa) * 60 * 10) / 10;
                                            var decArcmin = Math.round(Number(curDec) * 60 * 10) /
                                                10;
                                            var raLabel = raArcmin + "'";
                                            var decLabel = decArcmin + "'";
                                            liveText = raLabel + ' × ' + decLabel;
                                        }
                                    } catch (e) {
                                        liveText = '—';
                                    }
                                    if (legendFovEl) {
                                        legendFovEl.textContent = sourceText;
                                    }
                                    try {
                                        if (!badge && containerEl) {
                                            badge = document.createElement('div');
                                            badge.id = 'aladin-live-fov-badge';
                                            badge.style.position = 'absolute';
                                            badge.style.left = '8px';
                                            badge.style.top = '8px';
                                            badge.style.padding = '4px 8px';
                                            badge.style.background = 'rgba(0,0,0,0.6)';
                                            badge.style.color = 'white';
                                            badge.style.borderRadius = '6px';
                                            badge.style.fontSize = '12px';
                                            // ensure the live badge sits above overlay but below menus
                                            badge.style.zIndex = 48;
                                            badge.style.pointerEvents = 'none';
                                            containerEl.style.position = containerEl.style
                                                .position || 'relative';
                                            containerEl.appendChild(badge);
                                        }
                                        if (badge) {
                                            badge.textContent = liveText;
                                        }
                                    } catch (e) {}
                                } catch (e) {}
                                var zoom = (typeof aladinInstance.getZoom === 'function') ?
                                    aladinInstance.getZoom() : null;
                                var w = containerEl ? (containerEl.clientWidth || containerEl
                                    .offsetWidth || 0) : 0;
                                var h = containerEl ? (containerEl.clientHeight || containerEl
                                    .offsetHeight || 0) : 0;
                                // If either fov changed or container size changed, update overlay
                                if (cur !== aladinInstance.__dslLastFov || zoom !== aladinInstance
                                    .__dslLastZoom || w !== aladinInstance.__dslLastContainerW ||
                                    h !== aladinInstance.__dslLastContainerH) {
                                    aladinInstance.__dslLastFov = cur;
                                    aladinInstance.__dslLastZoom = zoom;
                                    aladinInstance.__dslLastContainerW = w;
                                    aladinInstance.__dslLastContainerH = h;
                                    // prefer instance-stored eyeFov when updating overlay
                                    updateDomFovOverlay(aladinInstance, (typeof aladinInstance
                                            .__dslEyeFov === 'number') ? aladinInstance
                                        .__dslEyeFov : eyeFovDeg);
                                }
                            } catch (e) {}
                        }, 300);
                        // Attach a non-passive wheel listener to the container to allow mouse wheel zooming
                        try {
                            if (containerEl && !aladinInstance.__dslWheelHandlerAttached) {
                                var wheelHandler = function(e) {
                                    try {
                                        // prevent page scroll during zooming
                                        if (e && typeof e.preventDefault === 'function') e
                                            .preventDefault();
                                        var delta = e.deltaY || e.wheelDelta || e.detail || 0;
                                        // Normalize: positive delta typically means scroll down -> zoom out
                                        var zoomOut = (delta > 0);
                                        if (typeof aladinInstance.getZoom === 'function' &&
                                            typeof aladinInstance.setZoom === 'function') {
                                            try {
                                                var z = aladinInstance.getZoom();
                                                if (typeof z === 'number') {
                                                    var nz = Math.max(0, z + (zoomOut ? -1 : 1));
                                                    aladinInstance.setZoom(nz);
                                                    return;
                                                }
                                            } catch (e) {}
                                        }
                                        if (typeof aladinInstance.getFov === 'function' &&
                                            typeof aladinInstance.setFov === 'function') {
                                            try {
                                                var fv = aladinInstance.getFov();
                                                var cf = null;
                                                if (fv && fv.length) cf = Number(fv[0]);
                                                else cf = Number(fv) || displayFovDeg || 1.0;
                                                if (cf && !isNaN(cf)) {
                                                    // Adaptive zoom: when current FOV is much larger than the
                                                    // eyeFovDeg (object-sized FoV), use a larger step so the
                                                    // wheel gets you to object scale faster. Otherwise use
                                                    // a modest step for fine control.
                                                    var baseFactor = 1.15;
                                                    var largeFactor = 1.6;
                                                    var useFactor = baseFactor;
                                                    try {
                                                        var curF = Number(cf) || displayFovDeg || 1.0;
                                                        if (typeof eyeFovDeg === 'number' && eyeFovDeg >
                                                            0 && curF > (eyeFovDeg * 8)) {
                                                            useFactor = largeFactor;
                                                        }
                                                    } catch (e) {
                                                        useFactor = baseFactor;
                                                    }
                                                    var factor = zoomOut ? useFactor : (1 / useFactor);
                                                    var newF = Math.max(0.01, Math.min(180, cf *
                                                        factor));
                                                    aladinInstance.setFov(newF);
                                                }
                                            } catch (e) {}
                                        }
                                    } catch (e) {}
                                };
                                try {
                                    containerEl.addEventListener('wheel', wheelHandler, {
                                        passive: false
                                    });
                                } catch (e) {
                                    // older browsers may not support options object
                                    containerEl.addEventListener('wheel', wheelHandler, false);
                                }
                                aladinInstance.__dslWheelHandlerAttached = wheelHandler;
                            }
                        } catch (e) {}
                    } catch (e) {}
                }
                // Helper to add a single marker via a catalog (catalog implements setView)
                function addMarkerViaCatalog(aladinInstance, ra, dec, opts) {
                    try {
                        var marker = Alib.marker([ra, dec], opts || {
                            color: 'magenta',
                            size: 20
                        });
                        var cat = Alib.catalog({
                            name: 'marker-catalog',
                            color: (opts && opts.color) || 'magenta'
                        });
                        cat.addSources([marker]);
                        aladinInstance.addCatalog(cat);
                    } catch (e) {
                        console.error('Failed to add marker via catalog', e);
                    }
                }

                // Draw a circle overlay representing the FoV (radius in degrees = fovDeg / 2)
                // We add the Circle into a graphic overlay (which implements setView) and then add that overlay.
                function addFovCircle(aladinInstance, ra, dec, eyeFovDeg, opts) {
                    try {

                        if (!eyeFovDeg || isNaN(Number(eyeFovDeg))) {

                            return;
                        }
                        var radius = Number(eyeFovDeg) / 2.0; // degrees
                        var circleOpts = opts || {
                            color: 'cyan',
                            lineWidth: 2,
                            opacity: 0.8
                        };

                        // Prefer to use Alib.graphicOverlay if available (overlay implements setView)
                        if (Alib && typeof Alib.graphicOverlay === 'function' && typeof Alib.circle ===
                            'function') {
                            try {

                                var circ = Alib.circle(ra, dec, radius, circleOpts);
                                var overlay = Alib.graphicOverlay({
                                    name: 'fov-overlay'
                                });
                                if (overlay && typeof overlay.add === 'function') {
                                    overlay.add(circ);
                                    aladinInstance.addOverlay(overlay);

                                    return;
                                }
                            } catch (e) {
                                // fall through to marker-based fallback

                            }
                        } else {

                        }

                        // Fallback: approximate the circle with a set of small markers placed around the circumference
                        // This avoids relying on an overlay factory and will always be visible as a ring of points.
                        var points = [];
                        var steps = 72; // more points for a smoother ring
                        var decRad = dec * Math.PI / 180.0;
                        var visColor = (circleOpts && circleOpts.color) ? circleOpts.color : 'lime';
                        var visSize = 24; // larger so it's visible
                        for (var i = 0; i < steps; i++) {
                            var theta = (i / steps) * 2.0 * Math.PI;
                            var dDec = radius * Math.sin(theta); // degrees
                            // Adjust RA offset by cos(dec) to keep angular distance approximately correct
                            var dRa = (radius * Math.cos(theta)) / Math.max(0.0001, Math.cos(decRad));
                            var pRa = ra + dRa;
                            var pDec = dec + dDec;
                            try {
                                var markerOpts = {
                                    color: visColor,
                                    size: visSize,
                                    markerSize: visSize,
                                    sourceSize: visSize,
                                    symbol: 'circle'
                                };
                                var m = Alib.marker([pRa, pDec], markerOpts);
                                points.push(m);
                            } catch (e) {
                                // if marker creation fails, skip point
                            }
                        }
                        if (points.length === 0) {

                            return;
                        }
                        var cat = Alib.catalog({
                            name: 'fov-markers',
                            color: visColor
                        });
                        cat.addSources(points);
                        aladinInstance.addCatalog(cat);
                        // Try to set marker/source size on the catalog if supported
                        try {
                            if (typeof cat.setMarkerSize === 'function') {
                                cat.setMarkerSize(visSize);
                            }
                            if (typeof cat.setSourceSize === 'function') {
                                cat.setSourceSize(visSize);
                            }
                            if (typeof cat.setSymbol === 'function') {
                                cat.setSymbol('circle');
                            }
                        } catch (e) {

                        }
                        // Try to force a redraw on the Aladin instance
                        try {
                            if (typeof aladinInstance.requestRedraw === 'function') {
                                aladinInstance.requestRedraw();
                            } else if (typeof aladinInstance.render === 'function') {
                                aladinInstance.render();
                            }
                        } catch (e) {
                            // ignore
                        }

                        try {
                            if (typeof cat.getSources === 'function') {

                            } else if (cat.sources) {

                            }
                        } catch (e) {

                        }
                        try {
                            if (typeof aladinInstance.getFov === 'function') {

                            }
                            if (typeof aladinInstance.getZoom === 'function') {

                            }
                        } catch (e) {
                            // ignore
                        }

                        // Also add a DOM overlay circle centered on the container so the FoV is always visible.
                        try {
                            var containerEl = document.getElementById('aladin-lite-container');
                            if (containerEl) {
                                var domId = 'aladin-fov-dom';
                                var existing = document.getElementById(domId);
                                // Delegate DOM overlay sizing to the shared helper so zoom-based scaling is consistent
                                try {
                                    updateDomFovOverlay(aladinInstance, eyeFovDeg);
                                } catch (e) {}
                            }
                        } catch (e) {

                        }
                        // Add four cardinal markers (N,S,E,W) to make the ring extent obvious
                        try {
                            var cardinal = [];
                            var offsets = [0, Math.PI / 2, Math.PI, 3 * Math.PI / 2];
                            for (var j = 0; j < offsets.length; j++) {
                                var th = offsets[j];
                                var dDec = radius * Math.sin(th);
                                var dRa = (radius * Math.cos(th)) / Math.max(0.0001, Math.cos(decRad));
                                var pRa = ra + dRa;
                                var pDec = dec + dDec;
                                try {
                                    cardinal.push(Alib.marker([pRa, pDec], {
                                        color: 'magenta',
                                        size: 30
                                    }));
                                } catch (e) {}
                            }
                            if (cardinal.length) {
                                var majorCat = Alib.catalog({
                                    name: 'fov-major',
                                    color: 'magenta'
                                });
                                majorCat.addSources(cardinal);
                                aladinInstance.addCatalog(majorCat);

                            }
                        } catch (e) {

                        }
                        try {
                            if (typeof aladinInstance.getCatalogs === 'function') {

                            }
                        } catch (e) {

                        }
                        try {
                            if (typeof aladinInstance.getCatalogs === 'function') {

                            }
                        } catch (e) {

                        }
                    } catch (e) {
                        console.error('Failed to add FoV circle (both overlay and marker fallback)', e);
                    }
                }

                // Try to detect a 15x RA scaling mismatch between our computed RA (deg)
                // and what Aladin actually uses. If detected, re-apply goto with corrected RA.
                function ensureAladinCenterAndMark(aladinInstance, intendedRaDeg, intendedDecDeg,
                    displayFov, eyeFov) {
                    try {
                        // first attempt (use displayFov for viewport size)
                        aladinInstance.gotoRaDec(intendedRaDeg, intendedDecDeg, displayFov);
                    } catch (e) {
                        console.error('gotoRaDec initial call failed', e);
                    }

                    // short delay to let Aladin update internal center
                    setTimeout(function() {
                        try {
                            var got = aladinInstance.getRaDec && aladinInstance.getRaDec();
                            if (!got || typeof got[0] === 'undefined') {
                                // fallback: just add marker at intended coords
                                addMarkerViaCatalog(aladinInstance, intendedRaDeg, intendedDecDeg, {
                                    color: 'magenta',
                                    size: 20
                                });
                                return;
                            }
                            var gotRa = Number(got[0]);
                            var gotDec = Number(got[1]);


                            // If gotRa is approximately intendedRaDeg, all good.
                            if (Math.abs(gotRa - intendedRaDeg) < 1e-6) {
                                addMarkerViaCatalog(aladinInstance, gotRa, gotDec, {
                                    color: 'magenta',
                                    size: 20
                                });
                                // draw FoV circle using the eyepiece fov
                                try {
                                    addFovCircle(aladinInstance, gotRa, gotDec, eyeFov);
                                } catch (e) {
                                    console.error('addFovCircle threw', e);
                                }
                                return;
                            }

                            // Check if there's a ~1/15 or ~15 ratio mismatch (common RA-hours<->degrees confusion)
                            var ratio = gotRa / intendedRaDeg;
                            if (Math.abs(ratio - 1 / 15) < 0.01) {

                                aladinInstance.gotoRaDec(intendedRaDeg / 15.0, intendedDecDeg,
                                    displayFov);
                                setTimeout(function() {
                                    var g2 = aladinInstance.getRaDec();
                                    addMarkerViaCatalog(aladinInstance, g2[0], g2[1], {
                                        color: 'magenta',
                                        size: 20
                                    });
                                    try {
                                        addFovCircle(aladinInstance, g2[0], g2[1], eyeFov);
                                    } catch (e) {
                                        console.error('addFovCircle threw', e);
                                    }
                                }, 200);
                                return;
                            }
                            if (Math.abs(ratio - 15) < 0.01) {

                                aladinInstance.gotoRaDec(intendedRaDeg * 15.0, intendedDecDeg,
                                    displayFov);
                                setTimeout(function() {
                                    var g2 = aladinInstance.getRaDec();
                                    addMarkerViaCatalog(aladinInstance, g2[0], g2[1], {
                                        color: 'magenta',
                                        size: 20
                                    });
                                    try {
                                        addFovCircle(aladinInstance, g2[0], g2[1], eyeFov);
                                    } catch (e) {
                                        console.error('addFovCircle threw', e);
                                    }
                                }, 200);
                                return;
                            }

                            // No obvious scaling; just add marker at what Aladin reports
                            addMarkerViaCatalog(aladinInstance, gotRa, gotDec, {
                                color: 'magenta',
                                size: 20
                            });
                            try {
                                addFovCircle(aladinInstance, gotRa, gotDec, eyeFov);
                            } catch (e) {
                                console.error('addFovCircle threw', e);
                            }
                        } catch (e) {
                            console.error('Error while ensuring Aladin center', e);
                            addMarkerViaCatalog(aladinInstance, intendedRaDeg, intendedDecDeg, {
                                color: 'magenta',
                                size: 20
                            });
                            try {
                                addFovCircle(aladinInstance, intendedRaDeg, intendedDecDeg, eyeFov);
                            } catch (e) {
                                console.error('addFovCircle threw', e);
                            }
                        }
                    }, 250);
                }

                if (centerRaDeg !== null && centerDecDeg !== null) {
                    var aladin = Alib.aladin('#aladin-lite-container', {
                        survey: 'P/DSS2/color',
                        fov: displayFovDeg,
                        cooFrame: 'ICRS'
                    });
                    // remember current aladin instance globally
                    __dslCurrentAladin = aladin;
                    try {
                        setFovOverlayWatcher(aladin, fovUsedDeg);
                    } catch (e) {}
                    // Prune unwanted controls once created
                    try {
                        pruneAladinControls(document.getElementById('aladin-lite-container'), aladin);
                    } catch (e) {}
                    // Install pan shim
                    try {
                        installAladinPanShim(document.getElementById('aladin-lite-container'), aladin);
                    } catch (e) {}
                    // Multiply RA by 15 before sending to Aladin (user requested behavior)
                    ensureAladinCenterAndMark(aladin, centerRaDeg * 15.0, centerDecDeg, displayFovDeg,
                        fovUsedDeg);
                    // After initial goto, adjust the display FOV iteratively so the Declination (vertical)
                    // FOV reported by Aladin matches the object's angular size (fovUsedDeg).
                    try {
                        callSetDisplayFovRepeated(aladin, fovUsedDeg, 24, 2, 160);
                    } catch (e) {}
                } else if (aladinDefaults && aladinDefaults.ra_raw && aladinDefaults.dec_raw) {
                    var raGuess = parseRaToDegrees(aladinDefaults.ra_raw);
                    var decGuess = parseDecToDegrees(aladinDefaults.dec_raw);
                    if (raGuess !== null && decGuess !== null) {
                        var al = Alib.aladin('#aladin-lite-container', {
                            survey: 'P/DSS2/color',
                            fov: displayFovDeg,
                            cooFrame: 'ICRS'
                        });
                        __dslCurrentAladin = al;
                        try {
                            setFovOverlayWatcher(al, fovUsedDeg);
                        } catch (e) {}
                        try {
                            pruneAladinControls(document.getElementById('aladin-lite-container'), al);
                        } catch (e) {}
                        try {
                            installAladinPanShim(document.getElementById('aladin-lite-container'), al);
                        } catch (e) {}
                        // Multiply RA by 15 before sending to Aladin (user requested behavior)
                        ensureAladinCenterAndMark(al, raGuess * 15.0, decGuess, displayFovDeg, fovUsedDeg);
                        try {
                            callSetDisplayFovRepeated(al, fovUsedDeg, 24, 2, 160);
                        } catch (e) {}
                    } else if (sessionName) {
                        var al2 = Alib.aladin('#aladin-lite-container', {
                            survey: 'P/DSS2/color',
                            fov: displayFovDeg,
                            cooFrame: 'ICRS'
                        });
                        __dslCurrentAladin = al2;
                        try {
                            setFovOverlayWatcher(al2, fovUsedDeg);
                        } catch (e) {}
                        try {
                            pruneAladinControls(document.getElementById('aladin-lite-container'), al2);
                        } catch (e) {}
                        try {
                            installAladinPanShim(document.getElementById('aladin-lite-container'), al2);
                        } catch (e) {}
                        al2.gotoObject(sessionName);
                    }
                } else if (sessionName) {
                    var al3 = Alib.aladin('#aladin-lite-container', {
                        survey: 'P/DSS2/color',
                        fov: displayFovDeg,
                        cooFrame: 'ICRS'
                    });
                    __dslCurrentAladin = al3;
                    try {
                        setFovOverlayWatcher(al3, fovUsedDeg);
                    } catch (e) {}
                    try {
                        pruneAladinControls(document.getElementById('aladin-lite-container'), al3);
                    } catch (e) {}
                    try {
                        installAladinPanShim(document.getElementById('aladin-lite-container'), al3);
                    } catch (e) {}
                    al3.gotoObject(sessionName);
                    try {
                        callSetDisplayFovRepeated(al3, fovUsedDeg, 24, 2, 160);
                    } catch (e) {}
                }
            });
        }

        // Install a pan shim that translates pointer drags into Aladin goto calls.
        // This bypasses library-specific mouse handlers which may ignore synthetic events
        // or rely on movementX/movementY. The shim uses the current FOV to convert
        // pixel deltas into RA/Dec shifts and calls gotoRaDec on the instance.
        function installAladinPanShim(containerEl, aladinInstance) {
            try {
                if (!containerEl || !aladinInstance) return;
                if (containerEl.__dsl_pan_shim_installed) return;
                containerEl.__dsl_pan_shim_installed = true;
                var dragging = false;
                var startX = 0,
                    startY = 0,
                    startRa = null,
                    startDec = null,
                    baseFov = null;
                containerEl.addEventListener('pointerdown', function(e) {
                    try {
                        if (e.button !== 0) return; // only left button
                        if (!aladinInstance || typeof aladinInstance.getRaDec !== 'function') return;
                        var g = aladinInstance.getRaDec && aladinInstance.getRaDec();
                        if (!g || typeof g[0] === 'undefined') return;
                        startRa = Number(g[0]);
                        startDec = Number(g[1]);
                        var f = aladinInstance.getFov && aladinInstance.getFov();
                        baseFov = (Array.isArray(f) && f.length) ? Number(f[0]) : (Number(f) ||
                            displayFovDeg || 1.0);
                        dragging = true;
                        startX = e.clientX;
                        startY = e.clientY;
                        try {
                            if (e.target && e.target.setPointerCapture) e.target.setPointerCapture(e
                                .pointerId);
                        } catch (e) {}
                        e.preventDefault();
                    } catch (e) {}
                }, true);

                window.addEventListener('pointermove', function(e) {
                    try {
                        if (!dragging) return;
                        if (!aladinInstance) return;
                        var cw = containerEl.clientWidth || containerEl.offsetWidth || 1;
                        var ch = containerEl.clientHeight || containerEl.offsetHeight || 1;
                        var dx = e.clientX - startX;
                        var dy = e.clientY - startY;
                        var effDx = dx;
                        var effDy = dy;
                        var display = baseFov || (typeof displayFovDeg === 'number' ? displayFovDeg : 1.0);
                        var vFov = display * (ch / Math.max(1, cw));
                        // Convert pixel deltas to degrees. Use effective deltas (effDx/effDy)
                        // which are inverted when the preview is mirrored so that UI drag
                        // direction matches visual movement.
                        var dRa = (effDx / cw) * display;
                        var dDec = -(effDy / ch) * vFov;
                        var newRa = startRa + dRa;
                        // Apply vertical mapping: pointer up should increase Dec visually; account for sign above
                        var newDec = startDec - dDec;
                        try {
                            if (typeof aladinInstance.gotoRaDec === 'function') aladinInstance.gotoRaDec(
                                newRa, newDec, display);
                        } catch (e) {}
                    } catch (e) {}
                }, {
                    passive: true,
                    capture: true
                });

                window.addEventListener('pointerup', function(e) {
                    try {
                        if (!dragging) return;
                        dragging = false;
                        try {
                            if (e.target && e.target.releasePointerCapture) e.target.releasePointerCapture(e
                                .pointerId);
                        } catch (e) {}
                    } catch (e) {}
                }, {
                    passive: true,
                    capture: true
                });
            } catch (e) {}
        }

        // Apply current selects to aladinDefaults and update preview
        function applyAladinSelectsUpdate() {
            try {
                // Force-sync visible select/combobox controls into the hidden inputs
                // so we don't read a stale value when selects update asynchronously.
                (function forceSyncVisibleIntoHidden() {
                    try {
                        ['instrument', 'eyepiece', 'lens'].forEach(function(k) {
                            try {
                                var hidden = document.getElementById('aladin-' + k + '-hidden');
                                if (!hidden) return;
                                var wrapper = document.querySelector('[data-dsl-field="' + k + '"]') ||
                                    hidden.parentElement || null;
                                var sel = wrapper ? wrapper.querySelector('select') : null;
                                var v = '';
                                if (sel) {
                                    try {
                                        if (sel.tom && typeof sel.tom.getValue === 'function') v = sel
                                            .tom.getValue();
                                    } catch (e) {}
                                    if (!v) v = sel.value || (sel.dataset && sel.dataset.tsValue) || '';
                                }
                                if (!v && wrapper) {
                                    var control = wrapper.querySelector(
                                        'input[type="text"], [role="combobox"], input');
                                    if (control) try {
                                        v = control.value || '';
                                    } catch (e) {}
                                }
                                if (v !== undefined && hidden.value !== v) hidden.value = v;
                            } catch (e) {}
                        });
                    } catch (e) {}
                })();
                var instHidden = document.getElementById('aladin-instrument-hidden');
                var epHidden = document.getElementById('aladin-eyepiece-hidden');
                var lnHidden = document.getElementById('aladin-lens-hidden');
                if (!aladinDefaults) aladinDefaults = {};
                // instrument
                if (instHidden && instHidden.value) {
                    var instId = instHidden.value;
                    var instMeta = (DSL_AVAILABLE.instruments || []).find(function(i) {
                        return String(i.id) === String(instId);
                    });
                    aladinDefaults.instrument = instMeta ? {
                        id: instMeta.id,
                        focal_length_mm: instMeta.focal_length_mm || instMeta.focal_length_mm_mm || null,
                        aperture_mm: instMeta.aperture_mm || null,
                        fixedMagnification: instMeta.fixedMagnification || null
                    } : {
                        id: instId
                    };
                } else {
                    aladinDefaults.instrument = null;
                }
                // eyepiece
                if (epHidden && epHidden.value) {
                    var epId = epHidden.value;
                    var epMeta = (DSL_AVAILABLE.eyepieces || []).find(function(e) {
                        return String(e.id) === String(epId);
                    });
                    aladinDefaults.eyepiece = epMeta ? {
                        id: epMeta.id,
                        focal_length_mm: epMeta.focal_length_mm || null,
                        apparent_fov_deg: epMeta.apparent_fov_deg || null
                    } : {
                        id: epId
                    };
                } else {
                    aladinDefaults.eyepiece = null;
                }
                // lens (support explicit empty string as 'none')
                if (lnHidden && lnHidden.value) {
                    var lnId = lnHidden.value;
                    var lnMeta = (DSL_AVAILABLE.lenses || []).find(function(l) {
                        return String(l.id) === String(lnId);
                    });
                    aladinDefaults.lens = lnMeta ? {
                        id: lnMeta.id,
                        factor: lnMeta.factor || null
                    } : {
                        id: lnId
                    };
                } else {
                    aladinDefaults.lens = null;
                }

                var eyeFovDeg = computeFovDegFromDefaults(aladinDefaults);

                // update legend
                var fovEl = document.getElementById('aladin-fov');
                var magEl = document.getElementById('aladin-mag');
                if (fovEl) fovEl.textContent = (typeof eyeFovDeg === 'number' ? formatFovLabel(eyeFovDeg) : '—');
                var magUsed = null;
                try {
                    if (aladinDefaults && aladinDefaults.instrument && aladinDefaults.eyepiece) {
                        magUsed = aladinDefaults.instrument.fixedMagnification ? Number(aladinDefaults.instrument
                            .fixedMagnification) : (aladinDefaults.instrument.focal_length_mm && aladinDefaults
                            .eyepiece.focal_length_mm ? Number(aladinDefaults.instrument.focal_length_mm) /
                            Number(aladinDefaults.eyepiece.focal_length_mm) : null);
                        // Apply lens factor for overall magnification if present
                        try {
                            if (aladinDefaults && aladinDefaults.lens && aladinDefaults.lens.factor) {
                                magUsed = magUsed ? Number(magUsed) * Number(aladinDefaults.lens.factor) : magUsed;
                            }
                        } catch (e) {}
                        if (magUsed && aladinDefaults.eyepiece.apparent_fov_deg) {
                            eyeFovDeg = Math.max(0.01, Number(aladinDefaults.eyepiece.apparent_fov_deg) / magUsed);
                        } else if (magUsed) {
                            eyeFovDeg = Math.max(0.01, 50.0 / magUsed);
                        }
                    }
                } catch (e) {
                    magUsed = null;
                }
                if (magEl) magEl.textContent = magUsed ? Math.round(magUsed) + 'x' : '—';
                // update FoV legend again in case mag/lens changed eyeFovDeg calculation
                try {
                    if (fovEl) fovEl.textContent = (typeof eyeFovDeg === 'number' ? formatFovLabel(eyeFovDeg) :
                        '—');
                } catch (e) {}

                // update Aladin if instance already present
                if (__dslCurrentAladin) {
                    try {
                        // try to set display FOV vertically and redraw overlays
                        // store computed eyeFov on the instance so watchers/readers use latest value
                        try {
                            __dslCurrentAladin.__dslEyeFov = (typeof eyeFovDeg === 'number') ? eyeFovDeg : (
                                __dslCurrentAladin.__dslEyeFov || null);
                        } catch (e) {}
                        setDisplayFovForVertical(__dslCurrentAladin, eyeFovDeg, 24);
                        // reduce attempts/delay to minimize visible multiple zoom changes
                        callSetDisplayFovRepeated(__dslCurrentAladin, eyeFovDeg, 24, 2, 180);
                        // shorten final adjust timeout to make final settle faster
                        try {
                            finalAdjustFovToMatchDec(__dslCurrentAladin, eyeFovDeg);
                        } catch (e) {}
                        // attempt to re-center/add FOV circle using stored center
                        if (typeof __dslCenterRaDeg === 'number' && typeof __dslCenterDecDeg === 'number') {
                            try {
                                addFovCircle(__dslCurrentAladin, __dslCenterRaDeg * 15.0, __dslCenterDecDeg,
                                    eyeFovDeg);
                            } catch (e) {}
                        }
                    } catch (e) {
                        console.error('Failed to update Aladin preview from selects', e);
                    }
                }
                try {
                    // small re-checks with backoff to catch later DOM replacements
                    setTimeout(function() {
                        try {
                            if (__dslCurrentAladin) callSetDisplayFovRepeated(__dslCurrentAladin,
                                computeFovDegFromDefaults(aladinDefaults), 24, 2, 100);
                        } catch (e) {}
                    }, 80);
                    setTimeout(function() {
                        try {
                            if (__dslCurrentAladin) callSetDisplayFovRepeated(__dslCurrentAladin,
                                computeFovDegFromDefaults(aladinDefaults), 24, 2, 220);
                        } catch (e) {}
                    }, 250);
                    setTimeout(function() {
                        try {
                            if (__dslCurrentAladin) callSetDisplayFovRepeated(__dslCurrentAladin,
                                computeFovDegFromDefaults(aladinDefaults), 24, 2, 420);
                        } catch (e) {}
                    }, 700);
                } catch (e) {}
            } catch (e) {
                console.error('applyAladinSelectsUpdate error', e);
            }
        }

        // Read hidden select inputs and merge into aladinDefaults (used during init and by update)
        function readSelectsIntoDefaults() {
            try {
                var instHidden = document.getElementById('aladin-instrument-hidden');
                var epHidden = document.getElementById('aladin-eyepiece-hidden');
                var lnHidden = document.getElementById('aladin-lens-hidden');
                if (!aladinDefaults) aladinDefaults = {};
                if (instHidden && instHidden.value) {
                    var instId = instHidden.value;
                    var instMeta = (DSL_AVAILABLE.instruments || []).find(function(i) {
                        return String(i.id) === String(instId);
                    });
                    aladinDefaults.instrument = instMeta ? {
                        id: instMeta.id,
                        focal_length_mm: instMeta.focal_length_mm || instMeta.focal_length_mm_mm || null,
                        aperture_mm: instMeta.aperture_mm || null,
                        fixedMagnification: instMeta.fixedMagnification || null
                    } : {
                        id: instId
                    };
                } else {
                    aladinDefaults.instrument = null;
                }
                if (epHidden && epHidden.value) {
                    var epId = epHidden.value;
                    var epMeta = (DSL_AVAILABLE.eyepieces || []).find(function(e) {
                        return String(e.id) === String(epId);
                    });
                    aladinDefaults.eyepiece = epMeta ? {
                        id: epMeta.id,
                        focal_length_mm: epMeta.focal_length_mm || null,
                        apparent_fov_deg: epMeta.apparent_fov_deg || null
                    } : {
                        id: epId
                    };
                } else {
                    aladinDefaults.eyepiece = null;
                }
                if (lnHidden && lnHidden.value) {
                    var lnId = lnHidden.value;
                    var lnMeta = (DSL_AVAILABLE.lenses || []).find(function(l) {
                        return String(l.id) === String(lnId);
                    });
                    aladinDefaults.lens = lnMeta ? {
                        id: lnMeta.id,
                        factor: lnMeta.factor || null
                    } : {
                        id: lnId
                    };
                } else {
                    aladinDefaults.lens = null;
                }
            } catch (e) {
                /* ignore */
            }
        }

        function scheduleApplyAladinSelectsUpdate() {
            try {
                if (typeof __dslAladinUpdateTimer !== 'undefined' && __dslAladinUpdateTimer) clearTimeout(
                    __dslAladinUpdateTimer);
                __dslAladinUpdateTimer = setTimeout(function() {
                    applyAladinSelectsUpdate();
                }, 220);
            } catch (e) {}
        }
        // expose to global so WireUI x-select handlers can call it
        window.scheduleApplyAladinSelectsUpdate = scheduleApplyAladinSelectsUpdate;

        // Update the small textual labels below each select from hidden inputs and available lists
        function updateSelectedLabels() {
            try {
                var instHidden = document.getElementById('aladin-instrument-hidden');
                var epHidden = document.getElementById('aladin-eyepiece-hidden');
                var lnHidden = document.getElementById('aladin-lens-hidden');
                var instLabel = document.getElementById('aladin-instrument-selected-label');
                var epLabel = document.getElementById('aladin-eyepiece-selected-label');
                var lnLabel = document.getElementById('aladin-lens-selected-label');
                try {
                    if (instHidden && instLabel) {
                        var id = instHidden.value || '';
                        var txt = '—';
                        if (id) {
                            var f = (DSL_AVAILABLE && DSL_AVAILABLE.instruments) ? (DSL_AVAILABLE.instruments.find(
                                function(i) {
                                    return String(i.id) === String(id);
                                }) || null) : null;
                            txt = f ? (f.name || '') : id;
                        } else {
                            txt = (DSL_TEXT && DSL_TEXT.none_label) ? DSL_TEXT.none_label : '(none)';
                        }
                        instLabel.textContent = txt;
                    }
                } catch (e) {}
                try {
                    if (epHidden && epLabel) {
                        var id2 = epHidden.value || '';
                        var txt2 = '—';
                        if (id2) {
                            var f2 = (DSL_AVAILABLE && DSL_AVAILABLE.eyepieces) ? (DSL_AVAILABLE.eyepieces.find(
                                function(i) {
                                    return String(i.id) === String(id2);
                                }) || null) : null;
                            txt2 = f2 ? (f2.name || '') : id2;
                        } else {
                            txt2 = (DSL_TEXT && DSL_TEXT.none_label) ? DSL_TEXT.none_label : '(none)';
                        }
                        epLabel.textContent = txt2;
                    }
                } catch (e) {}
                try {
                    if (lnHidden && lnLabel) {
                        var id3 = lnHidden.value || '';
                        var txt3 = '—';
                        if (id3) {
                            var f3 = (DSL_AVAILABLE && DSL_AVAILABLE.lenses) ? (DSL_AVAILABLE.lenses.find(function(
                                i) {
                                return String(i.id) === String(id3);
                            }) || null) : null;
                            txt3 = f3 ? (f3.name || '') : id3;
                        } else {
                            // default to localized 'No lens' or 'none'
                            txt3 = (DSL_TEXT && DSL_TEXT.none_label) ? DSL_TEXT.none_label : 'No lens';
                        }
                        lnLabel.textContent = txt3;
                    }
                } catch (e) {}
            } catch (e) {}
        }

        // Keep textual labels updated whenever selects are applied
        var __dslLabelUpdateTimer = null;
        var origSchedule = window.scheduleApplyAladinSelectsUpdate;
        window.scheduleApplyAladinSelectsUpdate = function() {
            try {
                if (typeof __dslLabelUpdateTimer !== 'undefined' && __dslLabelUpdateTimer) clearTimeout(
                    __dslLabelUpdateTimer);
                __dslLabelUpdateTimer = setTimeout(function() {
                    try {
                        updateSelectedLabels();
                    } catch (e) {}
                }, 30);
            } catch (e) {};
            try {
                origSchedule();
            } catch (e) {}
        };

        // attach change listeners to selects when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Helper: sync visible <select> value into hidden input if they differ, and trigger update
                function syncSelectToHidden(selectEl, hiddenEl) {
                    try {
                        if (!selectEl || !hiddenEl) return;
                        var v = selectEl.value || '';
                        if (!v && selectEl.dataset && selectEl.dataset.tsValue) v = selectEl.dataset
                            .tsValue; // fallback
                        if (hiddenEl.value !== v) {

                            hiddenEl.value = v;
                            try {
                                updateSelectedLabels();
                            } catch (e) {}
                            try {
                                scheduleApplyAladinSelectsUpdate();
                            } catch (e) {}
                        }
                    } catch (e) {}
                }

                // Attach change listeners to any underlying select elements inside the select wrappers.
                function attachSelectSyncing() {
                    try {
                        var instHidden = document.getElementById('aladin-instrument-hidden');
                        var epHidden = document.getElementById('aladin-eyepiece-hidden');
                        var lnHidden = document.getElementById('aladin-lens-hidden');
                        if (instHidden) {
                            var wrap = document.querySelector('[data-dsl-field="instrument"]') || instHidden
                                .parentElement || null;
                            var s = wrap ? wrap.querySelector('select') : null;
                            if (s) {
                                if (s._dsl_sync_handler) s.removeEventListener('change', s
                                    ._dsl_sync_handler);
                                s._dsl_sync_handler = function() {
                                    syncSelectToHidden(s, instHidden);
                                };
                                s.addEventListener('change', s._dsl_sync_handler);
                                try {
                                    if (s.tom && typeof s.tom.on === 'function') s.tom.on('change', s
                                        ._dsl_sync_handler);
                                } catch (e) {}
                            }
                        }
                        if (epHidden) {
                            var wrap2 = document.querySelector('[data-dsl-field="eyepiece"]') || epHidden
                                .parentElement || null;
                            var s2 = wrap2 ? wrap2.querySelector('select') : null;
                            if (s2) {
                                if (s2._dsl_sync_handler) s2.removeEventListener('change', s2
                                    ._dsl_sync_handler);
                                s2._dsl_sync_handler = function() {
                                    syncSelectToHidden(s2, epHidden);
                                };
                                s2.addEventListener('change', s2._dsl_sync_handler);
                                try {
                                    if (s2.tom && typeof s2.tom.on === 'function') s2.tom.on('change', s2
                                        ._dsl_sync_handler);
                                } catch (e) {}
                            }
                        }
                        if (lnHidden) {
                            var wrap3 = document.querySelector('[data-dsl-field="lens"]') || lnHidden
                                .parentElement || null;
                            var s3 = wrap3 ? wrap3.querySelector('select') : null;
                            if (s3) {
                                if (s3._dsl_sync_handler) s3.removeEventListener('change', s3
                                    ._dsl_sync_handler);
                                s3._dsl_sync_handler = function() {
                                    syncSelectToHidden(s3, lnHidden);
                                };
                                s3.addEventListener('change', s3._dsl_sync_handler);
                                try {
                                    if (s3.tom && typeof s3.tom.on === 'function') s3.tom.on('change', s3
                                        ._dsl_sync_handler);
                                } catch (e) {}
                            }
                        }
                    } catch (e) {}
                }

                // Bounded polling: try a few times to attach handlers and sync values in case async widgets initialize slowly
                (function tryAttachLoop() {
                    var attempts = 0;
                    var maxAttempts = 8; // ~8 * 150ms = 1.2s
                    var iv = setInterval(function() {
                        try {
                            attachSelectSyncing();
                            // proactively sync visible values into hidden inputs when found
                            var instHidden = document.getElementById(
                                'aladin-instrument-hidden');
                            var epHidden = document.getElementById('aladin-eyepiece-hidden');
                            var lnHidden = document.getElementById('aladin-lens-hidden');
                            if (instHidden) {
                                var wrapper = document.querySelector(
                                        '[data-dsl-field="instrument"]') || instHidden
                                    .parentElement || null;
                                if (wrapper) {
                                    var s = wrapper.querySelector('select');
                                    if (s) syncSelectToHidden(s, instHidden);
                                }
                            }
                            if (epHidden) {
                                var wrapper2 = document.querySelector(
                                        '[data-dsl-field="eyepiece"]') || epHidden
                                    .parentElement || null;
                                if (wrapper2) {
                                    var s2 = wrapper2.querySelector('select');
                                    if (s2) syncSelectToHidden(s2, epHidden);
                                }
                            }
                            if (lnHidden) {
                                var wrapper3 = document.querySelector(
                                        '[data-dsl-field="lens"]') || lnHidden.parentElement ||
                                    null;
                                if (wrapper3) {
                                    var s3 = wrapper3.querySelector('select');
                                    if (s3) syncSelectToHidden(s3, lnHidden);
                                }
                            }
                        } catch (e) {}
                        attempts++;
                        if (attempts >= maxAttempts) {
                            clearInterval(iv);
                        }
                    }, 150);
                })();

                var instHidden = document.getElementById('aladin-instrument-hidden');
                var epHidden = document.getElementById('aladin-eyepiece-hidden');
                var lnHidden = document.getElementById('aladin-lens-hidden');
                var saveBtn = document.getElementById('aladin-save-btn');
                // Parse embedded data-available and data-dsl-text now that DOM is ready
                try {
                    var _alc = document.getElementById('aladin-lite-container');
                    if (_alc) {
                        try {
                            var dab = _alc.getAttribute('data-available');
                            if (dab) DSL_AVAILABLE = JSON.parse(atob(dab));
                        } catch (e) {
                            /* ignore */
                        }
                        try {
                            var dslb = _alc.getAttribute('data-dsl-text');
                            if (dslb) DSL_TEXT = JSON.parse(atob(dslb));
                        } catch (e) {
                            /* ignore */
                        }
                    }
                } catch (e) {}
                // hidden inputs are updated by x-select change handlers; still call schedule to apply
                if (instHidden) instHidden.addEventListener('change', function(e) {
                    try {
                        scheduleApplyAladinSelectsUpdate();
                        console.log('aladin hidden instrument changed', instHidden.value);
                        var payload = {
                            instrument: instHidden.value || null,
                            eyepiece: epHidden ? (epHidden.value || null) : null,
                            lens: lnHidden ? (lnHidden.value || null) : null
                        };
                        try {
                            var __alc = document.getElementById('aladin-lite-container');
                            var __oid = (__alc && __alc.getAttribute ? __alc.getAttribute(
                                    'data-object-id') : null) || (window.__dsl_server_selected &&
                                    window.__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                            if (__oid === '') __oid = null;
                            payload.objectId = __oid;
                        } catch (e) {
                            payload.objectId = (window.__dsl_server_selected && window
                                    .__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                        }

                        // Dispatch in a predictable order: Livewire.dispatchTo -> central emitter -> Livewire.dispatch -> DOM event
                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try {
                                    Livewire.dispatchTo('aladin-preview-info', 'recalculate',
                                        payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                try {
                                    window.__dsl_emitAladinUpdated(payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try {
                                    Livewire.dispatch('aladinUpdated', payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                                detail: payload
                            }));
                        } catch (e) {}
                    } catch (e) {}
                });
                if (epHidden) epHidden.addEventListener('change', function(e) {
                    try {
                        scheduleApplyAladinSelectsUpdate();
                        console.log('aladin hidden eyepiece changed', epHidden.value);
                        var payload = {
                            instrument: instHidden ? (instHidden.value || null) : null,
                            eyepiece: epHidden.value || null,
                            lens: lnHidden ? (lnHidden.value || null) : null
                        };
                        try {
                            var __alc = document.getElementById('aladin-lite-container');
                            var __oid = (__alc && __alc.getAttribute ? __alc.getAttribute(
                                    'data-object-id') : null) || (window.__dsl_server_selected &&
                                    window.__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                            if (__oid === '') __oid = null;
                            payload.objectId = __oid;
                        } catch (e) {
                            payload.objectId = (window.__dsl_server_selected && window
                                    .__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                        }

                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try {
                                    Livewire.dispatchTo('aladin-preview-info', 'recalculate',
                                        payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                try {
                                    window.__dsl_emitAladinUpdated(payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try {
                                    Livewire.dispatch('aladinUpdated', payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                                detail: payload
                            }));
                        } catch (e) {}
                    } catch (e) {}
                });
                if (lnHidden) lnHidden.addEventListener('change', function(e) {
                    try {
                        scheduleApplyAladinSelectsUpdate();
                        console.log('aladin hidden lens changed', lnHidden.value);
                        var payload = {
                            instrument: instHidden ? (instHidden.value || null) : null,
                            eyepiece: epHidden ? (epHidden.value || null) : null,
                            lens: lnHidden.value || null
                        };
                        try {
                            var __alc = document.getElementById('aladin-lite-container');
                            var __oid = (__alc && __alc.getAttribute ? __alc.getAttribute(
                                    'data-object-id') : null) || (window.__dsl_server_selected &&
                                    window.__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                            if (__oid === '') __oid = null;
                            payload.objectId = __oid;
                        } catch (e) {
                            payload.objectId = (window.__dsl_server_selected && window
                                    .__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null;
                        }

                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try {
                                    Livewire.dispatchTo('aladin-preview-info', 'recalculate',
                                        payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                try {
                                    window.__dsl_emitAladinUpdated(payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try {
                                    Livewire.dispatch('aladinUpdated', payload);
                                    return;
                                } catch (e) {}
                            }
                        } catch (e) {}
                        try {
                            window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                                detail: payload
                            }));
                        } catch (e) {}
                    } catch (e) {}
                });
                if (saveBtn) {
                    saveBtn.addEventListener('click', function() {
                        // show saving state
                        try {
                            // Ensure hidden inputs reflect the current visible widget values.
                            // This guards against async TomSelect/WireUI widgets that haven't propagated
                            // their value into the server-initialized hidden inputs yet.
                            try {
                                var mismatches = [];
                                (function forceSyncVisibleIntoHidden() {
                                    try {
                                        ['instrument', 'eyepiece', 'lens'].forEach(function(k) {
                                            try {
                                                // prefer central emitter / DOM event first
                                                try {
                                                    if (typeof window
                                                        .__dsl_emitAladin === 'function'
                                                    ) {
                                                        try {
                                                            if (window
                                                                .__dsl_debug_aladin)
                                                                console.debug(
                                                                    '[dsl] used __dsl_emitAladinUpdated ->',
                                                                    payload);
                                                        } catch (e) {}
                                                        window.__dsl_emitAladinUpdated(
                                                            payload);
                                                        return;
                                                    }
                                                } catch (e) {}
                                                var sel = wrapper ? wrapper
                                                    .querySelector('select') : null;
                                                var v = '';
                                                if (sel) {
                                                    try {
                                                        if (sel.tom && typeof sel.tom
                                                            .getValue === 'function')
                                                            v = sel.tom.getValue();
                                                    } catch (e) {}
                                                    if (!v) v = sel.value || (sel
                                                        .dataset && sel.dataset
                                                        .tsValue) || '';
                                                }
                                                var control = (!v && wrapper) ? wrapper
                                                    .querySelector(
                                                        'input[type="text"], [role="combobox"], input'
                                                    ) : null;
                                                if (!v && control) try {
                                                    v = control.value || '';
                                                } catch (e) {}
                                                if (v && hidden.value !== v) {
                                                    hidden.value = v;
                                                }
                                                // Normalize empty strings vs null-ish
                                                var hv = (hidden.value || '')
                                                    .toString();
                                                var vv = (v || '').toString();
                                                if (hv !== vv) mismatches.push({
                                                    which: k,
                                                    hidden: hv,
                                                    visible: vv,
                                                    wrapperText: (wrapper ? (
                                                        wrapper
                                                        .innerText || ''
                                                    ).trim() : '')
                                                });
                                            } catch (e) {}
                                        });
                                    } catch (e) {}
                                })();
                                if (mismatches.length) {
                                    // Show toast and abort save so user can retry/select
                                    try {
                                        showDslToast((DSL_TEXT && DSL_TEXT.save_failed) ? (DSL_TEXT
                                                .save_failed + ' — mismatch') :
                                            'Save failed — selections not synchronized');
                                    } catch (e) {}
                                    saveBtn.disabled = false;
                                    saveBtn.textContent = (DSL_TEXT && DSL_TEXT.save) ? DSL_TEXT
                                        .save : 'Save';
                                    return; // abort sending
                                }
                            } catch (e) {
                                // If the check throws, fall through and attempt save anyway
                            }

                            var payload = (function() {
                                var out = {
                                    instrument_id: null,
                                    eyepiece_id: null,
                                    lens_id: null
                                };
                                try {
                                    // prefer hidden inputs
                                    if (instHidden && instHidden.value) out.instrument_id =
                                        instHidden.value;
                                    if (epHidden && epHidden.value) out.eyepiece_id =
                                        epHidden.value;
                                    if (lnHidden && lnHidden.value) out.lens_id = lnHidden
                                        .value;
                                    // fall back to underlying select element if hidden input is empty
                                    try {
                                        if ((!out.instrument_id || out.instrument_id ===
                                                '') && instHidden) {
                                            var wrapper = document.querySelector(
                                                    '[data-dsl-field="instrument"]') ||
                                                instHidden.parentElement || null;
                                            var s = wrapper ? wrapper.querySelector(
                                                'select') : null;
                                            if (s && s.value) out.instrument_id = s.value;
                                            // fallback: input/text or combobox-like controls
                                            if ((!out.instrument_id || out.instrument_id ===
                                                    '')) {
                                                var control = wrapper ? wrapper
                                                    .querySelector(
                                                        'input[type="text"], [role="combobox"], input'
                                                    ) : null;
                                                try {
                                                    if (control && control.value) out
                                                        .instrument_id = control.value;
                                                } catch (e) {}
                                            }
                                            // fallback: try mapping visible label text to an id from DSL_AVAILABLE
                                            if ((!out.instrument_id || out.instrument_id ===
                                                    '') && DSL_AVAILABLE && Array.isArray(
                                                    DSL_AVAILABLE.instruments)) {
                                                try {
                                                    var wrapperText = instHidden
                                                        .parentElement.innerText || '';
                                                    wrapperText = wrapperText.trim();
                                                    if (wrapperText) {
                                                        var match = DSL_AVAILABLE
                                                            .instruments.find(function(i) {
                                                                return String(i.name ||
                                                                        '').trim() ===
                                                                    String(wrapperText)
                                                                    .trim();
                                                            }) || null;
                                                        if (match) out.instrument_id = match
                                                            .id;
                                                    }
                                                } catch (e) {}
                                            }
                                        }
                                    } catch (e) {}
                                    try {
                                        if ((!out.eyepiece_id || out.eyepiece_id === '') &&
                                            epHidden) {
                                            var wrapper2 = document.querySelector(
                                                    '[data-dsl-field="eyepiece"]') ||
                                                epHidden.parentElement || null;
                                            var s2 = wrapper2 ? wrapper2.querySelector(
                                                'select') : null;
                                            if (s2 && s2.value) out.eyepiece_id = s2.value;
                                            if ((!out.eyepiece_id || out.eyepiece_id ===
                                                    '')) {
                                                var control2 = wrapper2 ? wrapper2
                                                    .querySelector(
                                                        'input[type="text"], [role="combobox"], input'
                                                    ) : null;
                                                try {
                                                    if (control2 && control2.value) out
                                                        .eyepiece_id = control2.value;
                                                } catch (e) {}
                                            }
                                            if ((!out.eyepiece_id || out.eyepiece_id ===
                                                    '') && DSL_AVAILABLE && Array.isArray(
                                                    DSL_AVAILABLE.eyepieces)) {
                                                try {
                                                    var w2 = epHidden.parentElement
                                                        .innerText || '';
                                                    w2 = w2.trim();
                                                    if (w2) {
                                                        var m2 = DSL_AVAILABLE.eyepieces
                                                            .find(function(i) {
                                                                return String(i.name ||
                                                                        '').trim() ===
                                                                    String(w2).trim();
                                                            }) || null;
                                                        if (m2) out.eyepiece_id = m2.id;
                                                    }
                                                } catch (e) {}
                                            }
                                        }
                                    } catch (e) {}
                                    try {
                                        if ((!out.lens_id || out.lens_id === '') &&
                                            lnHidden) {
                                            var wrapper3 = document.querySelector(
                                                    '[data-dsl-field="lens"]') || lnHidden
                                                .parentElement || null;
                                            var s3 = wrapper3 ? wrapper3.querySelector(
                                                'select') : null;
                                            if (s3 && s3.value) out.lens_id = s3.value;
                                            if ((!out.lens_id || out.lens_id === '')) {
                                                var control3 = wrapper3 ? wrapper3
                                                    .querySelector(
                                                        'input[type="text"], [role="combobox"]'
                                                    ) : null;
                                                try {
                                                    if (control3 && control3.value) out
                                                        .lens_id = control3.value;
                                                } catch (e) {}
                                            }
                                            if ((!out.lens_id || out.lens_id === '') &&
                                                DSL_AVAILABLE && Array.isArray(DSL_AVAILABLE
                                                    .lenses)) {
                                                try {
                                                    var w3 = lnHidden.parentElement
                                                        .innerText || '';
                                                    w3 = w3.trim();
                                                    if (w3) {
                                                        var m3 = DSL_AVAILABLE.lenses.find(
                                                            function(i) {
                                                                return String(i.name ||
                                                                        '').trim() ===
                                                                    String(w3).trim();
                                                            }) || null;
                                                        if (m3) out.lens_id = m3.id;
                                                    }
                                                } catch (e) {}
                                            }
                                        }
                                    } catch (e) {}
                                } catch (e) {}

                                return out;
                            })();
                            var saveUrl = document.getElementById('aladin-lite-container') ?
                                document.getElementById('aladin-lite-container').getAttribute(
                                    'data-save-url') : '/api/user/aladin-defaults';
                            fetch(saveUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]') ? document
                                            .querySelector('meta[name="csrf-token"]')
                                            .getAttribute('content') : ''
                                    },
                                    body: JSON.stringify(payload),
                                    credentials: 'same-origin'
                                })
                                .then(function(r) {
                                    // capture and log non-OK responses for debugging
                                    return r.text().then(function(txt) {
                                        var parsed = null;
                                        try {
                                            parsed = txt ? JSON.parse(txt) : null;
                                        } catch (e) {
                                            parsed = txt;
                                        }
                                        try {
                                            // suppressed debug logging
                                        } catch (e) {}
                                        return parsed;
                                    });
                                })
                                .then(function(j) {
                                    if (j && j.ok) {
                                        showDslToast(DSL_TEXT.saved || 'Saved');
                                    } else {
                                        // show error message from server if present
                                        try {
                                            if (j && j.error) {
                                                showDslToast(j.error || (DSL_TEXT.save_failed ||
                                                    'Save failed'));
                                            } else {
                                                showDslToast(DSL_TEXT.save_failed ||
                                                    'Save failed');
                                            }
                                        } catch (e) {
                                            showDslToast(DSL_TEXT.save_failed || 'Save failed');
                                        }
                                    }
                                }).catch(function(e) {
                                    showDslToast(DSL_TEXT.save_failed || 'Save failed');
                                }).finally(function() {
                                    saveBtn.disabled = false;
                                    saveBtn.textContent = (DSL_TEXT && DSL_TEXT.save) ? DSL_TEXT
                                        .save : 'Save';
                                });
                        } catch (e) {
                            showDslToast(DSL_TEXT.save_failed || 'Save failed');
                            saveBtn.disabled = false;
                            saveBtn.textContent = (DSL_TEXT && DSL_TEXT.save) ? DSL_TEXT.save :
                                'Save';
                        }
                    });
                }
                // rely on server-side selected attributes for initial selection; updates handled by change listeners
                // apply once to ensure preview matches initial selects (if any)
                scheduleApplyAladinSelectsUpdate();

                // No client-side heuristics: initial state is set server-side and x-on-selected handlers
                // update hidden inputs and trigger Aladin updates on user interaction.
            } catch (e) {}
        });

        // Diagnostics removed: pointer-to-mouse shim and temporary console logging were used during debugging
        // and have been cleaned up. Pan shim remains active below.

        // Minimal toast helper
        function showDslToast(msg) {
            try {
                var id = 'dsl-toast';
                var el = document.getElementById(id);
                if (!el) {
                    el = document.createElement('div');
                    el.id = id;
                    el.style.position = 'fixed';
                    el.style.right = '16px';
                    el.style.bottom = '16px';
                    el.style.zIndex = 9999;
                    document.body.appendChild(el);
                }
                var t = document.createElement('div');
                t.style.background = 'rgba(0,0,0,0.8)';
                t.style.color = 'white';
                t.style.padding = '8px 12px';
                t.style.borderRadius = '6px';
                t.style.marginTop = '8px';
                t.style.fontSize = '13px';
                t.textContent = msg;
                el.appendChild(t);
                setTimeout(function() {
                    try {
                        t.style.transition = 'opacity 300ms';
                        t.style.opacity = '0';
                        setTimeout(function() {
                            try {
                                el.removeChild(t);
                            } catch (e) {}
                        }, 320);
                    } catch (e) {}
                }, 2400);
            } catch (e) {}
        }

        // Ensure the Aladin script is loaded dynamically to avoid timing / init conflicts
        function ensureAladinScriptLoaded(cb) {
            var aladinSrc = 'https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.js';
            var jQuerySrc = 'https://code.jquery.com/jquery-3.6.0.min.js';
            if (window.A && typeof window.A.aladin === 'function') {
                return cb();
            }

            function injectScript(src, attrName, onload, onerror) {
                var existing = document.querySelector('script[' + attrName + ']');

                if (existing) {
                    existing.addEventListener('load', function() {
                        onload && onload();
                    });
                    existing.addEventListener('error', function(e) {
                        onerror && onerror(e);
                    });
                    return existing;
                }
                var s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.setAttribute(attrName, '1');
                s.onload = function() {
                    onload && onload();
                };
                s.onerror = function(e) {
                    console.error('Failed to load ' + src, e);
                    onerror && onerror(e);
                };
                document.head.appendChild(s);
                return s;
            }

            // Ensure jQuery is present before Aladin (Aladin expects jQuery)
            function loadAladinAfterJQuery() {
                // If Aladin already added, wait for it
                var existingAl = document.querySelector('script[data-aladin-loader]');
                if (existingAl) {
                    var tries = 0;
                    var poll = setInterval(function() {
                        if (window.A && typeof window.A.aladin === 'function') {
                            clearInterval(poll);
                            cb();
                        }
                        tries++;
                        if (tries > 100) {
                            clearInterval(poll);
                            console.error('Aladin not available after waiting');
                            cb();
                        }
                    }, 200);
                    return;
                }
                injectScript(aladinSrc, 'data-aladin-loader', function() {
                    // give Aladin a short moment to setup globals
                    setTimeout(function() {
                        if (window.A && typeof window.A.aladin === 'function') cb();
                        else cb();
                    }, 50);
                }, function() {
                    cb();
                });
            }

            if (!window.jQuery) {
                // inject jQuery, then aladin
                injectScript(jQuerySrc, 'data-jquery-loader', function() {

                    loadAladinAfterJQuery();
                }, function(e) {
                    console.error('Failed to load jQuery, attempting to load Aladin anyway', e);
                    loadAladinAfterJQuery();
                });
            } else {
                loadAladinAfterJQuery();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                ensureAladinScriptLoaded(initAladin);
            });
        } else {
            ensureAladinScriptLoaded(initAladin);
        }
        // Listen for Livewire-emitted events and update hidden inputs + trigger Aladin update
        if (typeof Livewire !== 'undefined' && Livewire.on) {
            Livewire.on('aladin-selects-changed', function(d) {
                try {
                    d = d || {};
                    if (typeof d.instrument !== 'undefined') document.getElementById(
                        'aladin-instrument-hidden').value = d.instrument || '';
                    if (typeof d.eyepiece !== 'undefined') document.getElementById('aladin-eyepiece-hidden')
                        .value = d.eyepiece || '';
                    if (typeof d.lens !== 'undefined') document.getElementById('aladin-lens-hidden').value =
                        d.lens || '';
                    // update labels
                    try {
                        document.getElementById('aladin-instrument-selected-label').textContent = (d
                            .instrument && DSL_AVAILABLE.instruments) ? ((DSL_AVAILABLE.instruments
                            .find(function(i) {
                                return String(i.id) === String(d.instrument);
                            }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    try {
                        document.getElementById('aladin-eyepiece-selected-label').textContent = (d
                            .eyepiece && DSL_AVAILABLE.eyepieces) ? ((DSL_AVAILABLE.eyepieces.find(
                            function(i) {
                                return String(i.id) === String(d.eyepiece);
                            }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    try {
                        document.getElementById('aladin-lens-selected-label').textContent = (d.lens &&
                            DSL_AVAILABLE.lenses) ? ((DSL_AVAILABLE.lenses.find(function(i) {
                            return String(i.id) === String(d.lens);
                        }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate();
                    // Also dispatch a recalc payload so the preview component updates immediately.
                    try {
                        var payload = {
                            instrument: d.instrument || null,
                            eyepiece: d.eyepiece || null,
                            lens: d.lens || null,
                            objectId: (document.getElementById('aladin-lite-container') && document
                                    .getElementById('aladin-lite-container').getAttribute) ? document
                                .getElementById('aladin-lite-container').getAttribute(
                                    'data-object-id') : (window.__dsl_server_selected && window
                                    .__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null
                        };
                        if (payload.objectId === '') payload.objectId = null;
                        try {
                            console.debug('[dsl] attempting recalc dispatchTo ->', payload);
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try {
                                    Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                                    console.debug('[dsl] dispatchTo invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] dispatchTo threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] dispatchTo outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via __dsl_emitAladinUpdated ->',
                                payload);
                        } catch (e) {}
                        try {
                            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                try {
                                    window.__dsl_emitAladinUpdated(payload);
                                    console.debug('[dsl] __dsl_emitAladinUpdated invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] __dsl_emitAladinUpdated threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] __dsl_emitAladinUpdated outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via Livewire.dispatch ->', payload);
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try {
                                    Livewire.dispatch('aladinUpdated', payload);
                                    console.debug('[dsl] Livewire.dispatch invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] Livewire.dispatch threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] Livewire.dispatch outer threw', e);
                        }
                        // Aggressive fallback: attempt to find a mounted aladin-preview-info instance by wire:id and call directly
                        try {
                            try {
                                console.debug('[dsl] attempting Livewire.find fallback');
                            } catch (e) {}
                            var rootEl = document.getElementById('dsl-aladin-preview-info');
                            var foundId = null;
                            if (rootEl) {
                                foundId = rootEl.getAttribute('wire:id') || rootEl.getAttribute(
                                    'data-wired-id') || null;
                                if (!foundId) {
                                    var nested = rootEl.querySelector('[wire\\:id]');
                                    if (nested) foundId = nested.getAttribute('wire:id') || null;
                                }
                            }
                            if (foundId && window.Livewire && typeof Livewire.find === 'function') {
                                try {
                                    Livewire.find(foundId).call('recalculate', payload);
                                    console.debug('[dsl] Livewire.find called recalculate on', foundId);
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] Livewire.find.call threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] Livewire.find fallback outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via DOM event ->', payload);
                        } catch (e) {}
                        try {
                            window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                                detail: payload
                            }));
                            console.debug('[dsl] DOM event dispatched');
                        } catch (e) {
                            console.debug('[dsl] DOM event dispatch threw', e);
                        }
                    } catch (e) {
                        try {
                            console.debug('[dsl] failed to build/dispatch payload', e);
                        } catch (_) {}
                    }
                } catch (e) {}
            });
            // Also hook into Livewire's lifecycle hook to ensure DOM updates (including select widgets)
            // are synced into our hidden inputs. Many Livewire installations expose Livewire.hook.
            try {
                if (typeof Livewire.hook === 'function') {
                    Livewire.hook('message.processed', function() {
                        try {
                            // Force-sync visible selects/controls into hidden inputs so applyAladinSelectsUpdate
                            // computes FOV.
                            ['instrument', 'eyepiece', 'lens'].forEach(function(k) {
                                try {
                                    var hidden = document.getElementById('aladin-' + k + '-hidden');
                                    if (!hidden) return;
                                    var wrapper = document.querySelector('[data-dsl-field="' + k +
                                        '"]') || hidden.parentElement || null;
                                    var sel = wrapper ? wrapper.querySelector('select') : null;
                                    var v = '';
                                    if (sel) {
                                        try {
                                            if (sel.tom && typeof sel.tom.getValue === 'function')
                                                v = sel.tom.getValue();
                                        } catch (e) {}
                                        if (!v) v = sel.value || (sel.dataset && sel.dataset
                                            .tsValue) || '';
                                    }
                                    if (!v && wrapper) {
                                        var control = wrapper.querySelector(
                                            'input[type="text"], [role="combobox"], input');
                                        if (control) try {
                                            v = control.value || '';
                                        } catch (e) {}
                                    }
                                    if (v !== undefined && hidden.value !== v) {
                                        hidden.value = v;
                                    }
                                } catch (e) {}
                            });
                            try {
                                updateSelectedLabels();
                            } catch (e) {}
                            try {
                                if (window.scheduleApplyAladinSelectsUpdate) window
                                    .scheduleApplyAladinSelectsUpdate();
                            } catch (e) {}
                        } catch (e) {}
                    });
                }
            } catch (e) {}
        } else {
            // Fallback to window event listener if Livewire not available yet
            window.addEventListener('aladin-selects-changed', function(e) {
                try {
                    var d = e.detail || {};
                    if (typeof d.instrument !== 'undefined') document.getElementById(
                        'aladin-instrument-hidden').value = d.instrument || '';
                    if (typeof d.eyepiece !== 'undefined') document.getElementById('aladin-eyepiece-hidden')
                        .value = d.eyepiece || '';
                    if (typeof d.lens !== 'undefined') document.getElementById('aladin-lens-hidden').value =
                        d.lens || '';
                    try {
                        document.getElementById('aladin-instrument-selected-label').textContent = (d
                            .instrument && DSL_AVAILABLE.instruments) ? ((DSL_AVAILABLE.instruments
                            .find(function(i) {
                                return String(i.id) === String(d.instrument);
                            }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    try {
                        document.getElementById('aladin-eyepiece-selected-label').textContent = (d
                            .eyepiece && DSL_AVAILABLE.eyepieces) ? ((DSL_AVAILABLE.eyepieces.find(
                            function(i) {
                                return String(i.id) === String(d.eyepiece);
                            }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    try {
                        document.getElementById('aladin-lens-selected-label').textContent = (d.lens &&
                            DSL_AVAILABLE.lenses) ? ((DSL_AVAILABLE.lenses.find(function(i) {
                            return String(i.id) === String(d.lens);
                        }) || {}).name || '(none)') : (DSL_TEXT && DSL_TEXT.none_label ? DSL_TEXT
                            .none_label : '(none)');
                    } catch (e) {}
                    if (window.scheduleApplyAladinSelectsUpdate) window.scheduleApplyAladinSelectsUpdate();
                    // Also dispatch recalc so preview updates
                    try {
                        var payload2 = {
                            instrument: d.instrument || null,
                            eyepiece: d.eyepiece || null,
                            lens: d.lens || null,
                            objectId: (document.getElementById('aladin-lite-container') && document
                                    .getElementById('aladin-lite-container').getAttribute) ? document
                                .getElementById('aladin-lite-container').getAttribute(
                                    'data-object-id') : (window.__dsl_server_selected && window
                                    .__dsl_server_selected.objectId) || window
                                .__dsl_embedded_objectId || null
                        };
                        if (payload2.objectId === '') payload2.objectId = null;
                        try {
                            console.debug('[dsl] attempting recalc dispatchTo ->', payload2);
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                try {
                                    Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload2);
                                    console.debug('[dsl] dispatchTo invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] dispatchTo threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] dispatchTo outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via __dsl_emitAladinUpdated ->',
                                payload2);
                        } catch (e) {}
                        try {
                            if (typeof window.__dsl_emitAladinUpdated === 'function') {
                                try {
                                    window.__dsl_emitAladinUpdated(payload2);
                                    console.debug('[dsl] __dsl_emitAladinUpdated invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] __dsl_emitAladinUpdated threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] __dsl_emitAladinUpdated outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via Livewire.dispatch ->', payload2);
                        } catch (e) {}
                        try {
                            if (window.Livewire && typeof Livewire.dispatch === 'function') {
                                try {
                                    Livewire.dispatch('aladinUpdated', payload2);
                                    console.debug('[dsl] Livewire.dispatch invoked');
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] Livewire.dispatch threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] Livewire.dispatch outer threw', e);
                        }
                        // Aggressive fallback: attempt to find mounted component and call directly
                        try {
                            try {
                                console.debug('[dsl] attempting Livewire.find fallback');
                            } catch (e) {}
                            var rootEl2 = document.getElementById('dsl-aladin-preview-info');
                            var foundId2 = null;
                            if (rootEl2) {
                                foundId2 = rootEl2.getAttribute('wire:id') || rootEl2.getAttribute(
                                    'data-wired-id') || null;
                                if (!foundId2) {
                                    var nested2 = rootEl2.querySelector('[wire\\:id]');
                                    if (nested2) foundId2 = nested2.getAttribute('wire:id') || null;
                                }
                            }
                            if (foundId2 && window.Livewire && typeof Livewire.find === 'function') {
                                try {
                                    Livewire.find(foundId2).call('recalculate', payload2);
                                    console.debug('[dsl] Livewire.find called recalculate on', foundId2);
                                    return;
                                } catch (e) {
                                    console.debug('[dsl] Livewire.find.call threw', e);
                                }
                            }
                        } catch (e) {
                            console.debug('[dsl] Livewire.find fallback outer threw', e);
                        }
                        try {
                            console.debug('[dsl] attempting recalc via DOM event ->', payload2);
                        } catch (e) {}
                        try {
                            window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                                detail: payload2
                            }));
                            console.debug('[dsl] DOM event dispatched');
                        } catch (e) {
                            console.debug('[dsl] DOM event dispatch threw', e);
                        }
                    } catch (e) {
                        try {
                            console.debug('[dsl] failed to build/dispatch payload2', e);
                        } catch (_) {}
                    }
                } catch (e) {}
            });
        }
    })();
</script>

<input type="hidden" id="object-id-hidden" value="{{ $session->id ?? '' }}">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const objectIdFromHidden = (document.getElementById('object-id-hidden') && document.getElementById(
            'object-id-hidden').value) || null;

        function buildAladinPayload(extra) {
            extra = extra || {};
            var payload = {};
            try {
                var oid = null;
                try {
                    oid = document.getElementById('aladin-lite-container')?.getAttribute('data-object-id') ||
                        null;
                } catch (e) {
                    oid = null;
                }
                if (!oid || String(oid).trim() === '') oid = objectIdFromHidden || (window
                        .__dsl_server_selected && window.__dsl_server_selected.objectId) || window
                    .__dsl_embedded_objectId || null;
                if (oid === '') oid = null;
                payload.objectId = oid;
            } catch (e) {
                payload.objectId = objectIdFromHidden || null;
            }
            try {
                payload.objectSlug = document.getElementById('aladin-lite-container')?.getAttribute(
                    'data-slug') || null;
            } catch (e) {
                payload.objectSlug = null;
            }
            try {
                payload.instrument = document.getElementById('aladin-instrument-hidden')?.value || null;
            } catch (e) {
                payload.instrument = null;
            }
            try {
                payload.eyepiece = document.getElementById('aladin-eyepiece-hidden')?.value || null;
            } catch (e) {
                payload.eyepiece = null;
            }
            try {
                payload.lens = document.getElementById('aladin-lens-hidden')?.value || null;
            } catch (e) {
                payload.lens = null;
            }
            // mark as auto-init so server logs can recognise
            payload.__dsl_auto_init = true;
            try {
                for (var k in extra) {
                    if (Object.prototype.hasOwnProperty.call(extra, k)) payload[k] = extra[k];
                }
            } catch (e) {}
            return payload;
        }

        function tryDispatchInitialRecalc(attempt) {
            attempt = attempt || 0;
            var payload = buildAladinPayload();
            try {
                // Prefer direct Livewire v3 API to target the component instance
                if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                    try {
                        Livewire.dispatchTo('aladin-preview-info', 'setObjectId', payload.objectId);
                    } catch (e) {}
                    try {
                        Livewire.dispatchTo('aladin-preview-info', 'recalculate', payload);
                        return true;
                    } catch (e) {}
                }
                // If our central emitter exists prefer it (it will queue/enrich if needed)
                if (typeof window.__dsl_emitAladinUpdated === 'function') {
                    window.__dsl_emitAladinUpdated(payload);
                    return true;
                }
                // Fallback to Livewire.dispatch event if available
                if (window.Livewire && typeof Livewire.dispatch === 'function') {
                    try {
                        Livewire.dispatch('aladinUpdated', payload);
                        return true;
                    } catch (e) {}
                }
                // Else dispatch a DOM event as a last resort
                try {
                    window.dispatchEvent(new CustomEvent('dsl-aladin-updated', {
                        detail: payload
                    }));
                    return true;
                } catch (e) {}
            } catch (e) {
                /* ignore */
            }
            // retry a few times with backoff if Livewire not yet ready
            if (attempt < 6) {
                setTimeout(function() {
                    tryDispatchInitialRecalc(attempt + 1);
                }, 150 + (attempt * 150));
            }
            return false;
        }

        // Ensure Livewire gets the objectId and an initial recalc; retry a few times to avoid races
        try {
            if (window.Livewire && (typeof Livewire.dispatchTo === 'function' || typeof Livewire.dispatch ===
                    'function')) {
                tryDispatchInitialRecalc(0);
            } else {
                window.addEventListener('livewire:load', function() {
                    try {
                        tryDispatchInitialRecalc(0);
                    } catch (e) {}
                });
                // also try after a short delay in case Livewire is present but event already fired
                setTimeout(function() {
                    tryDispatchInitialRecalc(0);
                }, 800);
            }
        } catch (e) {
            setTimeout(function() {
                tryDispatchInitialRecalc(0);
            }, 800);
        }
    });
</script>
