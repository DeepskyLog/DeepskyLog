@php
    // comet-details partial: top chart removed to avoid duplication; Livewire
    // component `comet-details` renders the canonical magnitude chart.
@endphp
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
