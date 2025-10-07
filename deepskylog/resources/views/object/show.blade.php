<x-app-layout>
    <div>
        <div class="mx-auto max-w-7xl bg-gray-900 px-4 py-6 sm:px-4 lg:px-6">
            <header class="mb-6">
                <h1 class="text-3xl font-extrabold">{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</h1>
                <p class="text-sm flex items-center gap-2 text-gray-300 mt-2">
                    <span class="text-gray-400">{{ __('Object type') }}</span>
                    <span class="text-white font-medium ml-2">{{ $session->source_type ?? __('Unknown') }}</span>
                    @if(!empty($session->constellation))
                        <span class="text-gray-400 ml-4">{{ __('Constellation') }}:</span>
                        <span class="text-white font-medium ml-2">{{ $session->constellation }}</span>
                    @endif
                </p>
            </header>

            <div class="grid md:grid-cols-3 gap-4">
                <article class="md:col-span-2">
                    @if(!empty($image))
                        <img class="w-full rounded shadow mb-3" src="{{ $image }}" alt="{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}">
                    @endif

                    <div class="mb-4 text-gray-100">
                        <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                        <table class="table-auto w-full text-sm text-gray-100">
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Name') }}</td>
                                <td>
                                    @php
                                        // Prefer canonicalSlug provided by controller, fall back to session.slug or slugified name
                                        $primarySlug = $canonicalSlug ?? ($session->slug ? $session->slug : \Illuminate\Support\Str::slug($session->name ?? '', '-'));
                                    @endphp
                                    <a href="{{ route('object.show', ['slug' => $primarySlug]) }}" class="font-bold text-white hover:underline">{{ $session->name }}</a>
                                </td>
                            </tr>
                            @if(!empty($alternatives) && is_array($alternatives) && count($alternatives) > 0)
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Also known as') }}</td>
                                    <td>
                                        @php
                                            // Render alternatives as a comma-separated list with exactly one space after comma.
                                            $altLinks = [];
                                            foreach ($alternatives as $alt) {
                                                $altSlug = \Illuminate\Support\Str::slug($alt, '-');
                                                $url = route('object.show', ['slug' => $altSlug]);
                                                $altLinks[] = '<a href="'.e($url).'" class="text-gray-300 hover:underline">'.e($alt).'</a>';
                                            }
                                        @endphp
                                        {!! implode(', ', $altLinks) !!}
                                    </td>
                                </tr>
                            @endif
                            @if(isset($session->ra) && isset($session->decl))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('RA / Dec') }}</td>
                                    <td>{{ $session->ra }} / {{ $session->decl }}</td>
                                </tr>
                                @if(!empty($atlasName) || !empty($atlasPage))
                                    <tr>
                                        <td class="pr-4 font-medium">
                                            @if(!empty($atlasName))
                                                {{ $atlasName }}
                                            @endif
                                            @if(!empty($atlasPage))
                                                @if(!empty($atlasName))
                                                    {{ ' ' }}
                                                @endif
                                                {{ __('page:') }}
                                            @endif                                            
                                        </td>
                                        <td>
                                            @if(!empty($atlasPage))
                                                {{ $atlasPage }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            @if(isset($session->diam1) || isset($session->diam2))
                                @if($session->diam1 > 0.0)
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Size') }}</td>
                                    <td>
                                        @php
                                            $d1 = $session->diam1 ?? '';
                                            $d2 = $session->diam2 ?? '';
                                        @endphp
                                        {{ $d1 }}' @if(!empty($d1) && !empty($d2)) x @endif {{ $d2 }}'
                                    </td>
                                </tr>
                                @endif
                            @endif

                            @if(!empty($session->pa))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Position angle') }}</td>
                                    <td>{{ $session->pa }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="pr-4 font-medium">{{ __('Description') }}</td>
                                <td>{!! nl2br(e($session->comments ?? '')) !!}</td>
                            </tr>
                            @if(!empty($session->mag))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Magnitude') }}</td>
                                    <td>{{ $session->mag ?? '' }}</td>
                                </tr>
                            @endif

                            @if(!empty($session->subr))
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Surface brightness') }}</td>
                                    <td>{{ $session->subr ?? '' }}</td>
                                </tr>
                            @endif

                            @if(isset($session->contrast_reserve))
                                <tr>
                                    <td class="pr-4 font-medium">
                                        <span>{{ __('Contrast reserve') }}</span>
                                        <div x-data="{ open: false }" class="inline-block relative">
                                            <button @click.prevent="open = !open" @keydown.escape="open = false" :aria-expanded="open.toString()" aria-haspopup="true" class="ml-2 text-gray-400 hover:text-gray-200 focus:outline-none" type="button">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-8 4a1 1 0 100 2 1 1 0 000-2zm.75-6.75a.75.75 0 00-1.5 0V10a.75.75 0 001.5 0V7.25z" clip-rule="evenodd"/></svg>
                                            </button>

                                            <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute left-0 mt-2 w-64 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg">
                                                <div class="font-semibold mb-2">{{ __('contrast.reserve.tooltip_title') }}</div>
                                                <ul class="text-xs space-y-1">
                                                    <li>{{ __('contrast.reserve.category.very_easy') }}</li>
                                                    <li>{{ __('contrast.reserve.category.easy') }}</li>
                                                    <li>{{ __('contrast.reserve.category.quite_difficult') }}</li>
                                                    <li>{{ __('contrast.reserve.category.difficult') }}</li>
                                                    <li>{{ __('contrast.reserve.category.questionable') }}</li>
                                                    <li>{{ __('contrast.reserve.category.not_visible') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->contrast_reserve === null)
                                            {{ __('Unknown') }}
                                        @else
                                            @php
                                                $crCat = $session->contrast_reserve_category ?? null;
                                                $crClass = 'text-white';
                                                // Map categories to requested colors:
                                                if ($crCat === 'very_easy') {
                                                    $crClass = 'text-green-400';
                                                } elseif ($crCat === 'easy') {
                                                    $crClass = 'text-green-700';
                                                } elseif ($crCat === 'quite_difficult') {
                                                    $crClass = 'text-orange-400';
                                                } elseif ($crCat === 'difficult') {
                                                    $crClass = 'text-orange-700';
                                                } elseif ($crCat === 'questionable') {
                                                    $crClass = 'text-gray-300';
                                                } elseif ($crCat === 'not_visible') {
                                                    $crClass = 'text-gray-600';
                                                }

                                                $categoryText = $crCat ? __('contrast.reserve.category.' . $crCat) : __('Unknown');
                                            @endphp

                                            <div x-data="{ openCR: false }" class="inline-block relative">
                                                <button @click.prevent="openCR = !openCR" @keydown.escape="openCR = false" :aria-expanded="openCR.toString()" aria-haspopup="true" type="button" class="focus:outline-none {{ $crClass }} font-medium">
                                                    {{ $session->contrast_reserve }}
                                                </button>

                                                <div x-show="openCR" x-cloak @click.outside="openCR = false" x-transition class="absolute z-10 left-0 mt-2 w-80 p-3 bg-gray-800 text-sm text-gray-100 rounded shadow-lg">
                                                    <div class="text-sm mb-2">{{ __('contrast.reserve.summary', ['value' => $session->contrast_reserve, 'category' => $categoryText]) }}</div>
                                                    <div class="text-xs text-gray-300 mb-1"><strong>{{ __('Location') }}:</strong> {{ $session->contrast_used_location ?? __('Unknown') }}</div>
                                                    <div class="text-xs text-gray-300"><strong>{{ __('Instrument') }}:</strong> {{ $session->contrast_used_instrument ?? __('Unknown') }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            {{-- Additional object fields that were present previously: optimum magnification, eyepieces, size and position angle --}}
                            @if(!empty($session->optimum_detection_magnification))
                                @php
                                    $eps = [];
                                    foreach ($session->optimum_eyepieces as $ep) {
                                        $name = $ep['name'] ?? ($ep['label'] ?? null);
                                        $slug = $ep['slug'] ?? null;
                                        $userSlug = $ep['user_slug'] ?? null;
                                        $parts = [];
                                        if (! empty($name)) {
                                            if (! empty($slug) && ! empty($userSlug)) {
                                                // Build an internal route to the eyepiece show page
                                                $url = route('eyepiece.show', ['user' => $userSlug, 'eyepiece' => $slug]);
                                                $parts[] = '<a href="'.e($url).'" class="text-gray-300 hover:underline">'.e($name).'</a>';
                                            } else {
                                                $parts[] = e($name);
                                            }
                                        }
                                        if (! empty($parts)) { $eps[] = implode(' — ', $parts); }
                                    }
                                @endphp
                                <tr>
                                    <td class="pr-4 font-medium">{{ __('Optimum detection magnification') }}</td>
                                    <td>{{ $session->optimum_detection_magnification }}x - {!! implode(', ', $eps) !!}</td>
                                </tr>
                            @endif

                        </table>

                    </div>

                </article>

                                        <aside class="md:col-span-1">
                    <div class="bg-gray-800 p-3 rounded shadow text-gray-100">
                        <h4 class="font-semibold mb-2 text-white">{{ __('Quick links') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('session.all') }}" class="text-gray-300 hover:underline">{{ __('All sessions') }}</a></li>
                            <li><a href="{{ route('observations.index') }}" class="text-gray-300 hover:underline">{{ __('All observations') }}</a></li>
                            @php
                                // Prepare name and coordinates for external links
                                $objectName = $session->name ?? null;
                                $hasCoords = isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl);
                                // SIMBAD: prefer name search, otherwise use coordinates (format: %2B12+34+56+%2B12+34+56 not necessary here, use basic coords)
                                $simbadUrl = null;
                                $nedUrl = null;
                                $wikipediaUrl = null;

                                if ($objectName) {
                                    $encName = rawurlencode($objectName);
                                    $simbadUrl = "https://simbad.cds.unistra.fr/simbad/sim-id?Ident=$encName";
                                    $nedUrl = "https://ned.ipac.caltech.edu/byname?objname=$encName";
                                    $wikipediaUrl = "https://en.wikipedia.org/wiki/Special:Search?search=$encName";
                                    // Intentionally removed external Aladin/AstroBin link per request
                                }

                                        
                            @endphp

                            @if($simbadUrl || $nedUrl || $wikipediaUrl || $aladinUrl)
                                <li class="pt-2 border-t border-gray-700 text-xs text-gray-400">{{ __("External databases") }}</li>
                                @if($simbadUrl)
                                    <li>
                                        <a href="{{ $simbadUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- SIMBAD icon (simple star) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l2.39 4.85L19 8.27l-3.5 3.41L16.18 19 12 16.27 7.82 19l.68-7.32L4.999 8.27l4.61-.42L12 2z" fill="currentColor"/></svg>
                                            <span>SIMBAD</span>
                                        </a>
                                    </li>
                                @endif
                                @if($nedUrl)
                                    <li>
                                        <a href="{{ $nedUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- NED icon (globe) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 2.06v2.04a6.002 6.002 0 013.364 3.364H17A8 8 0 0013 4.06zM6.636 7.48A6.002 6.002 0 0111 4.1V2.06A8 8 0 006.636 7.48zM4.06 11H6.1a6.002 6.002 0 010 2H4.06A8 8 0 004.06 11zM6.636 16.52A8 8 0 0011 21.94v-2.04a6.002 6.002 0 01-4.364-3.38zM13 19.94v-2.04a6.002 6.002 0 01-3.364-3.364H11a8 8 0 002 5.404z" fill="currentColor"/></svg>
                                            <span>NED</span>
                                        </a>
                                    </li>
                                @endif
                                @if($wikipediaUrl)
                                    <li>
                                        <a href="{{ $wikipediaUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 text-gray-300 hover:text-white">
                                            <!-- Wikipedia icon (W) -->
                                            <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l2.5 4.9L19 8l-4 3.6L16 19 12 16.2 8 19l1-7.4L5 8l4.5-.9L12 2z" fill="currentColor"/></svg>
                                            <span>Wikipedia</span>
                                        </a>
                                    </li>
                                @endif
                                {{-- Aladin external link removed per user request --}}
                            @endif
                        </ul>
                    </div>
                    {{-- Aladin Lite preview --}}
                    @if(isset($session->ra) && isset($session->decl) && !empty($session->ra) && !empty($session->decl))
                        <div class="mt-4 bg-gray-800 p-3 rounded shadow text-gray-100">
                            <h4 class="font-semibold mb-2 text-white">{{ __('Sky preview') }}</h4>
                            <div id="aladin-lite-container" class="w-full h-64 rounded bg-black" style="min-height:240px;"
                                 data-aladin="{{ base64_encode(json_encode($aladinDefaults ?? [])) }}"
                                 data-ra="{{ e($session->ra ?? '') }}"
                                 data-dec="{{ e($session->decl ?? '') }}"
                                 data-name="{{ e($session->name ?? '') }}">
                                {{-- Aladin will render into this container --}}
                            </div>
                            <div id="aladin-legend" class="mt-2 text-sm text-gray-300 flex items-center gap-3">
                                <div class="text-xs text-gray-400">{{ __('FoV:') }}</div>
                                <div id="aladin-fov-label" class="text-xs text-gray-400">{{ __('FoV:') }}</div>
                                    <div id="aladin-fov" class="font-medium">—</div>
                                <div class="text-xs text-gray-400">{{ __('Magnification:') }}</div>
                                <div id="aladin-mag" class="font-medium">—</div>
                            </div>
                            <div class="text-xs text-gray-400 mt-2">{{ __('Aladin Lite preview (uses default eyepiece/instrument if available)') }}</div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Aladin Lite assets (inline so layout stack is not required) -->
<link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css">

<script>
    (function(){
        // Server-provided values will be read from the aladin container data- attributes
        var aladinDefaults = null;
        var sessionRa = null;
        var sessionDec = null;
        var sessionName = null;
        // Debug flag (toggle by setting data-debug="1" or "true" on #aladin-lite-container)
        var DSL_DEBUG = false;
        function dbg() {
            if (!DSL_DEBUG) return;
            try { if (console && typeof console.debug === 'function') console.debug.apply(console, arguments); }
            catch (e) {}
        }

    // Utility: try to parse "HH MM SS" RA or decimal into degrees
        function parseRaToDegrees(ra) {
            if (!ra) return null;
            if (!isNaN(Number(ra))) return Number(ra);
            var parts = ra.toString().trim().replace(/:/g,' ').split(/\s+/);
            if (parts.length === 3) {
                var h = Number(parts[0]);
                var m = Number(parts[1]);
                var s = Number(parts[2]);
                if (!isNaN(h) && !isNaN(m) && !isNaN(s)) {
                    return (h + m/60 + s/3600) * 15.0;
                }
            }
            return null;
        }

        function parseDecToDegrees(dec) {
            if (!dec) return null;
            if (!isNaN(Number(dec))) return Number(dec);
            var parts = dec.toString().trim().replace(/:/g,' ').replace(/\+/g,'').split(/\s+/);
            if (parts.length === 3) {
                var d = Number(parts[0]);
                var m = Number(parts[1]);
                var s = Number(parts[2]);
                if (!isNaN(d) && !isNaN(m) && !isNaN(s)) {
                    var sign = (dec.trim().charAt(0) === '-') ? -1 : 1;
                    return sign * (Math.abs(d) + m/60 + s/3600);
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
                    if (ep && inst && inst.focal_length_mm && ep.apparent_fov_deg) {
                        var mag = inst.fixedMagnification || (inst.focal_length_mm && ep.focal_length_mm ? inst.focal_length_mm / ep.focal_length_mm : null);
                        if (mag) { return Math.max(0.01, Number(ep.apparent_fov_deg) / mag); }
                    }
                    if (ep && inst && inst.focal_length_mm && ep.focal_length_mm) {
                        var mag2 = inst.focal_length_mm / ep.focal_length_mm;
                        if (mag2 && mag2 > 0) { return Math.max(0.01, 50.0 / mag2); }
                    }
                    // If no instrument/eyepiece defaults available, try using the object's diameter (arcminutes)
                    if (defaults.object_diam_arcmin && !isNaN(Number(defaults.object_diam_arcmin)) && Number(defaults.object_diam_arcmin) > 0) {
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
                try { if (typeof aladinInstance.setFov === 'function') aladinInstance.setFov(desiredDisplay); }
                catch (e) { /* ignore setFov failures */ }
            } catch (e) { /* ignore */ }
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
                    (function(idx){
                        setTimeout(function(){ try { setDisplayFovForVertical(aladinInstance, eyeFovDeg, paddingPx); } catch(e){} }, idx * delayMs);
                    })(i);
                }
            } catch (e) { }
        }

        // Final correction: after Aladin has fully initialized, read its reported FOV
        // and scale the horizontal FOV so the vertical (dec) FOV equals eyeFovDeg.
        function finalAdjustFovToMatchDec(aladinInstance, eyeFovDeg) {
            try {
                if (!aladinInstance || typeof eyeFovDeg !== 'number' || eyeFovDeg <= 0) return;
                setTimeout(function(){
                    try {
                        var f = aladinInstance.getFov && aladinInstance.getFov();
                        if (!f) return;
                        var vals = Array.isArray(f) ? f.map(function(v){ return Number(v); }).filter(function(n){ return !isNaN(n); }) : [Number(f)];
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
                        try { if (typeof aladinInstance.setFov === 'function') aladinInstance.setFov(desiredDisplay); } catch (e) {}
                        // one more quick re-check after the change
                        setTimeout(function(){
                            try {
                                var f2 = aladinInstance.getFov && aladinInstance.getFov();
                                dbg('finalAdjustFov: after setFov, getFov=', f2);
                            } catch (e) {}
                        }, 300);
                    } catch (e) { }
                }, 1200);
            } catch (e) { }
        }

        function formatFovLabel(deg) {
            var arcmin = deg * 60;
            return (Math.round(arcmin * 10)/10) + "' (" + (Math.round(deg*100)/100) + "°)";
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
                // Determine the main viewport element (canvas/img/background) to keep
                var viewport = null;
                try {
                    // Prefer canvas or img inside container
                    viewport = containerEl.querySelector('canvas, img');
                    if (!viewport) {
                        // fallback: pick the largest child element (likely the sky area)
                        var maxArea = 0;
                        Array.prototype.slice.call(containerEl.children).forEach(function(ch){
                            try {
                                var r = ch.getBoundingClientRect();
                                var area = (r.width || 0) * (r.height || 0);
                                if (area > maxArea) { maxArea = area; viewport = ch; }
                            } catch (e) {}
                        });
                    }
                } catch (e) { viewport = null; }

                // Hide all children except viewport and overlay DOM we created (ids: aladin-fov-dom)
                Array.prototype.slice.call(containerEl.children).forEach(function(ch){
                    try {
                        if (ch.id === 'aladin-fov-dom') return; // keep our overlay
                        if (viewport && (ch === viewport || ch.contains(viewport) || viewport.contains(ch))) return;
                        // hide everything else (toolbar, legends, etc.)
                        ch.style.display = 'none';
                    } catch (e) {}
                });

                // Add a minimal control container if not already present
                var ctrlId = 'dsl-aladin-minimal-controls';
                var existing = document.getElementById(ctrlId);
                if (existing) return; // already installed

                var ctrl = document.createElement('div');
                ctrl.id = ctrlId;
                ctrl.style.position = 'absolute';
                ctrl.style.right = '8px';
                ctrl.style.top = '50%';
                ctrl.style.transform = 'translateY(-50%)';
                ctrl.style.zIndex = 60;
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
                    return b;
                }

                var btnZoomIn = makeBtn('+', 'Zoom in');
                var btnZoomOut = makeBtn('\u2212', 'Zoom out');
                var btnFullscreen = makeBtn('\u21f2', 'Fullscreen');
                var btnSave = makeBtn('\u2b73', 'Open Aladin in new tab');

                // Bind actions with fallbacks depending on available Aladin API
                btnZoomIn.addEventListener('click', function(){
                    try {
                        if (aladinInstance && typeof aladinInstance.getZoom === 'function' && typeof aladinInstance.setZoom === 'function') {
                            try { aladinInstance.setZoom(aladinInstance.getZoom() + 1); return; } catch(e){}
                        }
                        if (aladinInstance && typeof aladinInstance.zoomIn === 'function') { aladinInstance.zoomIn(); return; }
                        if (aladinInstance && typeof aladinInstance.getFov === 'function' && typeof aladinInstance.setFov === 'function') {
                            var f = aladinInstance.getFov();
                            if (f && f.length) f = Number(f[0]);
                            else f = Number(f) || 1.0;
                            aladinInstance.setFov(Math.max(0.01, f / 1.4));
                            return;
                        }
                    } catch (e) { console.error('Zoom in error', e); }
                });
                btnZoomOut.addEventListener('click', function(){
                    try {
                        if (aladinInstance && typeof aladinInstance.getZoom === 'function' && typeof aladinInstance.setZoom === 'function') {
                            try { aladinInstance.setZoom(Math.max(0, aladinInstance.getZoom() - 1)); return; } catch(e){}
                        }
                        if (aladinInstance && typeof aladinInstance.zoomOut === 'function') { aladinInstance.zoomOut(); return; }
                        if (aladinInstance && typeof aladinInstance.getFov === 'function' && typeof aladinInstance.setFov === 'function') {
                            var f = aladinInstance.getFov();
                            if (f && f.length) f = Number(f[0]);
                            else f = Number(f) || 1.0;
                            aladinInstance.setFov(Math.min(180, f * 1.4));
                            return;
                        }
                    } catch (e) { console.error('Zoom out error', e); }
                });
                btnFullscreen.addEventListener('click', function(){
                    try {
                        var el = containerEl;
                        if (!el) return;
                        if (document.fullscreenElement) {
                            document.exitFullscreen();
                        } else if (el.requestFullscreen) {
                            el.requestFullscreen();
                        } else if (el.webkitRequestFullscreen) { el.webkitRequestFullscreen(); }
                    } catch (e) { console.error('Fullscreen toggle failed', e); }
                });

                btnSave.addEventListener('click', function(){
                    try {
                        // First: try to find a canvas inside the container and download its data
                        var container = containerEl || document.getElementById('aladin-lite-container');
                        var filenameBase = (sessionName || 'aladin').toString().replace(/[^a-z0-9-_\.]/ig,'_');
                        var filename = filenameBase + '.png';
                        var done = false;
                        try {
                            var cvs = container.querySelector('canvas');
                            if (cvs && typeof cvs.toDataURL === 'function') {
                                var dataUrl = cvs.toDataURL('image/png');
                                // create download link
                                var link = document.createElement('a');
                                link.href = dataUrl;
                                link.download = filename;
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                                done = true;
                                return;
                            }
                        } catch (e) { /* continue to next method */ }

                        // Second: try to find an image element inside the container and fetch it
                        try {
                            var img = container.querySelector('img');
                            if (img && img.src) {
                                var src = img.src;
                                // If it is a data URL, download directly
                                if (src.indexOf('data:') === 0) {
                                    var link2 = document.createElement('a');
                                    link2.href = src;
                                    link2.download = filename;
                                    document.body.appendChild(link2);
                                    link2.click();
                                    document.body.removeChild(link2);
                                    done = true;
                                    return;
                                }
                                // Otherwise fetch the resource as blob then download
                                fetch(src, { mode: 'cors' }).then(function(resp){
                                    if (!resp.ok) throw new Error('Fetch failed');
                                    return resp.blob();
                                }).then(function(blob){
                                    var url = URL.createObjectURL(blob);
                                    var l = document.createElement('a');
                                    l.href = url;
                                    l.download = filename;
                                    document.body.appendChild(l);
                                    l.click();
                                    document.body.removeChild(l);
                                    setTimeout(function(){ URL.revokeObjectURL(url); }, 2000);
                                }).catch(function(err){
                                    // fallback to opening full aladin
                                    try { openFullAladin(); } catch(e) {}
                                });
                                return;
                            }
                        } catch (e) { /* continue */ }

                        // Final fallback: open full Aladin page for manual save
                        function openFullAladin() {
                            var targetUrl = null;
                            if (sessionName) {
                                targetUrl = 'https://aladin.u-strasbg.fr/AladinLite/?target=' + encodeURIComponent(sessionName);
                            } else if (typeof centerRaDeg !== 'undefined' && centerRaDeg !== null && typeof centerDecDeg !== 'undefined' && centerDecDeg !== null) {
                                targetUrl = 'https://aladin.u-strasbg.fr/AladinLite/?target=' + encodeURIComponent((centerRaDeg/15).toString() + ' ' + centerDecDeg.toString());
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

                // Append buttons
                ctrl.appendChild(btnZoomIn);
                ctrl.appendChild(btnZoomOut);
                ctrl.appendChild(btnFullscreen);
                ctrl.appendChild(btnSave);

                // Ensure container is positioned
                containerEl.style.position = containerEl.style.position || 'relative';
                containerEl.appendChild(ctrl);
            } catch (e) { console.error('pruneAladinControls error', e); }
        }

        function initAladin() {
            var container = document.getElementById('aladin-lite-container');
            if (!container) return;

            try {
                var raw = container.getAttribute('data-aladin');
                if (raw) {
                    var decoded = atob(raw);
                    aladinDefaults = JSON.parse(decoded || '{}');
                }
            } catch (e) { aladinDefaults = null; }
            sessionRa = container.getAttribute('data-ra') || null;
            sessionDec = container.getAttribute('data-dec') || null;
            sessionName = container.getAttribute('data-name') || null;

            var centerRaDeg = null;
            var centerDecDeg = null;
            if (aladinDefaults && aladinDefaults.ra_deg && aladinDefaults.dec_deg) {
                centerRaDeg = aladinDefaults.ra_deg;
                centerDecDeg = aladinDefaults.dec_deg;
            }
            if (!centerRaDeg && sessionRa) { centerRaDeg = parseRaToDegrees(sessionRa); }
            if (!centerDecDeg && sessionDec) { centerDecDeg = parseDecToDegrees(sessionDec); }

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
                            displayFovDeg = Math.max(0.01, Math.max(displayFovDeg, Math.max(displayFit, fovUsedDeg + 0.01)));
                        }
                }
            } catch (e) { /* non-fatal; keep displayFovDeg as computed above */ }
            var magUsed = null;
            try {
                if (aladinDefaults) {
                    var inst = aladinDefaults.instrument || null;
                    var ep = aladinDefaults.eyepiece || null;
                    if (inst && ep) {
                        magUsed = inst.fixedMagnification ? Number(inst.fixedMagnification) : (inst.focal_length_mm && ep.focal_length_mm ? Number(inst.focal_length_mm)/Number(ep.focal_length_mm) : null);
                        if (magUsed && ep.apparent_fov_deg) {
                            fovUsedDeg = Math.max(0.01, Number(ep.apparent_fov_deg) / magUsed);
                        } else if (magUsed) {
                            fovUsedDeg = Math.max(0.01, 50.0 / magUsed);
                        }
                    }
                }
            } catch (e) { magUsed = null; }

            // Always update legend if present; show both the used/source FoV and the live Aladin FoV when available
            var fovEl = document.getElementById('aladin-fov');
            var magEl = document.getElementById('aladin-mag');
            var fovLabelEl = document.getElementById('aladin-fov-label');
            try {
                var baseLabel = {!! json_encode(__('FoV')) !!};
            } catch (e) { var baseLabel = 'FoV'; }
            var sourceSuffix = '';
            try {
                if (typeof __dslFovUsedObjectDiameter !== 'undefined' && __dslFovUsedObjectDiameter) {
                    sourceSuffix = {!! json_encode(__('(object size)')) !!};
                } else if (aladinDefaults && aladinDefaults.eyepiece && aladinDefaults.instrument) {
                    sourceSuffix = {!! json_encode(__('(eyepiece)')) !!};
                } else if (aladinDefaults && aladinDefaults.instrument) {
                    sourceSuffix = {!! json_encode(__('(instrument)')) !!};
                }
            } catch (e) { sourceSuffix = ''; }
            if (fovLabelEl) { fovLabelEl.textContent = baseLabel + (sourceSuffix ? ' ' + sourceSuffix : '') + ':'; }
            if (fovEl) { fovEl.textContent = (typeof fovUsedDeg === 'number' ? formatFovLabel(fovUsedDeg) : '—'); }
            if (magEl) { magEl.textContent = magUsed ? Math.round(magUsed) + 'x' : '—'; }
            // Helper: wait for Aladin to be available (A.aladin) before calling it
            function waitForAladinAndRun(cb, attemptsLeft) {
                attemptsLeft = typeof attemptsLeft === 'number' ? attemptsLeft : 20;
            if (window.A && typeof window.A.aladin === 'function') {
                try { cb(window.A); } catch (e) { console.error('Aladin callback error', e); }
                    return;
                }
                if (attemptsLeft <= 0) {
                    console.error('Aladin did not become available in time.');
                    return;
                }
                // poll after a short delay
                setTimeout(function(){ waitForAladinAndRun(cb, attemptsLeft - 1); }, 200);
            }

                waitForAladinAndRun(function(Alib) {
                    dbg('Aladin is available, initializing with fov', fovUsedDeg);
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
                                    var numeric = f.map(function(v){ return Number(v); }).filter(function(n){ return !isNaN(n); });
                                    if (numeric.length) currentDisplay = Math.min.apply(null, numeric);
                                }
                            } catch (e) { currentDisplay = null; }
                            if (!currentDisplay || isNaN(currentDisplay)) currentDisplay = (typeof displayFovDeg === 'number' ? displayFovDeg : null);

                            // Compute radius in px. Prefer using reported FOV but always apply zoom-based scaling
                            var radiusPx = 0;
                            try {
                                var zoom = (typeof aladinInstance.getZoom === 'function') ? aladinInstance.getZoom() : null;
                                if (typeof aladinInstance.__dslBaseZoom === 'undefined' && typeof zoom !== 'undefined' && zoom !== null) {
                                    // Establish a base zoom reference on first run
                                    aladinInstance.__dslBaseZoom = zoom;
                                }
                                if (currentDisplay && eyeFovDeg) {
                                    // preferred: compute directly from FOV (pixel radius). Rely on Aladin's reported FOV to reflect zoom.
                                    var baseRadius = (Number(eyeFovDeg) / Number(currentDisplay)) * (minDim / 2);
                                    radiusPx = baseRadius;
                                    // remember this radius for future adjustments/fallbacks
                                    aladinInstance.__dslPrevRadiusPx = radiusPx;
                                    // update baseZoom reference so fallback zoom-only scaling has a sensible baseline
                                    if (typeof zoom !== 'undefined' && zoom !== null) {
                                        aladinInstance.__dslBaseZoom = zoom;
                                    }
                                } else if (typeof zoom !== 'undefined' && zoom !== null && typeof aladinInstance.__dslPrevRadiusPx !== 'undefined' && typeof aladinInstance.__dslLastZoom !== 'undefined' && aladinInstance.__dslLastZoom > 0) {
                                    // incremental scaling: scale previous radius by zoom ratio when FOV not available
                                    var lastZ = aladinInstance.__dslLastZoom || zoom;
                                    var ratio = (zoom / lastZ) || 1;
                                    radiusPx = (aladinInstance.__dslPrevRadiusPx || Math.max(40, Math.min(minDim/2 - 10, 100))) * ratio;
                                    // store updated prev radius
                                    aladinInstance.__dslPrevRadiusPx = radiusPx;
                                } else {
                                    // fallback: base pixel radius from container
                                    radiusPx = Math.max(40, Math.min(minDim/2 - 10, 100));
                                    aladinInstance.__dslPrevRadiusPx = radiusPx;
                                }
                                // update lastZoom for next iteration
                                if (typeof zoom !== 'undefined' && zoom !== null) aladinInstance.__dslLastZoom = zoom;
                            } catch (e) {
                                radiusPx = Math.max(40, Math.min(minDim/2 - 10, 100));
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
                                existing.style.zIndex = 40;
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
                            var hideThreshold = 1.05; // hide when diameter exceeds container min-dimension by 5%
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

                            // First, try to attach event-driven updates: ResizeObserver for container and Aladin event hooks if available
                            var containerEl = document.getElementById('aladin-lite-container');
                            // Cleanup previous observers/listeners if present
                            try {
                                if (aladinInstance.__dslResizeObserver && typeof aladinInstance.__dslResizeObserver.disconnect === 'function') {
                                    aladinInstance.__dslResizeObserver.disconnect();
                                    aladinInstance.__dslResizeObserver = null;
                                }
                            } catch (e) { }
                            try {
                                if (aladinInstance.__dslAladinListener && typeof aladinInstance.removeListener === 'function') {
                                    // best-effort: attempt to remove any previously attached listener
                                    try { aladinInstance.removeListener('zoom', aladinInstance.__dslAladinListener); } catch (e) { }
                                    aladinInstance.__dslAladinListener = null;
                                }
                            } catch (e) { }

                            // Attach ResizeObserver if available
                            if (typeof window.ResizeObserver === 'function' && containerEl) {
                                try {
                                    aladinInstance.__dslResizeObserver = new ResizeObserver(function(entries) {
                                        try { updateDomFovOverlay(aladinInstance, eyeFovDeg); } catch (e) { }
                                    });
                                    aladinInstance.__dslResizeObserver.observe(containerEl);
                                } catch (e) { aladinInstance.__dslResizeObserver = null; }
                            }

                            // If Aladin exposes an event or listener system, try to hook into zoom changes
                            try {
                                if (typeof aladinInstance.on === 'function') {
                                    // many libs use .on(event, handler)
                                    aladinInstance.__dslAladinListener = function() { updateDomFovOverlay(aladinInstance, eyeFovDeg); };
                                    try { aladinInstance.on('zoom', aladinInstance.__dslAladinListener); } catch (e) { /* ignore */ }
                                } else if (typeof aladinInstance.addListener === 'function') {
                                    aladinInstance.__dslAladinListener = function() { updateDomFovOverlay(aladinInstance, eyeFovDeg); };
                                    try { aladinInstance.addListener('zoom', aladinInstance.__dslAladinListener); } catch (e) { /* ignore */ }
                                }
                            } catch (e) { }

                            // Always keep a lightweight polling fallback for environments where events aren't available
                            aladinInstance.__dslFovInterval = setInterval(function(){
                                try {
                                    var f = aladinInstance.getFov && aladinInstance.getFov();
                                    var cur = null;
                                    var curRa = null;
                                    var curDec = null;
                                    if (f && f.length) {
                                        var numeric = f.map(function(v){ return Number(v); }).filter(function(n){ return !isNaN(n); });
                                        if (numeric.length === 1) { curRa = numeric[0]; curDec = numeric[0]; cur = numeric[0]; }
                                        else if (numeric.length >= 2) { curRa = numeric[0]; curDec = numeric[1]; cur = Math.max.apply(null, [curRa, curDec]); }
                                    }
                                    if (cur === null) cur = (typeof displayFovDeg === 'number' ? displayFovDeg : null);
                                    if (curRa === null) curRa = cur;
                                    if (curDec === null) curDec = cur;
                                    // Update visible legend with source FoV and update an in-preview live-FoV badge
                                    try {
                                        var legendFovEl = document.getElementById('aladin-fov');
                                        var badge = document.getElementById('aladin-live-fov-badge');
                                        var sourceText = (typeof eyeFovDeg === 'number' ? formatFovLabel(eyeFovDeg) : '—');
                                        var liveText = '—';
                                        try {
                                            if (curRa !== null && curDec !== null && !isNaN(Number(curRa)) && !isNaN(Number(curDec))) {
                                                // Show arcminutes only in the live badge (no degrees conversion)
                                                var raArcmin = Math.round(Number(curRa) * 60 * 10) / 10;
                                                var decArcmin = Math.round(Number(curDec) * 60 * 10) / 10;
                                                var raLabel = raArcmin + "'";
                                                var decLabel = decArcmin + "'";
                                                liveText = raLabel + ' × ' + decLabel;
                                            }
                                        } catch (e) { liveText = '—'; }
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
                                                badge.style.zIndex = 80;
                                                badge.style.pointerEvents = 'none';
                                                containerEl.style.position = containerEl.style.position || 'relative';
                                                containerEl.appendChild(badge);
                                            }
                                            if (badge) {
                                                badge.textContent = liveText;
                                            }
                                        } catch (e) {}
                                    } catch (e) {}
                                    var zoom = (typeof aladinInstance.getZoom === 'function') ? aladinInstance.getZoom() : null;
                                    var w = containerEl ? (containerEl.clientWidth || containerEl.offsetWidth || 0) : 0;
                                    var h = containerEl ? (containerEl.clientHeight || containerEl.offsetHeight || 0) : 0;
                                    // If either fov changed or container size changed, update overlay
                                    if (cur !== aladinInstance.__dslLastFov || zoom !== aladinInstance.__dslLastZoom || w !== aladinInstance.__dslLastContainerW || h !== aladinInstance.__dslLastContainerH) {
                                        aladinInstance.__dslLastFov = cur;
                                        aladinInstance.__dslLastZoom = zoom;
                                        aladinInstance.__dslLastContainerW = w;
                                        aladinInstance.__dslLastContainerH = h;
                                        updateDomFovOverlay(aladinInstance, eyeFovDeg);
                                    }
                                } catch (e) { }
                            }, 300);
                            // Attach a non-passive wheel listener to the container to allow mouse wheel zooming
                            try {
                                if (containerEl && !aladinInstance.__dslWheelHandlerAttached) {
                                    var wheelHandler = function(e) {
                                        try {
                                            // prevent page scroll during zooming
                                            if (e && typeof e.preventDefault === 'function') e.preventDefault();
                                            var delta = e.deltaY || e.wheelDelta || e.detail || 0;
                                            // Normalize: positive delta typically means scroll down -> zoom out
                                            var zoomOut = (delta > 0);
                                            if (typeof aladinInstance.getZoom === 'function' && typeof aladinInstance.setZoom === 'function') {
                                                try {
                                                    var z = aladinInstance.getZoom();
                                                    if (typeof z === 'number') {
                                                        var nz = Math.max(0, z + (zoomOut ? -1 : 1));
                                                        aladinInstance.setZoom(nz);
                                                        return;
                                                    }
                                                } catch (e) {}
                                            }
                                            if (typeof aladinInstance.getFov === 'function' && typeof aladinInstance.setFov === 'function') {
                                                try {
                                                    var fv = aladinInstance.getFov();
                                                    var cf = null;
                                                    if (fv && fv.length) cf = Number(fv[0]); else cf = Number(fv) || displayFovDeg || 1.0;
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
                                                            if (typeof eyeFovDeg === 'number' && eyeFovDeg > 0 && curF > (eyeFovDeg * 8)) {
                                                                useFactor = largeFactor;
                                                            }
                                                        } catch (e) { useFactor = baseFactor; }
                                                        var factor = zoomOut ? useFactor : (1 / useFactor);
                                                        var newF = Math.max(0.01, Math.min(180, cf * factor));
                                                        aladinInstance.setFov(newF);
                                                    }
                                                } catch (e) {}
                                            }
                                        } catch (e) {}
                                    };
                                    try {
                                        containerEl.addEventListener('wheel', wheelHandler, { passive: false });
                                    } catch (e) {
                                        // older browsers may not support options object
                                        containerEl.addEventListener('wheel', wheelHandler, false);
                                    }
                                    aladinInstance.__dslWheelHandlerAttached = wheelHandler;
                                }
                            } catch (e) {}
                        } catch (e) { }
                    }
                    // Helper to add a single marker via a catalog (catalog implements setView)
                    function addMarkerViaCatalog(aladinInstance, ra, dec, opts) {
                        try {
                            var marker = Alib.marker([ra, dec], opts || { color: 'magenta', size: 20 });
                            var cat = Alib.catalog({ name: 'marker-catalog', color: (opts && opts.color) || 'magenta' });
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
                            dbg('addFovCircle called', { ra: ra, dec: dec, eyeFovDeg: eyeFovDeg, opts: opts });
                            if (!eyeFovDeg || isNaN(Number(eyeFovDeg))) {
                                dbg('addFovCircle: invalid eyeFovDeg, skipping');
                                return;
                            }
                            var radius = Number(eyeFovDeg) / 2.0; // degrees
                            var circleOpts = opts || { color: 'cyan', lineWidth: 2, opacity: 0.8 };

                            // Prefer to use Alib.graphicOverlay if available (overlay implements setView)
                            if (Alib && typeof Alib.graphicOverlay === 'function' && typeof Alib.circle === 'function') {
                                try {
                                    dbg('addFovCircle: attempting graphicOverlay approach');
                                    var circ = Alib.circle(ra, dec, radius, circleOpts);
                                    var overlay = Alib.graphicOverlay({ name: 'fov-overlay' });
                                    if (overlay && typeof overlay.add === 'function') {
                                        overlay.add(circ);
                                        aladinInstance.addOverlay(overlay);
                                        dbg('addFovCircle: graphicOverlay added');
                                        return;
                                    }
                                } catch (e) {
                                    // fall through to marker-based fallback
                                    dbg('addFovCircle: graphicOverlay approach failed, will fallback to markers', e);
                                }
                            } else {
                                dbg('addFovCircle: graphicOverlay or circle factory not available, using fallback');
                            }

                            // Fallback: approximate the circle with a set of small markers placed around the circumference
                            // This avoids relying on an overlay factory and will always be visible as a ring of points.
                            var points = [];
                            var steps = 72; // more points for a smoother ring
                            var decRad = dec * Math.PI/180.0;
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
                                    var markerOpts = { color: visColor, size: visSize, markerSize: visSize, sourceSize: visSize, symbol: 'circle' };
                                    var m = Alib.marker([pRa, pDec], markerOpts);
                                    points.push(m);
                                } catch (e) {
                                    // if marker creation fails, skip point
                                }
                            }
                            if (points.length === 0) {
                                dbg('addFovCircle: no points created for fallback');
                                return;
                            }
                            var cat = Alib.catalog({ name: 'fov-markers', color: visColor });
                            cat.addSources(points);
                            aladinInstance.addCatalog(cat);
                            // Try to set marker/source size on the catalog if supported
                            try {
                                if (typeof cat.setMarkerSize === 'function') { cat.setMarkerSize(visSize); dbg('setMarkerSize called on catalog'); }
                                if (typeof cat.setSourceSize === 'function') { cat.setSourceSize(visSize); dbg('setSourceSize called on catalog'); }
                                if (typeof cat.setSymbol === 'function') { cat.setSymbol('circle'); dbg('setSymbol called on catalog'); }
                            } catch (e) {
                                dbg('Failed to call catalog sizing methods', e);
                            }
                            // Try to force a redraw on the Aladin instance
                            try {
                                if (typeof aladinInstance.requestRedraw === 'function') { aladinInstance.requestRedraw(); }
                                else if (typeof aladinInstance.render === 'function') { aladinInstance.render(); }
                            } catch (e) {
                                // ignore
                            }
                            dbg('addFovCircle: fallback catalog added with', points.length, 'points, catalog=', cat);
                            try {
                                if (typeof cat.getSources === 'function') {
                                    dbg('catalog.getSources():', cat.getSources().slice(0,5));
                                } else if (cat.sources) {
                                    dbg('catalog.sources:', (cat.sources || []).slice(0,5));
                                }
                            } catch (e) {
                                dbg('Could not inspect catalog sources', e);
                            }
                            try {
                                if (typeof aladinInstance.getFov === 'function') {
                                    dbg('Aladin fov (after add):', aladinInstance.getFov());
                                }
                                if (typeof aladinInstance.getZoom === 'function') {
                                    dbg('Aladin zoom:', aladinInstance.getZoom());
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
                                    try { updateDomFovOverlay(aladinInstance, eyeFovDeg); } catch (e) { dbg('addFovCircle: failed to update DOM overlay via helper', e); }
                                }
                            } catch (e) {
                                dbg('addFovCircle: failed to add/update DOM overlay', e);
                            }
                            // Add four cardinal markers (N,S,E,W) to make the ring extent obvious
                            try {
                                var cardinal = [];
                                var offsets = [0, Math.PI/2, Math.PI, 3*Math.PI/2];
                                for (var j = 0; j < offsets.length; j++) {
                                    var th = offsets[j];
                                    var dDec = radius * Math.sin(th);
                                    var dRa = (radius * Math.cos(th)) / Math.max(0.0001, Math.cos(decRad));
                                    var pRa = ra + dRa;
                                    var pDec = dec + dDec;
                                    try { cardinal.push(Alib.marker([pRa, pDec], { color: 'magenta', size: 30 })); } catch(e) {}
                                }
                                if (cardinal.length) {
                                    var majorCat = Alib.catalog({ name: 'fov-major', color: 'magenta' });
                                    majorCat.addSources(cardinal);
                                    aladinInstance.addCatalog(majorCat);
                                    dbg('addFovCircle: added cardinal markers', cardinal.length, 'majorCat=', majorCat);
                                }
                            } catch (e) {
                                dbg('addFovCircle: failed to add cardinal markers', e);
                            }
                            try {
                                if (typeof aladinInstance.getCatalogs === 'function') {
                                    dbg('Aladin catalogs after add:', aladinInstance.getCatalogs());
                                }
                            } catch (e) {
                                dbg('Could not read aladinInstance.getCatalogs()', e);
                            }
                            try {
                                if (typeof aladinInstance.getCatalogs === 'function') {
                                    dbg('Aladin catalogs after add:', aladinInstance.getCatalogs());
                                }
                            } catch (e) {
                                dbg('Could not read aladinInstance.getCatalogs()', e);
                            }
                        } catch (e) {
                            console.error('Failed to add FoV circle (both overlay and marker fallback)', e);
                        }
                    }

                    // Try to detect a 15x RA scaling mismatch between our computed RA (deg)
                    // and what Aladin actually uses. If detected, re-apply goto with corrected RA.
                    function ensureAladinCenterAndMark(aladinInstance, intendedRaDeg, intendedDecDeg, displayFov, eyeFov) {
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
                                    addMarkerViaCatalog(aladinInstance, intendedRaDeg, intendedDecDeg, { color: 'magenta', size: 20 });
                                    return;
                                }
                                var gotRa = Number(got[0]);
                                var gotDec = Number(got[1]);
                                dbg('Aladin reported center (deg):', gotRa, gotDec, 'intended (deg):', intendedRaDeg, intendedDecDeg);

                                // If gotRa is approximately intendedRaDeg, all good.
                                if (Math.abs(gotRa - intendedRaDeg) < 1e-6) {
                                    addMarkerViaCatalog(aladinInstance, gotRa, gotDec, { color: 'magenta', size: 20 });
                                    // draw FoV circle using the eyepiece fov
                                    try { dbg('ensureAladinCenterAndMark: calling addFovCircle (main branch)', gotRa, gotDec, eyeFov); addFovCircle(aladinInstance, gotRa, gotDec, eyeFov); } catch(e) { console.error('addFovCircle threw', e); }
                                    return;
                                }

                                // Check if there's a ~1/15 or ~15 ratio mismatch (common RA-hours<->degrees confusion)
                                var ratio = gotRa / intendedRaDeg;
                                if (Math.abs(ratio - 1/15) < 0.01) {
                                    dbg('Detected RA scaling ~1/15; re-applying corrected goto (dividing by 15)');
                                    aladinInstance.gotoRaDec(intendedRaDeg / 15.0, intendedDecDeg, displayFov);
                                    setTimeout(function(){
                                        var g2 = aladinInstance.getRaDec();
                                        addMarkerViaCatalog(aladinInstance, g2[0], g2[1], { color: 'magenta', size: 20 });
                                        try { dbg('ensureAladinCenterAndMark: calling addFovCircle (1/15 branch)', g2[0], g2[1], eyeFov); addFovCircle(aladinInstance, g2[0], g2[1], eyeFov); } catch(e) { console.error('addFovCircle threw', e); }
                                    }, 200);
                                    return;
                                }
                                if (Math.abs(ratio - 15) < 0.01) {
                                    dbg('Detected RA scaling ~15; re-applying corrected goto (multiplying by 15)');
                                    aladinInstance.gotoRaDec(intendedRaDeg * 15.0, intendedDecDeg, displayFov);
                                    setTimeout(function(){
                                        var g2 = aladinInstance.getRaDec();
                                        addMarkerViaCatalog(aladinInstance, g2[0], g2[1], { color: 'magenta', size: 20 });
                                        try { dbg('ensureAladinCenterAndMark: calling addFovCircle (x15 branch)', g2[0], g2[1], eyeFov); addFovCircle(aladinInstance, g2[0], g2[1], eyeFov); } catch(e) { console.error('addFovCircle threw', e); }
                                    }, 200);
                                    return;
                                }

                                // No obvious scaling; just add marker at what Aladin reports
                                addMarkerViaCatalog(aladinInstance, gotRa, gotDec, { color: 'magenta', size: 20 });
                                try { dbg('ensureAladinCenterAndMark: calling addFovCircle (no-scaling branch)', gotRa, gotDec, eyeFov); addFovCircle(aladinInstance, gotRa, gotDec, eyeFov); } catch(e) { console.error('addFovCircle threw', e); }
                            } catch (e) {
                                console.error('Error while ensuring Aladin center', e);
                                addMarkerViaCatalog(aladinInstance, intendedRaDeg, intendedDecDeg, { color: 'magenta', size: 20 });
                                try { dbg('ensureAladinCenterAndMark: calling addFovCircle (error fallback)', intendedRaDeg, intendedDecDeg, eyeFov); addFovCircle(aladinInstance, intendedRaDeg, intendedDecDeg, eyeFov); } catch(e) { console.error('addFovCircle threw', e); }
                            }
                        }, 250);
                    }

                    if (centerRaDeg !== null && centerDecDeg !== null) {
                        var aladin = Alib.aladin('#aladin-lite-container', {survey: 'P/DSS2/color', fov: displayFovDeg, cooFrame: 'ICRS'});
                        try { setFovOverlayWatcher(aladin, fovUsedDeg); } catch (e) {}
                        // Prune unwanted controls once created
                        try { pruneAladinControls(document.getElementById('aladin-lite-container'), aladin); } catch(e){}
                        // Multiply RA by 15 before sending to Aladin (user requested behavior)
                        ensureAladinCenterAndMark(aladin, centerRaDeg * 15.0, centerDecDeg, displayFovDeg, fovUsedDeg);
                            // After initial goto, adjust the display FOV iteratively so the Declination (vertical)
                            // FOV reported by Aladin matches the object's angular size (fovUsedDeg).
                            try { callSetDisplayFovRepeated(aladin, fovUsedDeg, 24, 6, 300); } catch (e) {}
                    } else if (aladinDefaults && aladinDefaults.ra_raw && aladinDefaults.dec_raw) {
                        var raGuess = parseRaToDegrees(aladinDefaults.ra_raw);
                        var decGuess = parseDecToDegrees(aladinDefaults.dec_raw);
                        if (raGuess !== null && decGuess !== null) {
                            var al = Alib.aladin('#aladin-lite-container', {survey: 'P/DSS2/color', fov: displayFovDeg, cooFrame: 'ICRS'});
                            try { setFovOverlayWatcher(al, fovUsedDeg); } catch (e) {}
                            try { pruneAladinControls(document.getElementById('aladin-lite-container'), al); } catch(e){}
                            // Multiply RA by 15 before sending to Aladin (user requested behavior)
                            ensureAladinCenterAndMark(al, raGuess * 15.0, decGuess, displayFovDeg, fovUsedDeg);
                            try { callSetDisplayFovRepeated(al, fovUsedDeg, 24, 6, 300); } catch (e) {}
                        } else if (sessionName) {
                                var al2 = Alib.aladin('#aladin-lite-container', {survey: 'P/DSS2/color', fov: displayFovDeg, cooFrame: 'ICRS'});
                                try { setFovOverlayWatcher(al2, fovUsedDeg); } catch (e) {}
                                try { pruneAladinControls(document.getElementById('aladin-lite-container'), al2); } catch(e){}
                                al2.gotoObject(sessionName);
                        }
                    } else if (sessionName) {
                        var al3 = Alib.aladin('#aladin-lite-container', {survey: 'P/DSS2/color', fov: displayFovDeg, cooFrame: 'ICRS'});
                            try { setFovOverlayWatcher(al3, fovUsedDeg); } catch (e) {}
                            try { pruneAladinControls(document.getElementById('aladin-lite-container'), al3); } catch(e){}
                            al3.gotoObject(sessionName);
                                try { callSetDisplayFovRepeated(al3, fovUsedDeg, 24, 6, 300); } catch (e) {}
                    }
                });
        }

        // Ensure the Aladin script is loaded dynamically to avoid timing / init conflicts
        function ensureAladinScriptLoaded(cb) {
            var aladinSrc = 'https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.js';
            var jQuerySrc = 'https://code.jquery.com/jquery-3.6.0.min.js';
            if (window.A && typeof window.A.aladin === 'function') {
                dbg('Aladin already present');
                return cb();
            }

            function injectScript(src, attrName, onload, onerror) {
                var existing = document.querySelector('script[' + attrName + ']');
                if (existing) {
                    existing.addEventListener('load', function(){ onload && onload(); });
                    existing.addEventListener('error', function(e){ onerror && onerror(e); });
                    return existing;
                }
                var s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.setAttribute(attrName, '1');
                s.onload = function() { dbg(src + ' loaded (onload)'); onload && onload(); };
                s.onerror = function(e) { console.error('Failed to load ' + src, e); onerror && onerror(e); };
                document.head.appendChild(s);
                return s;
            }

            // Ensure jQuery is present before Aladin (Aladin expects jQuery)
            function loadAladinAfterJQuery() {
                // If Aladin already added, wait for it
                var existingAl = document.querySelector('script[data-aladin-loader]');
                if (existingAl) {
                    var tries = 0;
                    var poll = setInterval(function(){
                        if (window.A && typeof window.A.aladin === 'function') { clearInterval(poll); cb(); }
                        tries++; if (tries > 100) { clearInterval(poll); console.error('Aladin not available after waiting'); cb(); }
                    }, 200);
                    return;
                }
                injectScript(aladinSrc, 'data-aladin-loader', function(){
                    // give Aladin a short moment to setup globals
                    setTimeout(function(){ if (window.A && typeof window.A.aladin === 'function') cb(); else cb(); }, 50);
                }, function(){ cb(); });
            }

            if (!window.jQuery) {
                // inject jQuery, then aladin
                injectScript(jQuerySrc, 'data-jquery-loader', function(){
                    dbg('jQuery loaded, now loading Aladin');
                    loadAladinAfterJQuery();
                }, function(e){
                    console.error('Failed to load jQuery, attempting to load Aladin anyway', e);
                    loadAladinAfterJQuery();
                });
            } else {
                loadAladinAfterJQuery();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { ensureAladinScriptLoaded(initAladin); });
        } else { ensureAladinScriptLoaded(initAladin); }
    })();
</script>
