<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
            margin: 24px;
        }

        h1 {
            font-size: 24px;
            margin: 0 0 8px;
        }

        h2 {
            font-size: 18px;
            margin: 0 0 6px;
        }

        .summary {
            color: #4b5563;
            margin: 0 0 18px;
        }

        .observation-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 18px;
        }

        .observation-header {
            margin-bottom: 16px;
        }

        .meta {
            color: #4b5563;
            font-size: 11px;
        }

        .sketch {
            margin: 14px 0 18px;
            text-align: center;
        }

        .sketch img {
            max-height: 340px;
            max-width: 100%;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .detail-table th,
        .detail-table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        .detail-table th {
            color: #374151;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            width: 32%;
        }

        .section-title {
            color: #111827;
            font-size: 13px;
            font-weight: bold;
            margin: 18px 0 8px;
            text-transform: uppercase;
        }

        .pill-list {
            margin: 0;
            padding: 0;
        }

        .pill-list li {
            display: inline-block;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 999px;
            margin: 0 6px 6px 0;
            padding: 4px 10px;
        }

        .notes {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 14px;
        }

        .empty-state {
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            color: #6b7280;
            padding: 18px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="summary">
        {{ __('Generated on') }} {{ now()->translatedFormat('j M Y H:i') }}
        @if (!empty($query))
            . {{ __('Query') }}: {{ $query }}
        @endif
    </p>

    @if ($entries->isEmpty())
        <div class="empty-state">{{ __('No observations found.') }}</div>
    @endif

    @foreach ($entries as $entry)
        @php
            $obs = $entry['observation'];
            $observationType = $entry['observationType'];
            $lens = $entry['lens'] ?? null;
            $moon = $entry['moonContext'] ?? null;
            $drawingDataUri = $entry['drawingDataUri'] ?? null;
            $user = ($entry['preloaded_users'] ?? collect())->get($obs->observerid);

            // Stored in UT, shown in the time zone of the location.
            $obsLocation = ($entry['preloaded_locations'] ?? collect())->get($obs->locationid);
            $obsDate = $obs->localDate($obsLocation) ?? '';
            $obsTime = $obs->localObservedAt($obsLocation)?->format('H:i T') ?? '';

            $rows = [];
            $characteristics = [];

            if ($observationType === 'deepsky') {
                $object = ($entry['preloaded_objects'] ?? collect())->get($obs->objectname);
                if (!$object) {
                    $object = ($entry['preloaded_objects'] ?? collect())->get(\Illuminate\Support\Str::slug((string) ($obs->objectname ?? ''), '-'));
                }
                if (!$object) {
                    $object = \App\Models\Target::where('slug', \Illuminate\Support\Str::slug((string) ($obs->objectname ?? ''), '-'))
                        ->orWhere('name', $obs->objectname)
                        ->first();
                }

                $location = ($entry['preloaded_locations'] ?? collect())->get($obs->locationid);
                $instrument = ($entry['preloaded_instruments'] ?? collect())->get($obs->instrumentid);
                $eyepiece = ($entry['preloaded_eyepieces'] ?? collect())->get($obs->eyepieceid);
                $filterObj = ($entry['preloaded_filters'] ?? collect())->get($obs->filterid);
                $constellations = $entry['preloaded_constellations'] ?? collect();
                $constellation = $object ? ($constellations[$object->con]->name ?? null) : null;
                $objectDisplayName = (string) ($object?->name ?? $obs->objectname ?? '');

                $rows[] = ['label' => __('Object'), 'value' => $objectDisplayName];
                if ($object?->long_type()) {
                    $rows[] = ['label' => __('Object type'), 'value' => __($object->long_type())];
                }
                if ($constellation) {
                    $rows[] = ['label' => __('Constellation'), 'value' => $constellation];
                }
                if ($location) {
                    $rows[] = ['label' => __('Location'), 'value' => html_entity_decode((string) $location->name)];
                }
                if ($instrument) {
                    $rows[] = ['label' => __('Instrument'), 'value' => html_entity_decode((string) $instrument->fullName())];
                }
                if ($eyepiece) {
                    $rows[] = ['label' => __('Eyepiece'), 'value' => html_entity_decode((string) $eyepiece->fullName())];
                }
                if ($filterObj) {
                    $rows[] = ['label' => __('Filter'), 'value' => html_entity_decode((string) $filterObj->fullName())];
                }
                if (!empty($lens)) {
                    $rows[] = ['label' => __('Lens'), 'value' => html_entity_decode((string) $lens->fullName())];
                }
                if (!empty($obs->magnification) && $obs->magnification !== '0') {
                    $rows[] = ['label' => __('Magnification'), 'value' => $obs->magnification . 'x'];
                }
                if ($obs->SQM > 0) {
                    $rows[] = ['label' => __('Sky quality (SQM)'), 'value' => number_format($obs->SQM, 2)];
                }
                if (!empty($obs->limmag) && $obs->limmag > 0) {
                    $rows[] = ['label' => __('Limiting magnitude'), 'value' => number_format($obs->limmag, 1)];
                }
                if (!empty($obs->seeing) && $obs->seeing > 0) {
                    $rows[] = ['label' => __('Seeing'), 'value' => __('observations.seeing.' . $obs->seeing)];
                }
                if (!empty($obs->transparency) && $obs->transparency > 0) {
                    $rows[] = ['label' => __('Transparency'), 'value' => __('observations.transparency.' . $obs->transparency)];
                }
                if (!empty($obs->visibility) && $obs->visibility > 0) {
                    $visibilityKey = (string) $obs->visibility;
                    $visibilityLabel = __('observations.visibility.' . $visibilityKey);
                    $rows[] = [
                        'label' => __('Object visibility'),
                        'value' => $visibilityLabel === 'observations.visibility.' . $visibilityKey ? $visibilityKey : $visibilityLabel,
                    ];
                }
                if (!empty($obs->smallDiameter) && $obs->smallDiameter > 0) {
                    $size = !empty($obs->largeDiameter) && $obs->largeDiameter > 0 && $obs->largeDiameter != $obs->smallDiameter
                        ? number_format($obs->largeDiameter, 1) . "' x " . number_format($obs->smallDiameter, 1) . "'"
                        : number_format($obs->smallDiameter, 1) . "'";
                    $rows[] = ['label' => __('Apparent size'), 'value' => $size];
                }
                if (!empty($obs->clusterType) || !empty($obs->cluster_type)) {
                    $clusterType = strtoupper(trim((string) ($obs->clusterType ?? $obs->cluster_type ?? '')));
                    $clusterLabel = __('observations.cluster_types.' . $clusterType);
                    $rows[] = [
                        'label' => __('Cluster type'),
                        'value' => $clusterLabel === 'observations.cluster_types.' . $clusterType ? $clusterType : $clusterLabel,
                    ];
                }

                if ((int) ($obs->stellar ?? -1) === 1) {
                    $characteristics[] = __('Stellar');
                }
                if ((int) ($obs->extended ?? -1) === 1) {
                    $characteristics[] = __('Extended');
                }
                if ((int) ($obs->resolved ?? -1) === 1) {
                    $characteristics[] = __('Resolved');
                }
                if ((int) ($obs->mottled ?? -1) === 1) {
                    $characteristics[] = __('Mottled');
                }
                if ((int) ($obs->unusualShape ?? -1) === 1) {
                    $characteristics[] = __('Unusual shape');
                }
                if ((int) ($obs->partlyUnresolved ?? -1) === 1) {
                    $characteristics[] = __('Partly unresolved');
                }
                if ((int) ($obs->colorContrasts ?? -1) === 1) {
                    $characteristics[] = __('Color contrasts');
                }
                if ((int) ($obs->equalBrightness ?? -1) === 1) {
                    $characteristics[] = __('Equal brightness');
                }
                if ((int) ($obs->niceField ?? -1) === 1) {
                    $characteristics[] = __('Nice field');
                }
                if ((int) ($obs->component1 ?? -1) > 0) {
                    $characteristics[] = __('Component 1') . ': ' . __('observations.component_colors.' . (int) $obs->component1);
                }
                if ((int) ($obs->component2 ?? -1) > 0) {
                    $characteristics[] = __('Component 2') . ': ' . __('observations.component_colors.' . (int) $obs->component2);
                }
            } else {
                $comet = ($entry['preloaded_comets'] ?? collect())->get($obs->objectid);
                $location = ($entry['preloaded_locations'] ?? collect())->get($obs->locationid);
                $instrument = ($entry['preloaded_instruments'] ?? collect())->get($obs->instrumentid);
                $objectDisplayName = (string) ($comet?->name ?? $obs->resolved_comet_name ?? $obs->target_name ?? __('Unknown comet'));

                $rows[] = ['label' => __('Comet'), 'value' => $objectDisplayName];
                if (!empty($obs->mag) && $obs->mag > -90) {
                    $magnitude = number_format($obs->mag, 1);
                    if (!empty($obs->maguncertain)) {
                        $magnitude .= ' (' . __('uncertain') . ')';
                    }
                    if (!empty($obs->lessmagnitude)) {
                        $magnitude .= ' (<)';
                    }
                    $rows[] = ['label' => __('Magnitude'), 'value' => $magnitude];
                }
                if ($location) {
                    $rows[] = ['label' => __('Location'), 'value' => html_entity_decode((string) $location->name)];
                }
                if ($instrument) {
                    $rows[] = ['label' => __('Instrument'), 'value' => html_entity_decode((string) $instrument->fullName())];
                }
                if (!empty($obs->magnification) && $obs->magnification !== '0') {
                    $rows[] = ['label' => __('Magnification'), 'value' => $obs->magnification . 'x'];
                }
                if (!empty($obs->dc)) {
                    $rows[] = ['label' => __('Degree of condensation (DC)'), 'value' => (string) $obs->dc];
                }
                if (!empty($obs->coma) && $obs->coma > -90) {
                    $rows[] = ['label' => __('Coma diameter'), 'value' => $obs->coma . "'"];
                }
                if (!empty($obs->tail) && $obs->tail > -90) {
                    $tail = $obs->tail . "'";
                    if (!empty($obs->pa) && $obs->pa > -90) {
                        $tail .= ' / ' . __('PA') . ' ' . $obs->pa . ' deg';
                    }
                    $rows[] = ['label' => __('Tail length'), 'value' => $tail];
                }
                if (!empty($obs->methode)) {
                    $rows[] = ['label' => __('Method'), 'value' => (string) $obs->methode];
                }
                if (!empty($obs->chart)) {
                    $rows[] = ['label' => __('Chart'), 'value' => (string) $obs->chart];
                }
            }
        @endphp

        <div class="observation-card" @if (! $loop->last) style="page-break-after: always;" @endif>
            <div class="observation-header">
                <h2>{{ $objectDisplayName }}</h2>
                <div class="meta">
                    {{ __('Observed by') }} {{ $user?->name ?? ($obs->observerid ?? __('Unknown observer')) }}
                    {{ __('on') }} {{ \Carbon\Carbon::parse($obsDate)->translatedFormat('j M Y') }}
                    @if ($obsTime !== '')
                        {{ __('at') }} {{ $obsTime }}
                    @endif
                </div>
            </div>

            @if (!empty($drawingDataUri))
                <div class="sketch">
                    <img src="{{ $drawingDataUri }}" alt="{{ __('Observation sketch') }}">
                </div>
            @endif

            <table class="detail-table">
                @foreach ($rows as $row)
                    <tr>
                        <th>{{ $row['label'] }}</th>
                        <td>{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </table>

            @if (!empty($characteristics))
                <div class="section-title">{{ __('Characteristics') }}</div>
                <ul class="pill-list">
                    @foreach ($characteristics as $characteristic)
                        <li>{{ $characteristic }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($moon)
                <div class="section-title">{{ __('Moon during observation') }}</div>
                <table class="detail-table">
                    <tr>
                        <th>{{ __('Phase') }}</th>
                        <td>{{ $moon['phase_name'] ?? __('Moon phase') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Illumination') }}</th>
                        <td>
                            @if (!is_null($moon['illumination_percent'] ?? null))
                                {{ $moon['illumination_percent'] }}% {{ __('illuminated') }}
                            @else
                                {{ __('Illumination unavailable') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('Moon altitude') }}</th>
                        <td>
                            @if (($moon['has_specific_time'] ?? false) && ($moon['horizon_status'] ?? 'unknown') === 'above')
                                {{ __('Above the horizon') }}
                                @if (!is_null($moon['altitude_degrees'] ?? null))
                                    ({{ number_format($moon['altitude_degrees'], 1) }} deg)
                                @endif
                            @elseif (($moon['has_specific_time'] ?? false) && ($moon['horizon_status'] ?? 'unknown') === 'below')
                                {{ __('Not above the horizon') }}
                                @if (!is_null($moon['altitude_degrees'] ?? null))
                                    ({{ number_format($moon['altitude_degrees'], 1) }} deg)
                                @endif
                            @elseif ($moon['has_specific_time'] ?? false)
                                {{ __('Moon altitude unavailable') }}
                            @else
                                {{ __('No observation time recorded') }}
                            @endif
                        </td>
                    </tr>
                </table>
            @endif

            @if (!empty($obs->description))
                <div class="section-title">{{ __('Notes') }}</div>
                <div class="notes">{!! \App\Support\Html::purify((string) $obs->description, true) !!}</div>
            @endif
        </div>
    @endforeach
</body>
</html>