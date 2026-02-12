<x-app-layout>
    <div>
        <!-- Use a wider container so the object details area can take more horizontal space.
           Switched from max-w-7xl to max-w-screen-xl which uses more of the viewport on large screens. -->
        <!-- wider container: default to screen-xl, but use an even wider max at xl and above -->
        <!-- Allow full width at xl so the main area can expand; keep comfortable padding -->
        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-6">
                @php
                    $objSlugTop =
                        $canonicalSlug ?? ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
                @endphp
                <h1 class="text-3xl font-extrabold">
                    <a href="{{ route('object.show', ['slug' => $objSlugTop]) }}"
                        class="hover:underline">{{ html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                </h1>

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
                            @php
                                $objectIdForConstellation =
                                    $session->id ?? ($canonicalSlug ?? ($session->slug ?? ($session->name ?? null)));
                            @endphp
                            <livewire:object-constellation :initial-constellation="$session->constellation ?? null" :object-id="$objectIdForConstellation" />
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
                                    @if (!empty($alternatives) && count($alternatives) > 0)
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
                                    @endif

                                    {{-- If this is a planet and the server computed a diameter, show it for non-authenticated users (Livewire handles auth users) --}}
                                    @if (($session->source_type_raw ?? '') === 'planet' && isset($session->planet_diam1))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Diameter') }}</td>
                                            <td id="dsl-top-diameter">
                                                @php
                                                    $pd1 = $session->planet_diam1 ?? null;
                                                    $pd2 = $session->planet_diam2 ?? null;
                                                @endphp
                                                @if (is_numeric($pd1))
                                                    {{ number_format($pd1, 1) }}"@if (is_numeric($pd2) && $pd2 !== $pd1)
                                                        x {{ number_format($pd2, 1) }}"
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    {{-- Static RA/Dec removed for comet pages; Livewire `comet-details` renders dynamic values. --}}
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
                                    @if (!empty(trim($session->comments ?? '')))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Description') }}</td>
                                            <td>{!! nl2br(e($session->comments ?? '')) !!}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($session->mag))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Magnitude') }}</td>
                                            <td id="dsl-top-mag">
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

                                        {{-- Show illuminated only for planets --}}
                                        @if (($session->source_type_raw ?? '') === 'planet')
                                            <tr>
                                                <td class="pr-4 font-medium">{{ __('Illuminated') }}</td>
                                                <td id="dsl-top-illum">
                                                    @php
                                                        $illum = $session->illuminated_fraction ?? null;
                                                        $illumDisplay = is_numeric($illum)
                                                            ? number_format(floatval($illum) * 100.0, 1) . '%'
                                                            : '—';
                                                    @endphp
                                                    {{ $illumDisplay }}
                                                </td>
                                            </tr>
                                        @endif
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

                                    @php
                                        $__dsl_top_raw = is_array($comet_magnitudes ?? null)
                                            ? count($comet_magnitudes)
                                            : 0;
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

                                    {{-- comet magnitude chart moved below the altitude/year graphs --}}
                                    @php $__isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp
                                    @if ($__isCometLocal)
                                        @includeWhen(true, 'object.partials.comet-details', ['session' => $session, 'comet_magnitudes' => $comet_magnitudes ?? null, 'ephemerides' => $ephemerides ?? null, 'suppressLivewire' => false])
                                    @endif

                                    @php
                                        $partOf = null;
                                        $partOfUrl = null;
                                        $partOfLabel = __('Part of');
                                        try {
                                            $objNameLocal = $session->name ?? '';
                                            if (
                                                !empty($objNameLocal) &&
                                                class_exists(\App\Models\ObjectPartOf::class)
                                            ) {
                                                $p = \App\Models\ObjectPartOf::where(
                                                    'objectname',
                                                    $objNameLocal,
                                                )->first();
                                                if ($p && !empty($p->partofname)) {
                                                    $partOf = $p->partofname;
                                                    // If the parent is a planet, label as 'Moon of' and link to planet record
                                                    try {
                                                        $planet = \App\Models\Planet::where('name', $partOf)->first();
                                                        if ($planet) {
                                                            $partOfLabel = __('Moon of');
                                                            $slug =
                                                                $planet->slug ??
                                                                \Illuminate\Support\Str::slug($planet->name);
                                                            $partOfUrl = route('object.show', ['slug' => $slug]);
                                                        } else {
                                                            // Try legacy objects table for a link
                                                            $objRow = \Illuminate\Support\Facades\DB::table('objects')
                                                                ->where('name', $partOf)
                                                                ->first();
                                                            if ($objRow) {
                                                                $partOfUrl = route('object.show', [
                                                                    'slug' => \Illuminate\Support\Str::slug($partOf),
                                                                ]);
                                                            }
                                                        }
                                                    } catch (\Throwable $_) {
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $_) {
                                            $partOf = null;
                                            $partOfUrl = null;
                                        }

                                        // Inverse relations: Moons for planets, Contains for deepsky
                                        $moonsList = [];
                                        $containsList = [];
                                        try {
                                            if (($session->source_type_raw ?? '') === 'planet') {
                                                if (\Illuminate\Support\Facades\Schema::hasTable('moons')) {
                                                    $rows = \Illuminate\Support\Facades\DB::table('moons')
                                                        ->where('planet_id', $session->id)
                                                        ->get();
                                                    foreach ($rows as $r) {
                                                        $slug = $r->slug ?? \Illuminate\Support\Str::slug($r->name);
                                                        $moonsList[] =
                                                            '<a href="' .
                                                            e(route('object.show', ['slug' => $slug])) .
                                                            '" class="text-gray-300 hover:underline">' .
                                                            e($r->name) .
                                                            '</a>';
                                                    }
                                                }
                                                // Fallback to legacy objectpartof table
                                                if (class_exists(\App\Models\ObjectPartOf::class)) {
                                                    $po = \App\Models\ObjectPartOf::where('partofname', $session->name)
                                                        ->pluck('objectname')
                                                        ->unique();
                                                    foreach ($po as $on) {
                                                        $slug = \Illuminate\Support\Str::slug($on);
                                                        $moonsList[] =
                                                            '<a href="' .
                                                            e(route('object.show', ['slug' => $slug])) .
                                                            '" class="text-gray-300 hover:underline">' .
                                                            e($on) .
                                                            '</a>';
                                                    }
                                                }
                                            } else {
                                                if (class_exists(\App\Models\ObjectPartOf::class)) {
                                                    // Try exact case-insensitive match first
                                                    $po = \App\Models\ObjectPartOf::whereRaw('LOWER(partofname) = ?', [
                                                        mb_strtolower($session->name ?? ''),
                                                    ])
                                                        ->pluck('objectname')
                                                        ->unique()
                                                        ->toArray();

                                                    // If nothing found, try slug-based matching which handles variations like "Hickson 56" vs "HCG 56"
                                                    if (empty($po)) {
                                                        try {
                                                            $targetSlug = \Illuminate\Support\Str::slug(
                                                                $session->name ?? '',
                                                            );
                                                            $all = \Illuminate\Support\Facades\DB::table('objectpartof')
                                                                ->select(['objectname', 'partofname'])
                                                                ->get();
                                                            foreach ($all as $r) {
                                                                if (
                                                                    \Illuminate\Support\Str::slug(
                                                                        $r->partofname ?? '',
                                                                    ) === $targetSlug
                                                                ) {
                                                                    $po[] = $r->objectname;
                                                                }
                                                            }
                                                            $po = array_values(array_unique($po));
                                                        } catch (\Throwable $_) {
                                                            $po = $po;
                                                        }
                                                    }

                                                    foreach ($po as $on) {
                                                        $slug = \Illuminate\Support\Str::slug($on);
                                                        $containsList[] =
                                                            '<a href="' .
                                                            e(route('object.show', ['slug' => $slug])) .
                                                            '" class="text-gray-300 hover:underline">' .
                                                            e($on) .
                                                            '</a>';
                                                    }
                                                }
                                            }
                                        } catch (\Throwable $_) {
                                        }
                                    @endphp

                                    @if (!empty($partOf))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ $partOfLabel }}</td>
                                            <td>
                                                @if (!empty($partOfUrl))
                                                    <a href="{{ $partOfUrl }}"
                                                        class="text-gray-300 hover:underline">{{ e($partOf) }}</a>
                                                @else
                                                    {{ e($partOf) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($moonsList))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Moons') }}</td>
                                            <td>{!! implode(', ', array_unique($moonsList)) !!}</td>
                                        </tr>
                                    @endif

                                    @if (!empty($containsList))
                                        <tr>
                                            <td class="pr-4 font-medium">{{ __('Contains') }}</td>
                                            <td>{!! implode(', ', array_unique($containsList)) !!}</td>
                                        </tr>
                                    @endif

                                    {{-- (Observation/drawing stats moved to header) --}}

                                    {{-- Ephemerides: date, rise/transit/set, best time, maximum altitude, altitude graph provided by astronomy library --}}
                                    {{-- Date selector moved to global aside Livewire component --}}
                                    {{-- Ephemerides rows are rendered by a Livewire component so they can update live when the aside date changes --}}
                                    {{-- Render ephemerides for all visitors (not auth-only) so planet event rows are visible for Mercury/Venus. --}}
                                    @livewire('object-ephemerides', ['objectId' => (string) ($session->id ?? ''), 'initial' => $ephemerides ?? null, 'objectName' => $session->name ?? null, 'sourceTypeRaw' => $session->source_type_raw ?? null])

                                    @auth
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

                                        @php $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp
                                        @if (!$isCometLocal)
                                            @php
                                                // Only mount the heavy Aladin preview when the user has a
                                                // configured standard instrument set or explicit saved
                                                // selections. This avoids expensive Livewire/server work
                                                // for users who haven't configured instruments yet.
                                                $authUser = auth()->user();
                                                $hasStdSet = $authUser?->stdinstrumentset ?? null;
                                                $hasSavedSelection = !empty($selectedInstrumentId) || !empty($selectedEyepieceId) || !empty($selectedLensId);
                                            @endphp

                                            @if ($hasStdSet || $hasSavedSelection)
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
                                            @else
                                                <div class="text-sm text-gray-400">{{ __('Aladin preview disabled — configure a default instrument set or select instrument/eyepiece to enable.') }}</div>
                                            @endif
                                        @endif
                                    @endauth

                                </table>

                                {{-- Comet magnitude chart: show under Optimum detection magnification --}}
                                @php $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? '')) ) === 'comet'; @endphp
                                @if ($isCometLocal && $__dsl_top_filtered > 0)
                                    @php
                                        // Render static SVG magnitude chart below the ephemerides block.
                                        $svg = null;
                                        try {
                                            $pts = is_array($__dsl_top_chart_points ?? null) ? $__dsl_top_chart_points : [];
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
                                                $svg .= '<style>.dsl-txt{font-family:Arial,Helvetica,sans-serif;font-size:11px;fill:#bbbbbb}</style>';
                                                $svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" fill="transparent" />';
                                                $svg .= '<line x1="' . $mL . '" y1="' . $mT . '" x2="' . $mL . '" y2="' . ($h - $mB) . '" stroke="#444" stroke-width="1"/>';
                                                $svg .= '<line x1="' . $mL . '" y1="' . ($h - $mB) . '" x2="' . ($w - $mR) . '" y2="' . ($h - $mB) . '" stroke="#444" stroke-width="1"/>';
                                                $svg .= '<text x="8" y="' . ($mT + 10) . '" class="dsl-txt">' . htmlspecialchars($yLabelMin) . '</text>';
                                                $svg .= '<text x="8" y="' . ($h - $mB) . '" class="dsl-txt">' . htmlspecialchars($yLabelMax) . '</text>';
                                                $svg .= '<text x="' . $mL . '" y="' . ($h - 6) . '" class="dsl-txt">' . htmlspecialchars($labelMin) . '</text>';
                                                $svg .= '<text x="' . ($mL + $plotW / 2 - 30) . '" y="' . ($h - 6) . '" class="dsl-txt">' . htmlspecialchars($labelMid) . '</text>';
                                                $svg .= '<text x="' . ($w - $mR - 60) . '" y="' . ($h - 6) . '" class="dsl-txt">' . htmlspecialchars($labelMax) . '</text>';
                                                $svg .= '<polyline points="' . $poly . '" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />';
                                                $svg .= implode('', $dots);
                                                $svg .= '</svg>';
                                            }
                                        } catch (\Throwable $_) {
                                            $svg = null;
                                        }
                                    @endphp
                                    @if (!empty($svg))
                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold mb-2">Estimated magnitudes</h4>
                                            {!! $svg !!}
                                        </div>
                                    @endif
                                @endif

                                {{-- Altitude graph now rendered by the Livewire `object-ephemerides` component to avoid duplication --}}

                            </div>

                            {{-- Nearby objects (powergrid) --}}
                                @if (isset($session->ra) &&
                                    isset($session->decl) &&
                                    !empty($session->ra) &&
                                    !empty($session->decl) &&
                                    ($session->source_type_raw ?? '') !== 'planet' && ($session->source_type_raw ?? '') !== 'comet')
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
                                                class="text-sm rounded bg-gray-700 text-gray-100 p-1 border-gray-600"
                                                style="background-color: #374151; color: #f3f4f6;">
                                                <option value="5" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 5 ? 'selected' : '' }}>5'</option>
                                                <option value="10" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 10 ? 'selected' : '' }}>10'</option>
                                                <option value="15" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 15 ? 'selected' : '' }}>15'</option>
                                                <option value="30" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 30 ? 'selected' : '' }}>30'</option>
                                                <option value="60" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 60 ? 'selected' : '' }}>1°</option>
                                                <option value="120" style="background-color: #374151; color: #f3f4f6;"
                                                    {{ $nearbyRadiusSelected === 120 ? 'selected' : '' }}>2°</option>
                                            </select>
                                            <div class="text-xs text-gray-400 ml-2">
                                                {{ __('Choose radius to search nearby objects.') }}</div>
                                            <!-- Export names (PDF) button: dispatches to the nearby-objects-table Livewire component -->
                                            <div class="ml-auto" x-data="{ open: false }" x-cloak>
                                                @php
                                                    // Build safe base URLs for exports using server-known coordinates
                                                    $exportNamesBase =
                                                        route('object.nearby.names.pdf', [
                                                            'slug' => $canonicalSlug ?? ($session->slug ?? ''),
                                                        ]) . '?';
                                                    $exportNamesBase .= 'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                    $exportNamesBase .= '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                    $exportNamesBase .=
                                                        '&radius=' . rawurlencode($nearbyRadiusSelected ?? 30);

                                                    $exportTableBase =
                                                        route('object.nearby.table.pdf', [
                                                            'slug' => $canonicalSlug ?? ($session->slug ?? ''),
                                                        ]) . '?';
                                                    $exportTableBase .= 'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                    $exportTableBase .= '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                    $exportTableBase .=
                                                        '&radius=' . rawurlencode($nearbyRadiusSelected ?? 30);
                                                    // Argo/Navis plain-text export URL (same params as PDFs)
                                                    $exportArgoBase =
                                                        route('object.nearby.argo', [
                                                            'slug' => $canonicalSlug ?? ($session->slug ?? ''),
                                                        ]) . '?';
                                                    $exportArgoBase .= 'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                    $exportArgoBase .= '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                    $exportArgoBase .=
                                                        '&radius=' . rawurlencode($nearbyRadiusSelected ?? 30);
                                                    // SkySafari .skylist export URL
                                                    $exportSkylistBase =
                                                        route('object.nearby.skylist', [
                                                            'slug' => $canonicalSlug ?? ($session->slug ?? ''),
                                                        ]) . '?';
                                                    $exportSkylistBase .= 'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                    $exportSkylistBase .= '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                    $exportSkylistBase .=
                                                        '&radius=' . rawurlencode($nearbyRadiusSelected ?? 30);
                                                @endphp

                                                <div class="relative inline-block text-left">
                                                    <button type="button" @click="open = !open"
                                                        @keydown.escape="open = false"
                                                        class="inline-flex items-center gap-2 text-sm font-medium px-3 py-1.5 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 active:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                                                        aria-haspopup="true" :aria-expanded="open.toString()">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path d="M12 5v14M5 12h14" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <span>{{ __('Export') }}</span>
                                                    </button>

                                                    <div x-show="open" x-transition @click.outside="open = false"
                                                        class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                                        style="display:none;">
                                                        <div class="py-1 text-sm text-gray-100" role="menu"
                                                            aria-orientation="vertical">
                                                            <a href="{{ $exportNamesBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export names (PDF)') }}</a>
                                                            <a href="{{ $exportTableBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export table (PDF)') }}</a>
                                                            <a href="{{ $exportArgoBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export Argo Navis') }}</a>
                                                            <a href="{{ $exportSkylistBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export SkySafari (.skylist)') }}</a>
                                                            @php
                                                                $exportStxtBase =
                                                                    route('object.nearby.stxt', [
                                                                        'slug' =>
                                                                            $canonicalSlug ?? ($session->slug ?? ''),
                                                                    ]) . '?';
                                                                $exportStxtBase .=
                                                                    'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                                $exportStxtBase .=
                                                                    '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                                $exportStxtBase .=
                                                                    '&radius=' .
                                                                    rawurlencode($nearbyRadiusSelected ?? 30);

                                                                // APD export: route to controller action that delegates to the Livewire component
                                                                $exportApdBase =
                                                                    route('object.nearby.apd', [
                                                                        'slug' =>
                                                                            $canonicalSlug ?? ($session->slug ?? ''),
                                                                    ]) . '?';
                                                                $exportApdBase .=
                                                                    'ra=' . rawurlencode($nearbyRaDeg ?? '');
                                                                $exportApdBase .=
                                                                    '&dec=' . rawurlencode($nearbyDecDeg ?? '');
                                                                $exportApdBase .=
                                                                    '&radius=' .
                                                                    rawurlencode($nearbyRadiusSelected ?? 30);
                                                            @endphp
                                                            <a href="{{ $exportStxtBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export SkyTools (.txt)') }}</a>
                                                            <a href="{{ $exportApdBase }}" target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="block px-4 py-2 hover:bg-gray-700"
                                                                role="menuitem">{{ __('Export AstroPlanner (.apd)') }}</a>
                                                        </div>
                                                    </div>
                                                </div>
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
                                            // Initialize server-side `lazy` as false so datasource runs immediately
                                            'lazy' => false,
                                        ])
                                        <script>
                                            // Auto-poll the nearby table to refresh when queue jobs complete
                                            (function(){
                                                let pollInterval = null;
                                                let retryCount = 0;
                                                const maxRetries = 10;
                                                let pollStartTime = null;
                                                const maxPollDuration = 300000; // 5 minutes max
                                                
                                                function getComponent() {
                                                    try {
                                                        // Try multiple ways to find the component
                                                        const element = document.querySelector('[wire\\:id*="nearby-objects-table"]') ||
                                                                       document.querySelector('[wire\\:id]');
                                                        
                                                        if (!element) return null;
                                                        
                                                        const wireId = element.getAttribute('wire:id');
                                                        if (!wireId) return null;
                                                        
                                                        return window.Livewire?.find(wireId);
                                                    } catch (e) {
                                                        return null;
                                                    }
                                                }
                                                
                                                function checkAndPoll() {
                                                    try {
                                                        const component = getComponent();
                                                        
                                                        if (!component) {
                                                            if (retryCount < maxRetries) {
                                                                retryCount++;
                                                                setTimeout(checkAndPoll, 1000);
                                                            }
                                                            return;
                                                        }
                                                        
                                                        // Found component - reset retry counter
                                                        retryCount = 0;
                                                        
                                                        // Check the hasPendingCalculations property (works across all pages!)
                                                        // Access the raw data state, not the proxy function
                                                        let hasPending = false;
                                                        try {
                                                            // Try multiple ways to access the actual boolean value
                                                            if (component.__instance && component.__instance.data && typeof component.__instance.data.hasPendingCalculations === 'boolean') {
                                                                hasPending = component.__instance.data.hasPendingCalculations;
                                                            } else if (component.$wire && typeof component.$wire.$get === 'function') {
                                                                hasPending = component.$wire.$get('hasPendingCalculations') || false;
                                                            } else if (component.state && typeof component.state.hasPendingCalculations === 'boolean') {
                                                                hasPending = component.state.hasPendingCalculations;
                                                            } else if (component.data && typeof component.data.hasPendingCalculations === 'boolean') {
                                                                hasPending = component.data.hasPendingCalculations;
                                                            }
                                                        } catch (e) {
                                                            // Silently continue if property access fails
                                                        }
                                                        
                                                        // Check if we've been polling too long
                                                        if (pollStartTime && (Date.now() - pollStartTime) > maxPollDuration) {
                                                            if (pollInterval) {
                                                                clearInterval(pollInterval);
                                                                pollInterval = null;
                                                                pollStartTime = null;
                                                            }
                                                            return;
                                                        }
                                                        
                                                        if (hasPending) {
                                                            // Start polling if not already polling
                                                            if (!pollInterval) {
                                                                pollStartTime = Date.now();
                                                                pollInterval = setInterval(() => {
                                                                    try {
                                                                        if (window.Livewire) {
                                                                            Livewire.dispatch('pg:eventRefresh-nearby-objects-table');
                                                                        }
                                                                    } catch (e) {
                                                                        console.error('[NearbyTable] Refresh error:', e);
                                                                    }
                                                                }, 5000); // 5 seconds
                                                            }
                                                        } else {
                                                            // Stop polling when no more pending calculations
                                                            if (pollInterval) {
                                                                clearInterval(pollInterval);
                                                                pollInterval = null;
                                                                pollStartTime = null;
                                                            }
                                                        }
                                                    } catch (e) {
                                                        console.error('[NearbyTable] Poll check error:', e);
                                                    }
                                                }
                                                
                                                // Initial check after table loads - wait longer for component to initialize
                                                setTimeout(() => {
                                                    checkAndPoll();
                                                }, 3000);
                                                
                                                // Listen for Livewire updates
                                                if (window.Livewire) {
                                                    Livewire.hook('commit', ({ component, succeed }) => {
                                                        succeed(() => {
                                                            setTimeout(checkAndPoll, 500);
                                                        });
                                                    });
                                                }
                                                
                                                // Cleanup on page unload
                                                window.addEventListener('beforeunload', () => {
                                                    if (pollInterval) clearInterval(pollInterval);
                                                });
                                            })();
                                        </script>
                                        <script>
                                            // Defer loading the nearby table data so the main page can
                                            // render quickly. Trigger the Livewire component to load
                                            // after a short delay.
                                            (function(){
                                                try {
                                                    setTimeout(function(){
                                                        if (window.Livewire && typeof Livewire.dispatchTo === 'function') {
                                                            try { Livewire.dispatchTo('nearby-objects-table', 'loadNearby'); } catch (e) { }
                                                        }
                                                    }, 800);
                                                } catch (e) { }
                                            })();
                                        </script>
                                        {{-- Ensure selects inside the nearby objects area use a dark background and light text.
                                            PowerGrid renders its own <select> for per-page; browsers limit styling of <option>,
                                            but we force the select box to match the site's dark theme. --}}
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
            $objectSketches = \App\Models\SketchOfTheWeek::whereIn('observation_id', $obsIds)
                ->orderByDesc('date')
                ->get();
        } else {
            // Fallback: try tokenized LIKE search on legacy objectname in case
            // the legacy DB uses a slightly different formatting for comet names.
            try {
                $simple = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $objName);
                $tokens = array_filter(array_map('trim', preg_split('/\s+/', $simple)));
                if (!empty($tokens)) {
                    $q = \Illuminate\Support\Facades\DB::connection('mysqlOld')->table('observations');
                    $first = array_shift($tokens);
                    $q->where('objectname', 'like', '%' . $first . '%');
                    foreach ($tokens as $t) {
                        $q->orWhere('objectname', 'like', '%' . $t . '%');
                    }
                    $altIds = $q->pluck('id')->toArray();
                    if (!empty($altIds)) {
                        $objectSketches = \App\Models\SketchOfTheWeek::whereIn('observation_id', $altIds)
                            ->orderByDesc('date')
                            ->get();
                    }
                }
            } catch (\Throwable $_) {
                // ignore fallback failures
            }
            // Also check sketch_of_the_week entries that reference cometobservations
            try {
                $tokensForLike = [];
                if (!empty($tokens)) {
                    foreach ($tokens as $t) {
                        $tokensForLike[] = '%' . $t . '%';
                    }
                } else {
                    $tokensForLike[] = '%' . $objName . '%';
                }
                // Build WHERE clause for tokenized matching on cometobjects.name
                $likes = implode(' OR ', array_fill(0, count($tokensForLike), 'coo.name LIKE ?'));
                $sql = 'SELECT s.* FROM sketch_of_the_week s JOIN deepskylog.cometobservations co ON co.id = -s.observation_id JOIN deepskylog.cometobjects coo ON coo.id = co.objectid WHERE ' . $likes . ' ORDER BY s.date DESC';
                $cometRows = \Illuminate\Support\Facades\DB::select($sql, $tokensForLike);
                if (!empty($cometRows)) {
                    foreach ($cometRows as $r) {
                        // convert stdClass row to Eloquent model for the view helper x-sketch
                        $objectSketches->push(\App\Models\SketchOfTheWeek::find($r->id));
                    }
                }
            } catch (\Throwable $_) {
                // ignore
            }
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

                        <aside class="w-full xl:col-span-2 xl:w-auto lg:flex-none lg:max-w-[420px] space-y-4">
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
                                                <a href="{{ $simbadUrl }}" target="_blank"
                                                    rel="noopener noreferrer"
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
                                @if (isset($session->ra) &&
                                    isset($session->decl) &&
                                    !empty($session->ra) &&
                                    !empty($session->decl) &&
                                    ($session->source_type_raw ?? '') !== 'planet' && ($session->source_type_raw ?? '') !== 'comet')
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
                                        data-object-type="{{ $session->source_type_raw ?? '' }}"
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
                                        {{-- One-time server-side initial sync: ensure hidden inputs match server-selected ids immediately on first render. This avoids relying on client heuristics to populate hidden fields. --}}
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

<!-- Aladin Lite assets (inline so layout stack is not required) -->
<link rel="stylesheet" href="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.css">
<!-- Tom Select removed: using native selects / WireUI styling instead -->

<input type="hidden" id="object-id-hidden" value="{{ $session->id ?? '' }}">
<!-- Fallback payload observer installer: ensures an observer is present even if earlier script didn't run -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script defer src="https://aladin.u-strasbg.fr/AladinLite/api/v2/latest/aladin.min.js"></script>
<script src="/js/object-show-inline.js"></script>

<script>
// Attach Save handler for Aladin preview defaults
(function () {
    try {
        var btn = document.getElementById('aladin-save-btn');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var container = document.getElementById('aladin-lite-container');
            if (!container) return;
            var saveUrl = container.getAttribute('data-save-url') || '/api/user/aladin-defaults';
            var inst = document.getElementById('aladin-instrument-hidden')?.value || null;
            var ep = document.getElementById('aladin-eyepiece-hidden')?.value || null;
            var ln = document.getElementById('aladin-lens-hidden')?.value || null;

            // UI feedback
            var origText = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = 'Saving...';

            fetch(saveUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' ,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ instrument_id: inst, eyepiece_id: ep, lens_id: ln })
            }).then(function (r) {
                return r.json().catch(function () { return { error: 'invalid-json' }; });
            }).then(function (json) {
                if (json && json.ok) {
                    btn.textContent = 'Saved';
                    setTimeout(function () { btn.textContent = origText; btn.disabled = false; }, 1000);
                } else {
                    console.warn('Save failed', json);
                    btn.textContent = 'Save failed';
                    setTimeout(function () { btn.textContent = origText; btn.disabled = false; }, 2000);
                }
            }).catch(function (err) {
                console.error('Save error', err);
                btn.textContent = 'Save failed';
                setTimeout(function () { btn.textContent = origText; btn.disabled = false; }, 2000);
            });
        });
    } catch (e) { console.warn('aladin save handler failed', e); }
})();
</script>
</x-app-layout>
