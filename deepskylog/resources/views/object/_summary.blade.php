@php
    $objectName = $session->name ?? '';
    $totalObs =
        isset($totalObservations) && $totalObservations !== null
            ? $totalObservations
            : \App\Models\ObservationsOld::getObservationsCountForObject($objectName);

    // Prefer controller-provided numeric/collection drawings when available (comet controller passes numeric counts)
    if (isset($drawings)) {
        if (is_numeric($drawings)) {
            $drawingsCount = (int) $drawings;
        } elseif (is_countable($drawings)) {
            $drawingsCount = count($drawings);
        } else {
            $drawingsCount = \App\Models\ObservationsOld::getDrawingsCountForObject($objectName);
        }
    } else {
        $drawingsCount = \App\Models\ObservationsOld::getDrawingsCountForObject($objectName);
    }

    // Prefer controller-provided per-user counts when available (controller sets comet-specific counts)
    if (isset($yourObservations)) {
        $yourObs = is_numeric($yourObservations)
            ? (int) $yourObservations
            : (is_countable($yourObservations)
                ? count($yourObservations)
                : 0);
    } else {
        $yourObs = auth()->check()
            ? \App\Models\ObservationsOld::getObservationsCountForUser(auth()->user(), $objectName)
            : 0;
    }

    if (isset($yourDrawings)) {
        $yourDrawingsCount = is_numeric($yourDrawings)
            ? (int) $yourDrawings
            : (is_countable($yourDrawings)
                ? count($yourDrawings)
                : 0);
        $yourDrawings = $yourDrawingsCount;
    } else {
        $yourDrawings = auth()->check()
            ? \App\Models\ObservationsOld::getDrawingsCountForUser(auth()->user(), $objectName)
            : 0;
    }
    $lastObs = auth()->check()
        ? \App\Models\ObservationsOld::getLastObservationDateForUser(auth()->user(), $objectName)
        : null;
    $lastDrawing = auth()->check()
        ? \App\Models\ObservationsOld::getLastDrawingDateForUser(auth()->user(), $objectName)
        : null;
    $objSlug = $canonicalSlug ?? ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ''));
    $isCometLocal = strtolower(trim((string) ($session->source_type_raw ?? ''))) === 'comet';
@endphp

<div
    class="grid grid-cols-1 sm:inline-grid sm:grid-flow-col sm:auto-cols-max gap-y-0 sm:gap-x-6 mt-1 text-sm text-gray-300">
    <div>
        <div class="flex items-center gap-0">
            <span class="text-gray-400">{{ __('Object type') }}</span>
            <span class="text-white font-medium ml-2">
                {{ $session->source_type ?? __('Unknown') }}
                @if (!empty($session->feature_type))
                    <span class="text-gray-400">&nbsp;(</span>
                    <span
                        class="text-white font-medium">{{ \Illuminate\Support\Str::title($session->feature_type) }}</span>
                    <span class="text-gray-400">)</span>
                @endif
            </span>
        </div>

        <div class="mt-1 space-y-0">
            <div class="flex items-center justify-start gap-0">
                <span class="text-gray-400">{{ __('Observations') }}</span>
                @if ($isCometLocal)
                    <a href="{{ url('/cometobservations/' . $objSlug) }}"
                        class="text-white font-medium ml-2 hover:underline">{{ $totalObs ?? 0 }}</a>
                @else
                    <a href="{{ url('/observations/' . $objSlug) }}"
                        class="text-white font-medium ml-2 hover:underline">{{ $totalObs ?? 0 }}</a>
                @endif
            </div>

            @auth
                <div class="flex items-center justify-start gap-0">
                    <span class="text-gray-400">{{ __('Your observations') }}</span>
                    @if ($isCometLocal)
                        <a href="{{ url('/cometobservations/' . auth()->user()->slug . '/' . $objSlug) }}"
                            class="text-white font-medium ml-2 hover:underline">{{ $yourObs ?? 0 }}</a>
                    @else
                        <a href="{{ url('/observations/' . auth()->user()->slug . '/' . $objSlug) }}"
                            class="text-white font-medium ml-2 hover:underline">{{ $yourObs ?? 0 }}</a>
                    @endif
                </div>

                <div class="flex items-center justify-start gap-0">
                    <span class="text-gray-400">{{ __('Your drawings') }}</span>
                    @if ($isCometLocal)
                        <a href="{{ url('/cometdrawings/' . auth()->user()->slug . '/' . $objSlug) }}"
                            class="text-white font-medium ml-2 hover:underline">{{ $yourDrawings ?? 0 }}</a>
                    @else
                        <a href="{{ url('/observations/drawings/' . auth()->user()->slug . '/' . $objSlug) }}"
                            class="text-white font-medium ml-2 hover:underline">{{ $yourDrawings ?? 0 }}</a>
                    @endif
                </div>
            @endauth
        </div>
    </div>

    <div>
        <div class="flex items-center gap-0" aria-hidden="true">
            <span class="text-gray-400">&nbsp;</span>
        </div>
        <div class="mt-1 space-y-0">
            <div class="flex items-center justify-start gap-0">
                <span class="text-gray-400">{{ __('Drawings') }}</span>
                @if ($isCometLocal)
                    <a href="{{ url('/cometdrawings/' . $objSlug) }}"
                        class="text-white font-medium ml-2 hover:underline">{{ $drawingsCount ?? 0 }}</a>
                @else
                    <a href="{{ url('/observations/drawings/' . $objSlug) }}"
                        class="text-white font-medium ml-2 hover:underline">{{ $drawingsCount ?? 0 }}</a>
                @endif
            </div>

            @auth
                <div class="flex items-center justify-start gap-0">
                    <span class="text-gray-400">{{ __('Last observed by you') }}</span>
                    <span
                        class="text-white font-medium ml-2">{{ $lastObs ? ($lastObs instanceof \Carbon\Carbon ? $lastObs->translatedFormat('j M Y') : $lastObs) : __('Never') }}</span>
                </div>
                <div class="flex items-center justify-start gap-0">
                    <span class="text-gray-400">{{ __('Last drawing by you') }}</span>
                    <span
                        class="text-white font-medium ml-2">{{ $lastDrawing ? ($lastDrawing instanceof \Carbon\Carbon ? $lastDrawing->translatedFormat('j M Y') : $lastDrawing) : __('Never') }}</span>
                </div>
            @endauth
        </div>
    </div>
</div>
